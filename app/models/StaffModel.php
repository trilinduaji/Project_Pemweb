<?php
class StaffModel {
    private static function refresh(): void { if (function_exists('sipedo_load_session_from_db')) sipedo_load_session_from_db(); }

    public static function all(): array { self::refresh(); return $_SESSION['staffList'] ?? []; }

    public static function findById(string $id): ?array {
        self::refresh();
        foreach ($_SESSION['staffList'] ?? [] as $s) {
            if ($s['id'] === $id || (string)($s['num_id'] ?? '') === $id) return $s;
        }
        return null;
    }

    public static function create(string $name, string $email): void {
        if (db_ready()) {
            $conn = db();
            $initials = sipedo_initials($name);
            $color = '#d97706';
            $hash = password_hash('123', PASSWORD_BCRYPT);
            $role = 'staff';
            $stmt = $conn->prepare("INSERT INTO users (name,email,password,role,initials,color) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('ssssss', $name, $email, $hash, $role, $initials, $color);
            $stmt->execute();
            $userId = $conn->insert_id;
            $count = (int)$conn->query("SELECT COUNT(*) c FROM staff_profiles")->fetch_assoc()['c'];
            $kode = 'STF-' . str_pad((string)($count + 1), 2, '0', STR_PAD_LEFT);
            $jabatan = 'Staff Verifikasi';
            $today = date('Y-m-d');
            $status = 'active';
            $stmt2 = $conn->prepare("INSERT INTO staff_profiles (user_id,kode,jabatan,joined_at,status) VALUES (?,?,?,?,?)");
            $stmt2->bind_param('issss', $userId, $kode, $jabatan, $today, $status);
            $stmt2->execute();
            self::refresh();
            return;
        }

        $count = count($_SESSION['staffList'] ?? []);
        $_SESSION['staffList'][] = ['id'=>'STF-'.str_pad((string)($count+1),2,'0',STR_PAD_LEFT),'name'=>$name,'email'=>$email,'role'=>'Staff Verifikasi','since'=>'Baru saja','status'=>'active'];
    }

    public static function setStatus(string $id, string $status): ?string {
        if (db_ready()) {
            $staff = self::findById($id);
            if (!$staff) return null;
            $conn = db();
            $stmt = $conn->prepare("UPDATE staff_profiles SET status=? WHERE kode=?");
            $stmt->bind_param('ss', $status, $staff['id']);
            $stmt->execute();
            self::refresh();
            return $staff['name'];
        }
        foreach ($_SESSION['staffList'] as &$staff) {
            if ($staff['id'] === $id) { $staff['status'] = $status; return $staff['name']; }
        }
        return null;
    }

    public static function deleteWithTransfer(string $id): array {
        if (db_ready()) {
            $staff = self::findById($id);
            if (!$staff) return ['name' => null, 'transferred' => []];
            $conn   = db();
            $userId = (int)($staff['user_id'] ?? 0);


            $adminRow = $conn->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch_assoc();
            $adminId  = (int)($adminRow['id'] ?? 1);


            $transferred = [];
            $progRes = $conn->query("SELECT id FROM programs WHERE created_by = {$userId}");
            while ($prog = $progRes->fetch_assoc()) {
                $programId = (int)$prog['id'];


                $rekanStmt = $conn->prepare(
                    "SELECT ps.staff_id FROM program_staff ps
                     JOIN users u ON u.id = ps.staff_id
                     WHERE ps.program_id = ? AND ps.staff_id != ?
                     ORDER BY ps.joined_at ASC LIMIT 1"
                );
                $rekanStmt->bind_param('ii', $programId, $userId);
                $rekanStmt->execute();
                $rekan = $rekanStmt->get_result()->fetch_assoc();

                if ($rekan) {

                    $newOwner = (int)$rekan['staff_id'];

                    $updProg = $conn->prepare("UPDATE programs SET created_by = ? WHERE id = ?");
                    $updProg->bind_param('ii', $newOwner, $programId);
                    $updProg->execute();


                    $updRole = $conn->prepare(
                        "UPDATE program_staff SET role_in_program = 'Koordinator' WHERE program_id = ? AND staff_id = ?"
                    );
                    $updRole->bind_param('ii', $programId, $newOwner);
                    $updRole->execute();
                    $transferred[] = $programId;
                } else {

                    $updProg = $conn->prepare("UPDATE programs SET created_by = ? WHERE id = ?");
                    $updProg->bind_param('ii', $adminId, $programId);
                    $updProg->execute();
                    $transferred[] = $programId;
                }
            }


            $updAdded = $conn->prepare("UPDATE program_staff SET added_by = ? WHERE added_by = ?");
            $updAdded->bind_param('ii', $adminId, $userId);
            $updAdded->execute();


            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();

            self::refresh();
            return ['name' => $staff['name'], 'transferred' => $transferred];
        }


        $deletedId   = null;
        $deletedName = null;
        foreach ($_SESSION['staffList'] as $i => $s) {
            if ($s['id'] === $id) {
                $deletedId   = $s['id'];
                $deletedName = $s['name'];
                array_splice($_SESSION['staffList'], $i, 1);
                break;
            }
        }
        if (!$deletedId) return ['name' => null, 'transferred' => []];


        $remainingIds = array_column($_SESSION['staffList'], 'id');
        foreach ($_SESSION['programs'] as &$p) {
            if (($p['created_by'] ?? '') === $deletedId) {
                $p['created_by'] = $remainingIds[0] ?? 'admin';
            }
        }
        unset($p);

        return ['name' => $deletedName, 'transferred' => []];
    }

    public static function addRekanToProgram(int $programId, int $rekanUserId, int $addedBy, string $roleInProgram = 'Anggota'): bool|string {
        if (!db_ready()) return false;
        $conn = db();

        $chk = $conn->prepare(
            "SELECT id FROM programs WHERE id=? AND created_by=? LIMIT 1"
        );
        $chk->bind_param('ii', $programId, $addedBy);
        $chk->execute();
        $isOwner = (bool)$chk->get_result()->fetch_assoc();

        if (!$isOwner) {

            $chk2 = $conn->prepare("SELECT id FROM program_staff WHERE program_id=? AND staff_id=? LIMIT 1");
            $chk2->bind_param('ii', $programId, $addedBy);
            $chk2->execute();
            $isOwner = (bool)$chk2->get_result()->fetch_assoc();
        }
        if (!$isOwner) return 'not_owner';


        $dup = $conn->prepare("SELECT id FROM program_staff WHERE program_id=? AND staff_id=? LIMIT 1");
        $dup->bind_param('ii', $programId, $rekanUserId);
        $dup->execute();
        if ($dup->get_result()->fetch_assoc()) return 'exists';


        $stmt = $conn->prepare("INSERT INTO program_staff (program_id, staff_id, added_by, role_in_program) VALUES (?,?,?,?)");
        $stmt->bind_param('iiis', $programId, $rekanUserId, $addedBy, $roleInProgram);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }


    public static function removeRekanFromProgram(int $psId, int $actorId): bool|string {
        if (!db_ready()) return false;
        $conn = db();


        $row = $conn->prepare("SELECT ps.*, p.created_by FROM program_staff ps JOIN programs p ON p.id = ps.program_id WHERE ps.id=? LIMIT 1");
        $row->bind_param('i', $psId);
        $row->execute();
        $data = $row->get_result()->fetch_assoc();
        if (!$data) return false;

        if ((int)$data['created_by'] !== $actorId && (int)$data['added_by'] !== $actorId) {
            return 'not_owner';
        }

        $del = $conn->prepare("DELETE FROM program_staff WHERE id=?");
        $del->bind_param('i', $psId);
        $del->execute();
        return $del->affected_rows > 0;
    }


    public static function getRekanByStaff(int $staffUserId): array {
        if (!db_ready()) return [];
        $conn = db();
        $stmt = $conn->prepare(
            "SELECT ps.id AS ps_id, ps.role_in_program, ps.joined_at,
                    p.id AS program_id, p.kode AS program_kode, p.name AS program_name,
                    u.id AS staff_uid, u.name AS staff_name, u.email AS staff_email,
                    u.initials, u.color,
                    ab.name AS added_by_name,
                    p.created_by
             FROM program_staff ps
             JOIN programs p ON p.id = ps.program_id
             JOIN users u ON u.id = ps.staff_id
             JOIN users ab ON ab.id = ps.added_by
             WHERE p.created_by = ? OR ps.staff_id = ?
             ORDER BY p.id ASC, ps.joined_at ASC"
        );
        $stmt->bind_param('ii', $staffUserId, $staffUserId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        return $rows;
    }


    public static function getProgramsForStaff(int $staffUserId): array {
        if (!db_ready()) return [];
        $conn = db();
        $stmt = $conn->prepare(
            "SELECT DISTINCT p.id, p.kode, p.name, p.status
             FROM programs p
             LEFT JOIN program_staff ps ON ps.program_id = p.id AND ps.staff_id = ?
             WHERE (p.created_by = ? OR ps.staff_id = ?) AND p.status <> 'deleted'
             ORDER BY p.id ASC"
        );
        $stmt->bind_param('iii', $staffUserId, $staffUserId, $staffUserId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        return $rows;
    }
}
