<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

define('APP_NAME', 'SIPEDO');
define('APP_SUBTITLE', 'Sistem Pengelolaan Donasi');

function sipedo_initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $a = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $b = strtoupper(substr($parts[1] ?? '', 0, 1));
    return $a . $b;
}

function sipedo_date_id(?string $date): string {
    if (!$date || $date === '0000-00-00') return '-';
    $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $ts = strtotime($date);
    if (!$ts) return $date;
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function sipedo_default_program_image(string $kode, string $name): string {
    $key = strtoupper(trim($kode));
    $slug = strtolower(trim($name));
    $map = [
        'PR-01' => 'assets/images/beasiswa-anak-yatim.jpg',
        'PR-02' => 'assets/images/renovasi-panti-asuhan.jpg',
        'PR-03' => 'assets/images/bantuan-bencana-alam.jpg',
        'PR-04' => 'assets/images/pengobatan-gratis.jpg',
    ];
    if (isset($map[$key])) return $map[$key];
    if (str_contains($slug, 'beasiswa') || str_contains($slug, 'yatim')) return $map['PR-01'];
    if (str_contains($slug, 'panti') || str_contains($slug, 'renovasi')) return $map['PR-02'];
    if (str_contains($slug, 'bencana')) return $map['PR-03'];
    if (str_contains($slug, 'pengobatan') || str_contains($slug, 'medis')) return $map['PR-04'];
    return '';
}

function sipedo_program_image_path(string $kode, string $name, ?string $image): string {
    $image = trim((string)$image);
    if ($image !== '') {
        if (str_starts_with($image, 'public/')) return substr($image, 7);
        return ltrim($image, '/');
    }
    return sipedo_default_program_image($kode, $name);
}


function sipedo_sync_program_images(mysqli $conn): void {
    $updates = [
        'PR-01' => 'assets/images/beasiswa-anak-yatim.jpg',
        'PR-02' => 'assets/images/renovasi-panti-asuhan.jpg',
        'PR-03' => 'assets/images/bantuan-bencana-alam.jpg',
        'PR-04' => 'assets/images/pengobatan-gratis.jpg',
    ];

    $stmt = $conn->prepare("UPDATE programs SET image=? WHERE kode=? AND (image IS NULL OR image='' OR image LIKE 'assets/uploads/programs/prog-20260504-%')");
    foreach ($updates as $kode => $image) {
        $stmt->bind_param('ss', $image, $kode);
        $stmt->execute();
    }
}

function sipedo_load_session_from_db(): bool {
    if (!db_ready()) return false;
    $conn = db();
    sipedo_sync_program_images($conn);


    $_SESSION['users'] = [];
    $res = $conn->query("SELECT * FROM users ORDER BY id ASC");
    while ($u = $res->fetch_assoc()) {
        $_SESSION['users'][$u['email']] = [
            'db_id'    => (int)$u['id'],
            'pass'     => $u['password'],
            'role'     => $u['role'],
            'name'     => $u['name'],

            'email'    => $u['email'],
            'initials' => $u['initials'] ?: sipedo_initials($u['name']),
            'color'    => $u['color'] ?: '#059669',
            'photo'    => $u['photo'] ?? '',
        ];
    }


    $_SESSION['programs'] = [];
    $res = $conn->query("SELECT * FROM programs WHERE status <> 'deleted' ORDER BY id ASC");
    while ($p = $res->fetch_assoc()) {
        $target = (float)$p['target'];
        $collected = (float)$p['collected'];
        $pct = $target > 0 ? round(($collected / $target) * 100, 1) : (float)$p['pct'];
        $_SESSION['programs'][] = [
            'num_id'     => (int)$p['id'],
            'id'         => $p['kode'],
            'name'       => $p['name'],
            'cat'        => $p['category'],
            'target'     => $target / 1000000,
            'collected'  => $collected / 1000000,
            'pct'        => $pct,
            'deadline'   => sipedo_date_id($p['deadline']),
            'deadline_raw' => $p['deadline'],
            'status'     => $p['status'],
            'image'      => sipedo_program_image_path($p['kode'], $p['name'], $p['image'] ?? ''),
            'desc'       => $p['description'] ?? '',
            'gradient'   => $p['gradient'] ?: 'linear-gradient(135deg,#0D1B3E,#2A4080)',
            'created_by' => (int)$p['created_by'],
        ];
    }


    $_SESSION['donations'] = [];
    $sql = "SELECT d.*, u.name donor_name, u.initials donor_initials, u.color donor_color, p.name program_name, p.kode program_kode, ps.name processed_name
            FROM donations d
            JOIN users u ON u.id = d.user_id
            JOIN programs p ON p.id = d.program_id
            LEFT JOIN users ps ON ps.id = d.processed_by
            ORDER BY d.donated_at DESC, d.id DESC";
    $res = $conn->query($sql);
    while ($d = $res->fetch_assoc()) {
        $_SESSION['donations'][] = [
            'num_id'      => (int)$d['id'],
            'id'          => $d['kode'],
            'donor'       => $d['donor_name'],
            'init'        => $d['donor_initials'] ?: sipedo_initials($d['donor_name']),
            'col'         => $d['donor_color'] ?: '#059669',
            'program'     => $d['program_name'],
            'progId'      => $d['program_kode'],
            'amount'      => number_format((float)$d['amount'], 0, ',', '.'),
            'method'      => $d['method'],
            'date'        => sipedo_date_id($d['donated_at']),
            'status'      => $d['status'],
            'processedBy' => $d['processed_name'] ?: '—',
            'proof'       => $d['proof'] ?? '',

            'note'        => $d['note'] ?? '',
        ];
    }


    $_SESSION['staffList'] = [];
    $sql = "SELECT sp.*, u.name, u.email FROM staff_profiles sp JOIN users u ON u.id = sp.user_id ORDER BY sp.id ASC";
    $res = $conn->query($sql);
    while ($s = $res->fetch_assoc()) {
        $_SESSION['staffList'][] = [
            'num_id' => (int)$s['id'],
            'user_id'=> (int)$s['user_id'],
            'id'     => $s['kode'],
            'name'   => $s['name'],
            'email'  => $s['email'],
            'role'   => $s['jabatan'],
            'since'  => sipedo_date_id($s['joined_at']),
            'status' => $s['status'],
        ];
    }


    $_SESSION['logs'] = [];
    $res = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC, id DESC LIMIT 100");
    while ($l = $res->fetch_assoc()) {
        $_SESSION['logs'][] = [
            'no'    => (int)$l['id'],
            'time'  => sipedo_date_id($l['created_at']),
            'actor' => $l['actor_name'],
            'role'  => $l['role'],
            'desc'  => $l['description'],
            'ref'   => $l['ref'],
        ];
    }


    $conn->query("CREATE TABLE IF NOT EXISTS `program_staff` (
        `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
        `program_id` int UNSIGNED NOT NULL,
        `staff_id` int UNSIGNED NOT NULL,
        `added_by` int UNSIGNED NOT NULL,
        `role_in_program` varchar(80) NOT NULL DEFAULT 'Anggota',
        `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_program_staff` (`program_id`,`staff_id`),
        KEY `idx_ps_program` (`program_id`),
        KEY `idx_ps_staff` (`staff_id`),
        CONSTRAINT `fk_ps_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_ps_staff` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_ps_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");


    $conn->query("INSERT IGNORE INTO program_staff (program_id, staff_id, added_by, role_in_program)
        SELECT p.id, p.created_by, p.created_by, 'Koordinator'
        FROM programs p JOIN users u ON u.id = p.created_by AND u.role = 'staff'
        WHERE p.status <> 'deleted'");


    if (isset($_SESSION['currentUser']['email'])) {
        $email = $_SESSION['currentUser']['email'];
        if (isset($_SESSION['users'][$email])) {
            $_SESSION['currentUser'] = $_SESSION['users'][$email];
            $_SESSION['currentUser']['email'] = $email;
            $_SESSION['currentRole'] = $_SESSION['currentUser']['role'];
        }
    }


    $_SESSION['settings'] = [];
    $res = $conn->query("SELECT `key`, `value` FROM settings");
    while ($row = $res->fetch_assoc()) {
        $_SESSION['settings'][$row['key']] = $row['value'];
    }

    return true;
}

function sipedo_load_fallback_data(): void {
    if (!isset($_SESSION['users'])) {
        $_SESSION['users'] = [
            'admin@s.id' => ['db_id' => 1, 'email' => 'admin@s.id', 'pass' => '123', 'role' => 'admin',   'name' => 'Ahmad Haris',    'initials' => 'AH', 'color' => '#2563eb', 'photo' => ''],
            'staff@s.id' => ['db_id' => 2, 'email' => 'staff@s.id', 'pass' => '123', 'role' => 'staff',   'name' => 'Dina Ramadhani', 'initials' => 'DR', 'color' => '#d97706', 'photo' => ''],
            'don@s.id'   => ['db_id' => 3, 'email' => 'don@s.id',   'pass' => '123', 'role' => 'donatur', 'name' => 'Siti Rahayu',    'initials' => 'SR', 'color' => '#059669', 'photo' => ''],
        ];
    }
    if (!isset($_SESSION['donations'])) {
        $_SESSION['donations'] = [
            ['id' => 'DN-2024', 'donor' => 'Siti Rahayu',   'init' => 'SR', 'col' => '#2563eb', 'program' => 'Beasiswa Anak Yatim',   'progId' => 'PR-01', 'amount' => '500.000',   'method' => 'BCA Transfer',     'date' => '12 Des 2024', 'status' => 'pending',  'processedBy' => '—',          'proof' => '', 'note' => ''],
            ['id' => 'DN-2023', 'donor' => 'Budi Pratama',  'init' => 'BP', 'col' => '#7c3aed', 'program' => 'Renovasi Panti Asuhan', 'progId' => 'PR-02', 'amount' => '1.200.000', 'method' => 'Mandiri Transfer', 'date' => '12 Des 2024', 'status' => 'pending',  'processedBy' => '—',          'proof' => '', 'note' => ''],
            ['id' => 'DN-2022', 'donor' => 'Rina Nurcahya', 'init' => 'RN', 'col' => '#d97706', 'program' => 'Bantuan Bencana',       'progId' => 'PR-03', 'amount' => '250.000',   'method' => 'BRI Transfer',     'date' => '11 Des 2024', 'status' => 'verified', 'processedBy' => 'Staff Dina', 'proof' => '', 'note' => ''],
            ['id' => 'DN-2021', 'donor' => 'Andi Setiawan', 'init' => 'AS', 'col' => '#059669', 'program' => 'Beasiswa Anak Yatim',   'progId' => 'PR-01', 'amount' => '750.000',   'method' => 'QRIS',             'date' => '11 Des 2024', 'status' => 'verified', 'processedBy' => 'Staff Reza', 'proof' => '', 'note' => ''],
            ['id' => 'DN-2020', 'donor' => 'Maya Kusuma',   'init' => 'MK', 'col' => '#dc2626', 'program' => 'Pengobatan Gratis',     'progId' => 'PR-04', 'amount' => '2.000.000', 'method' => 'BNI Transfer',     'date' => '10 Des 2024', 'status' => 'rejected', 'processedBy' => 'Staff Dina', 'proof' => '', 'note' => 'Bukti transfer tidak terbaca, mohon kirim ulang dengan gambar yang lebih jelas.'],
        ];
    }
    if (!isset($_SESSION['programs'])) {
        $_SESSION['programs'] = [
            ['id' => 'PR-01', 'name' => 'Beasiswa Anak Yatim',   'cat' => 'Pendidikan',  'target' => 50,  'collected' => 48.3, 'pct' => 96.6, 'deadline' => '31 Des 2024', 'deadline_raw' => '2024-12-31', 'status' => 'active', 'image' => 'assets/images/beasiswa-anak-yatim.jpg', 'desc' => 'Program beasiswa pendidikan untuk anak-anak yatim piatu kurang mampu.',  'gradient' => 'linear-gradient(135deg,#0D1B3E,#2A4080)', 'created_by' => 2],
            ['id' => 'PR-02', 'name' => 'Renovasi Panti Asuhan', 'cat' => 'Sosial',      'target' => 80,  'collected' => 72.1, 'pct' => 90.1, 'deadline' => '15 Jan 2025', 'deadline_raw' => '2025-01-15', 'status' => 'active', 'image' => 'assets/images/renovasi-panti-asuhan.jpg', 'desc' => 'Perbaikan fasilitas panti asuhan demi kenyamanan penghuni.',           'gradient' => 'linear-gradient(135deg,#065F46,#0F9D58)', 'created_by' => 2],
            ['id' => 'PR-03', 'name' => 'Bantuan Bencana Alam',  'cat' => 'Kedaruratan', 'target' => 120, 'collected' => 55,   'pct' => 45.8, 'deadline' => '28 Feb 2025', 'deadline_raw' => '2025-02-28', 'status' => 'active', 'image' => 'assets/images/bantuan-bencana-alam.jpg', 'desc' => 'Penyaluran bantuan darurat untuk korban bencana alam.',                'gradient' => 'linear-gradient(135deg,#B45309,#F59E0B)', 'created_by' => 4],
            ['id' => 'PR-04', 'name' => 'Pengobatan Gratis',     'cat' => 'Kesehatan',   'target' => 30,  'collected' => 30,   'pct' => 100,  'deadline' => '01 Des 2024', 'deadline_raw' => '2024-12-01', 'status' => 'closed', 'image' => 'assets/images/pengobatan-gratis.jpg', 'desc' => 'Layanan pemeriksaan dan pengobatan gratis untuk dhuafa.',              'gradient' => 'linear-gradient(135deg,#7C3AED,#A78BFA)', 'created_by' => 5],
        ];
    }
    if (!isset($_SESSION['staffList'])) {
        $_SESSION['staffList'] = [
            ['id' => 'STF-01', 'name' => 'Dina Ramadhani', 'email' => 'staff@s.id',       'role' => 'Staff Verifikasi', 'since' => '01 Nov 2024', 'status' => 'active'],
            ['id' => 'STF-02', 'name' => 'Reza Kurniawan', 'email' => 'reza@sipedo.org', 'role' => 'Staff Verifikasi', 'since' => '15 Nov 2024', 'status' => 'active'],
            ['id' => 'STF-03', 'name' => 'Hana Permata',   'email' => 'hana@sipedo.org', 'role' => 'Staff Senior',     'since' => '10 Okt 2024', 'status' => 'active'],
        ];
    }
    if (!isset($_SESSION['logs'])) {
        $_SESSION['logs'] = [
            ['no' => 15, 'time' => '12 Des 2024, 10:05', 'actor' => 'Admin Ahmad H.', 'role' => 'Admin', 'desc' => 'Menambah staff baru',      'ref' => 'STF-04'],
            ['no' => 14, 'time' => '12 Des 2024, 09:42', 'actor' => 'Staff Dina',     'role' => 'Staff', 'desc' => 'Memverifikasi donasi',     'ref' => '#DN-2022'],
            ['no' => 13, 'time' => '12 Des 2024, 09:30', 'actor' => 'Staff Reza',     'role' => 'Staff', 'desc' => 'Menolak donasi',           'ref' => '#DN-2020'],
        ];
    }
}

if (!sipedo_load_session_from_db()) {
    $_SESSION['db_warning'] = 'Database belum terhubung atau belum di-import. Aplikasi memakai data dummy sementara.';
    sipedo_load_fallback_data();


    if (isset($_SESSION['currentUser']['email'])) {
        $email = $_SESSION['currentUser']['email'];
        if (isset($_SESSION['users'][$email])) {
            $refreshed = $_SESSION['users'][$email];
            $refreshed['email'] = $email;

            $_SESSION['currentUser'] = array_merge($_SESSION['currentUser'], $refreshed);
        }
    }
} else {
    unset($_SESSION['db_warning']);
}
