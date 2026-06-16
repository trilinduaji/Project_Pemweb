<?php

class DonationModel {
    private static function refresh(): void {
        if (function_exists('sipedo_load_session_from_db')) sipedo_load_session_from_db();
    }

    private static function rupiahToNumber($amount): float {
        return (float)str_replace(['.', ','], ['', ''], (string)$amount);
    }

    public static function all(): array {
        self::refresh();
        return $_SESSION['donations'] ?? [];
    }

    public static function findById(string $id): ?array {
        self::refresh();
        foreach ($_SESSION['donations'] ?? [] as $d) {
            if (($d['id'] ?? '') === $id || (string)($d['num_id'] ?? '') === $id) return $d;
        }
        return null;
    }

    public static function byDonor(string $donorName): array {
        return array_values(array_filter(self::all(), fn($d) => ($d['donor'] ?? '') === $donorName));
    }

    public static function byStaff(int $staffUserId): array {
        if ($staffUserId <= 0) return [];

        $programs = ProgramModel::byStaff($staffUserId);
        $programCodes = array_map(fn($p) => (string)($p['id'] ?? ''), $programs);
        $programNums = array_map(fn($p) => (string)($p['num_id'] ?? ''), $programs);
        $allowed = array_filter(array_unique(array_merge($programCodes, $programNums)));

        if (empty($allowed)) return [];

        return array_values(array_filter(self::all(), function($d) use ($allowed) {
            $progId = (string)($d['progId'] ?? $d['program_id'] ?? '');
            return in_array($progId, $allowed, true);
        }));
    }

    public static function canProcess(string $id, ?array $user = null): bool {
        $user = $user ?: current_user();
        if (!$user) return false;
        if (($user['role'] ?? current_role()) === 'admin') return true;
        if (($user['role'] ?? current_role()) !== 'staff') return false;

        $donation = self::findById($id);
        if (!$donation) return false;

        $programId = (string)($donation['progId'] ?? '');
        if ($programId === '') return false;

        return ProgramModel::canManage($programId, $user);
    }

    public static function pending(): array {
        return array_values(array_filter(self::all(), fn($d) => ($d['status'] ?? '') === 'pending'));
    }

    public static function verified(): array {
        return array_values(array_filter(self::all(), fn($d) => ($d['status'] ?? '') === 'verified'));
    }

    public static function create(array $data): void {
        if (db_ready()) {
            $conn = db();
            $count = (int)$conn->query("SELECT COUNT(*) c FROM donations")->fetch_assoc()['c'];
            $kode = 'DN-' . (2025 + $count);
            $userId = current_user()['db_id'] ?? 3;

            $program = ProgramModel::findById($data['programId']);
            $programId = (int)($program['num_id'] ?? 0);
            if ($programId <= 0) {
                $stmtFind = $conn->prepare("SELECT id FROM programs WHERE kode=? LIMIT 1");
                $stmtFind->bind_param('s', $data['programId']);
                $stmtFind->execute();
                $row = $stmtFind->get_result()->fetch_assoc();
                $programId = (int)($row['id'] ?? 0);
            }
            if ($programId <= 0) return;

            $amount = (float)$data['amount'];
            $status = 'pending';
            $proof = $data['proof'] ?? '';
            $stmt = $conn->prepare("INSERT INTO donations (kode,user_id,program_id,amount,method,proof,status) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param('siidsss', $kode, $userId, $programId, $amount, $data['method'], $proof, $status);
            $stmt->execute();
            self::refresh();
            return;
        }

        array_unshift($_SESSION['donations'], [
            'id' => 'DN-' . (2025 + count($_SESSION['donations'] ?? [])),
            'donor' => $data['donor'],
            'init' => $data['initials'],
            'col' => $data['color'],
            'program' => $data['programName'],
            'progId' => $data['programId'],
            'amount' => number_format($data['amount'], 0, ',', '.'),
            'method' => $data['method'],
            'date' => 'Baru saja',
            'status' => 'pending',
            'processedBy' => '—',
            'proof' => $data['proof'],
            'note' => '',
        ]);
    }

    public static function updateStatus(string $id, string $status, string $processedBy, string $note = ''): bool {
        if (!in_array($status, ['pending','verified','rejected'], true)) return false;

        if (db_ready()) {
            $conn = db();
            $processorId = current_user()['db_id'] ?? null;
            $donation = self::findById($id);
            if (!$donation) return false;
            $oldStatus = $donation['status'] ?? 'pending';
            $kode = $donation['id'];

            $stmt = $conn->prepare("UPDATE donations SET status=?, processed_by=?, processed_at=NOW(), note=? WHERE kode=?");
            $stmt->bind_param('siss', $status, $processorId, $note, $kode);
            $ok = $stmt->execute();

            if ($ok && $oldStatus !== $status) {
                $amount = self::rupiahToNumber($donation['amount'] ?? 0);
                $progKode = $conn->real_escape_string($donation['progId'] ?? '');
                if ($status === 'verified' && $oldStatus !== 'verified') {
                    $conn->query("UPDATE programs SET collected = collected + {$amount}, pct = CASE WHEN target > 0 THEN ROUND(((collected + {$amount}) / target) * 100, 2) ELSE 0 END WHERE kode='{$progKode}'");
                } elseif ($oldStatus === 'verified' && $status !== 'verified') {
                    $conn->query("UPDATE programs SET collected = GREATEST(collected - {$amount}, 0), pct = CASE WHEN target > 0 THEN ROUND((GREATEST(collected - {$amount}, 0) / target) * 100, 2) ELSE 0 END WHERE kode='{$progKode}'");
                }
            }
            self::refresh();
            return $ok;
        }

        foreach ($_SESSION['donations'] as &$donation) {
            if (($donation['id'] ?? '') === $id) {
                $oldStatus = $donation['status'] ?? 'pending';
                $donation['status'] = $status;
                $donation['processedBy'] = $processedBy;
                $donation['note'] = $note;

                if ($oldStatus !== $status) {
                    $amount = self::rupiahToNumber($donation['amount'] ?? 0);
                    $progId = $donation['progId'] ?? '';
                    foreach ($_SESSION['programs'] as &$prog) {
                        if (($prog['id'] ?? '') === $progId) {
                            if ($status === 'verified' && $oldStatus !== 'verified') {
                                $prog['collected'] = round(((float)$prog['collected']) + ($amount / 1000000), 6);
                            } elseif ($oldStatus === 'verified' && $status !== 'verified') {
                                $prog['collected'] = max(0, round(((float)$prog['collected']) - ($amount / 1000000), 6));
                            }
                            $target = (float)($prog['target'] ?? 0);
                            $prog['pct'] = $target > 0 ? round((((float)$prog['collected']) / $target) * 100, 1) : 0;
                            break;
                        }
                    }
                    unset($prog);
                }
                unset($donation);
                return true;
            }
        }
        unset($donation);
        return false;
    }

    public static function totalCollectedRp(): int {
        $total = 0;
        foreach (self::verified() as $d) $total += (int)self::rupiahToNumber($d['amount'] ?? 0);
        return $total;
    }

    public static function uniqueDonors(): int {
        return count(array_unique(array_column(self::verified(), 'donor')));
    }

    public static function topDonors(int $limit = 5): array {
        $map = [];
        foreach (self::verified() as $d) {
            $key = $d['donor'];
            $nominal = (int)self::rupiahToNumber($d['amount'] ?? 0);
            if (!isset($map[$key])) $map[$key] = ['nama'=>$d['donor'], 'initials'=>$d['init'], 'color'=>$d['col'], 'total'=>0, 'count'=>0];
            $map[$key]['total'] += $nominal;
            $map[$key]['count']++;
        }
        usort($map, fn($a,$b) => $b['total'] <=> $a['total']);
        return array_slice(array_values($map), 0, $limit);
    }
}
