<?php

class ActivityLogModel {
    private static function refresh(): void {
        if (function_exists('sipedo_load_session_from_db')) sipedo_load_session_from_db();
    }

    /**
     * Ambil semua log aktivitas, urut dari terbaru.
     */
    public static function all(): array {
        self::refresh();
        return $_SESSION['logs'] ?? [];
    }

    /**
     * Tambah satu entri log.
     * Dipanggil via helper add_log() di helpers.php — method ini
     * menyediakan akses model langsung bila dibutuhkan dari controller.
     */
    public static function create(string $description, string $ref, ?int $userId = null): void {
        $user   = current_user();
        $role   = ucfirst(current_role() ?? 'User');
        $actor  = $user['name'] ?? 'System';
        $uid    = $userId ?? ($user['db_id'] ?? null);

        if (db_ready()) {
            $conn = db();
            $stmt = $conn->prepare(
                "INSERT INTO activity_logs (user_id, actor_name, role, description, ref)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('issss', $uid, $actor, $role, $description, $ref);
            $stmt->execute();
            self::refresh();
            return;
        }

        $next = count($_SESSION['logs'] ?? []) + 16;
        array_unshift($_SESSION['logs'], [
            'no'    => $next,
            'time'  => 'Baru saja',
            'actor' => $actor,
            'role'  => $role,
            'desc'  => $description,
            'ref'   => $ref,
        ]);
    }

    /**
     * Hapus semua log (hanya admin).
     */
    public static function clear(): bool {
        if (db_ready()) {
            $ok = db()->query("DELETE FROM activity_logs");
            self::refresh();
            return (bool)$ok;
        }
        $_SESSION['logs'] = [];
        return true;
    }

    /**
     * Ambil log milik satu user berdasarkan user_id.
     */
    public static function byUser(int $userId): array {
        if (db_ready()) {
            $conn = db();
            $stmt = $conn->prepare(
                "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC"
            );
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res  = $stmt->get_result();
            $rows = [];
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            return $rows;
        }

        return array_values(array_filter(
            $_SESSION['logs'] ?? [],
            fn($l) => ($l['actor'] ?? '') === (current_user()['name'] ?? '')
        ));
    }
}
