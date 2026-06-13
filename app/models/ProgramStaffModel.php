<?php

class ProgramStaffModel {
    private static function refresh(): void {
        if (function_exists('sipedo_load_session_from_db')) sipedo_load_session_from_db();
    }

    /**
     * Cek apakah staff sudah terdaftar di sebuah program.
     */
    public static function exists(int $programId, int $staffId): bool {
        if (db_ready()) {
            $conn = db();
            $stmt = $conn->prepare(
                "SELECT id FROM program_staff
                 WHERE program_id = ? AND staff_id = ?
                 LIMIT 1"
            );
            $stmt->bind_param('ii', $programId, $staffId);
            $stmt->execute();
            return (bool)$stmt->get_result()->fetch_assoc();
        }
        return false;
    }

    /**
     * Tambah staff ke program.
     * Mengembalikan: true (berhasil), 'exists' (sudah ada), 'not_owner' (bukan pemilik/anggota).
     */
    public static function add(int $programId, int $staffId, int $addedBy, string $roleInProgram = 'Anggota'): bool|string {
        if (!db_ready()) return false;

        $conn = db();

        // Cek apakah addedBy adalah pemilik program atau anggota
        $chk = $conn->prepare(
            "SELECT id FROM programs WHERE id = ? AND created_by = ? LIMIT 1"
        );
        $chk->bind_param('ii', $programId, $addedBy);
        $chk->execute();
        $isOwner = (bool)$chk->get_result()->fetch_assoc();

        if (!$isOwner) {
            $chk2 = $conn->prepare(
                "SELECT id FROM program_staff WHERE program_id = ? AND staff_id = ? LIMIT 1"
            );
            $chk2->bind_param('ii', $programId, $addedBy);
            $chk2->execute();
            $isOwner = (bool)$chk2->get_result()->fetch_assoc();
        }
        if (!$isOwner) return 'not_owner';

        // Cek duplikat
        if (self::exists($programId, $staffId)) return 'exists';

        $stmt = $conn->prepare(
            "INSERT INTO program_staff (program_id, staff_id, added_by, role_in_program)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('iiis', $programId, $staffId, $addedBy, $roleInProgram);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Hapus staff dari program berdasarkan id baris program_staff.
     * Mengembalikan: true (berhasil), false (tidak ditemukan), 'not_owner' (tidak berwenang).
     */
    public static function remove(int $psId, int $actorId): bool|string {
        if (!db_ready()) return false;

        $conn = db();

        $row = $conn->prepare(
            "SELECT ps.*, p.created_by
             FROM program_staff ps
             JOIN programs p ON p.id = ps.program_id
             WHERE ps.id = ?
             LIMIT 1"
        );
        $row->bind_param('i', $psId);
        $row->execute();
        $data = $row->get_result()->fetch_assoc();
        if (!$data) return false;

        if ((int)$data['created_by'] !== $actorId && (int)$data['added_by'] !== $actorId) {
            return 'not_owner';
        }

        $del = $conn->prepare("DELETE FROM program_staff WHERE id = ?");
        $del->bind_param('i', $psId);
        $del->execute();
        return $del->affected_rows > 0;
    }

    /**
     * Ambil semua relasi staff-program yang melibatkan satu staff
     * (sebagai pemilik program atau sebagai anggota).
     */
    public static function byStaff(int $staffUserId): array {
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
             JOIN programs p ON p.id  = ps.program_id
             JOIN users    u ON u.id  = ps.staff_id
             JOIN users   ab ON ab.id = ps.added_by
             WHERE p.created_by = ? OR ps.staff_id = ?
             ORDER BY p.id ASC, ps.joined_at ASC"
        );
        $stmt->bind_param('ii', $staffUserId, $staffUserId);
        $stmt->execute();
        $res  = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        return $rows;
    }

    /**
     * Ambil daftar program yang boleh dikelola oleh satu staff.
     */
    public static function programsByStaff(int $staffUserId): array {
        if (!db_ready()) return [];

        $conn = db();
        $stmt = $conn->prepare(
            "SELECT DISTINCT p.id, p.kode, p.name, p.status
             FROM programs p
             LEFT JOIN program_staff ps ON ps.program_id = p.id AND ps.staff_id = ?
             WHERE (p.created_by = ? OR ps.staff_id = ?)
               AND p.status <> 'deleted'
             ORDER BY p.id ASC"
        );
        $stmt->bind_param('iii', $staffUserId, $staffUserId, $staffUserId);
        $stmt->execute();
        $res  = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        return $rows;
    }

    /**
     * Pindahkan kepemilikan program ke staff lain ketika koordinator dihapus.
     * Dipanggil dari StaffModel::deleteWithTransfer().
     */
    public static function transferOwnership(int $programId, int $oldOwnerId, int $newOwnerId): void {
        if (!db_ready()) return;

        $conn = db();

        // Update tabel programs
        $upd = $conn->prepare("UPDATE programs SET created_by = ? WHERE id = ?");
        $upd->bind_param('ii', $newOwnerId, $programId);
        $upd->execute();

        // Tandai koordinator baru di program_staff
        $upd2 = $conn->prepare(
            "UPDATE program_staff
             SET role_in_program = 'Koordinator'
             WHERE program_id = ? AND staff_id = ?"
        );
        $upd2->bind_param('ii', $programId, $newOwnerId);
        $upd2->execute();
    }
}
