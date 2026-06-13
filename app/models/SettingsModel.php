<?php

class SettingsModel {
    private static function refresh(): void {
        if (function_exists('sipedo_load_session_from_db')) sipedo_load_session_from_db();
    }

    /**
     * Ambil semua setting sebagai array asosiatif [key => value].
     */
    public static function all(): array {
        self::refresh();
        return $_SESSION['settings'] ?? [];
    }

    /**
     * Ambil satu nilai setting berdasarkan key.
     * Kembalikan $default jika key tidak ditemukan.
     */
    public static function get(string $key, string $default = ''): string {
        self::refresh();
        return (string)($_SESSION['settings'][$key] ?? $default);
    }

    /**
     * Simpan satu nilai setting berdasarkan key.
     */
    public static function set(string $key, string $value): bool {
        if (db_ready()) {
            $conn = db();
            $stmt = $conn->prepare(
                "UPDATE settings
                 SET value = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE `key` = ?"
            );
            $stmt->bind_param('ss', $value, $key);
            $ok = $stmt->execute();
            self::refresh();
            return $ok;
        }

        if (!isset($_SESSION['settings'])) {
            $_SESSION['settings'] = [];
        }
        $_SESSION['settings'][$key] = $value;
        return true;
    }

    /**
     * Simpan banyak setting sekaligus dari array [key => value].
     */
    public static function setMany(array $data): bool {
        $ok = true;
        foreach ($data as $key => $value) {
            if (!self::set($key, (string)$value)) {
                $ok = false;
            }
        }
        return $ok;
    }
}
