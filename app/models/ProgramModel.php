<?php

class ProgramModel {
    private static array $palette = [
        'linear-gradient(135deg,#0D1B3E,#2A4080)',
        'linear-gradient(135deg,#065F46,#0F9D58)',
        'linear-gradient(135deg,#7C3AED,#A78BFA)',
        'linear-gradient(135deg,#B45309,#F59E0B)',
        'linear-gradient(135deg,#0E7490,#22D3EE)',
    ];

    private static function refresh(): void {
        if (function_exists('sipedo_load_session_from_db')) sipedo_load_session_from_db();
    }

    private static function allowedStatus(string $status): string {
        return in_array($status, ['active','inactive','closed','deleted'], true) ? $status : 'active';
    }

    private static function nextKode(mysqli $conn = null): string {
        if ($conn) {
            $row = $conn->query("SELECT MAX(CAST(SUBSTRING(kode, 4) AS UNSIGNED)) AS max_no FROM programs WHERE kode LIKE 'PR-%'")->fetch_assoc();
            $next = ((int)($row['max_no'] ?? 0)) + 1;
            return 'PR-' . str_pad((string)$next, 2, '0', STR_PAD_LEFT);
        }

        $max = 0;
        foreach ($_SESSION['programs'] ?? [] as $program) {
            if (preg_match('/^PR-(\d+)$/', (string)($program['id'] ?? ''), $m)) {
                $max = max($max, (int)$m[1]);
            }
        }
        return 'PR-' . str_pad((string)($max + 1), 2, '0', STR_PAD_LEFT);
    }

    public static function all(): array {
        self::refresh();
        return $_SESSION['programs'] ?? [];
    }

    public static function active(): array {
        return array_values(array_filter(self::all(), fn($p) => ($p['status'] ?? '') === 'active'));
    }

    public static function byStaff(int $userId): array {
        if (db_ready()) {
            $conn = db();
            $stmt = $conn->prepare(
                "SELECT DISTINCT p.id
                 FROM programs p
                 LEFT JOIN program_staff ps ON ps.program_id = p.id AND ps.staff_id = ?
                 WHERE (p.created_by = ? OR ps.staff_id = ?) AND p.status <> 'deleted'"
            );
            $stmt->bind_param('iii', $userId, $userId, $userId);
            $stmt->execute();
            $ids = [];
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) $ids[] = (int)$row['id'];

            self::refresh();
            return array_values(array_filter($_SESSION['programs'] ?? [], fn($p) => in_array((int)($p['num_id'] ?? 0), $ids, true)));
        }

        return array_values(array_filter(self::all(), function($p) use ($userId) {
            return isset($p['created_by']) && (int)$p['created_by'] === $userId && ($p['status'] ?? '') !== 'deleted';
        }));
    }

    public static function findById(string $id): ?array {
        self::refresh();
        foreach ($_SESSION['programs'] ?? [] as $p) {
            if (($p['id'] ?? '') === $id || (string)($p['num_id'] ?? '') === $id) return $p;
        }
        return null;
    }

    public static function canManage(string $id, ?array $user = null): bool {
        $user = $user ?: current_user();
        if (!$user) return false;
        if (($user['role'] ?? current_role()) === 'admin') return true;
        if (($user['role'] ?? current_role()) !== 'staff') return false;

        $program = self::findById($id);
        if (!$program) return false;
        $userId = (int)($user['db_id'] ?? 0);
        if ((int)($program['created_by'] ?? 0) === $userId) return true;

        if (db_ready() && !empty($program['num_id'])) {
            $conn = db();
            $stmt = $conn->prepare("SELECT id FROM program_staff WHERE program_id=? AND staff_id=? LIMIT 1");
            $programId = (int)$program['num_id'];
            $stmt->bind_param('ii', $programId, $userId);
            $stmt->execute();
            return (bool)$stmt->get_result()->fetch_assoc();
        }
        return false;
    }

    public static function create(array $data): void {
        if (db_ready()) {
            $conn = db();
            $count = (int)$conn->query("SELECT COUNT(*) c FROM programs")->fetch_assoc()['c'];
            $kode = self::nextKode($conn);
            $gradient = self::$palette[$count % count(self::$palette)];
            $createdBy = (int)(current_user()['db_id'] ?? 1);
            $target = (float)$data['target'];
            $collected = 0.0;
            $pct = 0.0;
            $status = self::allowedStatus($data['status'] ?? 'active');
            $image = $data['image'] ?? '';
            $stmt = $conn->prepare("INSERT INTO programs (kode,name,description,category,target,collected,pct,deadline,status,image,gradient,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssssdddssssi', $kode, $data['name'], $data['description'], $data['category'], $target, $collected, $pct, $data['deadline'], $status, $image, $gradient, $createdBy);
            $stmt->execute();
            self::refresh();
            return;
        }

        $count = count($_SESSION['programs'] ?? []);
        $createdBy = (int)(current_user()['db_id'] ?? 0);
        $deadlineRaw = $data['deadline'] ?? '';
        $_SESSION['programs'][] = [
            'id'           => self::nextKode(),
            'name'         => $data['name'],
            'cat'          => $data['category'],
            'target'       => ((float)$data['target']) / 1000000,
            'collected'    => 0,
            'pct'          => 0,
            'deadline'     => function_exists('sipedo_date_id') ? sipedo_date_id($deadlineRaw) : $deadlineRaw,
            'deadline_raw' => $deadlineRaw,
            'status'       => self::allowedStatus($data['status'] ?? 'active'),
            'image'        => $data['image'] ?? '',
            'desc'         => $data['description'],
            'gradient'     => self::$palette[$count % count(self::$palette)],
            'created_by'   => $createdBy,
        ];
    }

    public static function update(string $id, array $data): bool {
        $status = self::allowedStatus($data['status'] ?? 'active');

        if (db_ready()) {
            $conn = db();
            $program = self::findById($id);
            if (!$program) return false;
            $kode = $program['id'];
            $target = (float)$data['target'];
            if (!empty($data['image'])) {
                $stmt = $conn->prepare("UPDATE programs SET name=?, description=?, category=?, target=?, deadline=?, status=?, image=? WHERE kode=?");
                $stmt->bind_param('sssdssss', $data['name'], $data['description'], $data['category'], $target, $data['deadline'], $status, $data['image'], $kode);
            } else {
                $stmt = $conn->prepare("UPDATE programs SET name=?, description=?, category=?, target=?, deadline=?, status=? WHERE kode=?");
                $stmt->bind_param('sssdsss', $data['name'], $data['description'], $data['category'], $target, $data['deadline'], $status, $kode);
            }
            $ok = $stmt->execute();
            self::refresh();
            return $ok;
        }

        foreach ($_SESSION['programs'] as $i => $program) {
            if (($program['id'] ?? '') !== $id && (string)($program['num_id'] ?? '') !== $id) continue;
            $deadlineRaw = $data['deadline'] ?? '';
            $_SESSION['programs'][$i]['name'] = $data['name'];
            $_SESSION['programs'][$i]['cat'] = $data['category'];
            $_SESSION['programs'][$i]['deadline'] = function_exists('sipedo_date_id') ? sipedo_date_id($deadlineRaw) : $deadlineRaw;
            $_SESSION['programs'][$i]['deadline_raw'] = $deadlineRaw;
            $_SESSION['programs'][$i]['desc'] = $data['description'];
            $_SESSION['programs'][$i]['status'] = $status;
            $_SESSION['programs'][$i]['target'] = ((float)$data['target']) / 1000000;
            $targetJuta = (float)$_SESSION['programs'][$i]['target'];
            $_SESSION['programs'][$i]['pct'] = $targetJuta > 0 ? min(100, round(((float)$_SESSION['programs'][$i]['collected'] / $targetJuta) * 100, 1)) : 0;
            if (!empty($data['image'])) $_SESSION['programs'][$i]['image'] = $data['image'];
            return true;
        }
        return false;
    }

    public static function setStatus(string $id, string $status): bool {
        $status = self::allowedStatus($status);
        if (db_ready()) {
            $conn = db();
            $stmt = $conn->prepare("UPDATE programs SET status=? WHERE kode=? OR id=?");
            $num = (int)$id;
            $stmt->bind_param('ssi', $status, $id, $num);
            $ok = $stmt->execute();
            self::refresh();
            return $ok;
        }
        foreach ($_SESSION['programs'] as &$program) {
            if (($program['id'] ?? '') === $id || (string)($program['num_id'] ?? '') === $id) {
                $program['status'] = $status;
                return true;
            }
        }
        return false;
    }
}
