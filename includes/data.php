<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        'admin@s.id' => ['pass' => '123', 'role' => 'admin',   'name' => 'Ahmad Haris',     'initials' => 'AH', 'color' => '#2563eb', 'photo' => ''],
        'staff@s.id' => ['pass' => '123', 'role' => 'staff',   'name' => 'Dina Ramadhani',  'initials' => 'DR', 'color' => '#d97706', 'photo' => ''],
        'don@s.id'   => ['pass' => '123', 'role' => 'donatur', 'name' => 'Siti Rahayu',     'initials' => 'SR', 'color' => '#059669', 'photo' => ''],
    ];
}

if (!isset($_SESSION['donations'])) {
    $_SESSION['donations'] = [
        ['id' => 'DN-2024', 'donor' => 'Siti Rahayu',   'init' => 'SR', 'col' => '#2563eb', 'program' => 'Beasiswa Anak Yatim',   'progId' => 'PR-01', 'amount' => '500.000',   'method' => 'BCA Transfer',     'date' => '12 Des 2024', 'status' => 'pending',  'processedBy' => '—',          'proof' => ''],
        ['id' => 'DN-2023', 'donor' => 'Budi Pratama',  'init' => 'BP', 'col' => '#7c3aed', 'program' => 'Renovasi Panti Asuhan', 'progId' => 'PR-02', 'amount' => '1.200.000', 'method' => 'Mandiri Transfer', 'date' => '12 Des 2024', 'status' => 'pending',  'processedBy' => '—',          'proof' => ''],
        ['id' => 'DN-2022', 'donor' => 'Rina Nurcahya', 'init' => 'RN', 'col' => '#d97706', 'program' => 'Bantuan Bencana',       'progId' => 'PR-03', 'amount' => '250.000',   'method' => 'BRI Transfer',     'date' => '11 Des 2024', 'status' => 'verified', 'processedBy' => 'Staff Dina', 'proof' => ''],
        ['id' => 'DN-2021', 'donor' => 'Andi Setiawan', 'init' => 'AS', 'col' => '#059669', 'program' => 'Beasiswa Anak Yatim',   'progId' => 'PR-01', 'amount' => '750.000',   'method' => 'QRIS',             'date' => '11 Des 2024', 'status' => 'verified', 'processedBy' => 'Staff Reza', 'proof' => ''],
        ['id' => 'DN-2020', 'donor' => 'Maya Kusuma',   'init' => 'MK', 'col' => '#dc2626', 'program' => 'Pengobatan Gratis',     'progId' => 'PR-04', 'amount' => '2.000.000', 'method' => 'BNI Transfer',     'date' => '10 Des 2024', 'status' => 'rejected', 'processedBy' => 'Staff Dina', 'proof' => ''],
    ];
}

if (!isset($_SESSION['programs'])) {
    $_SESSION['programs'] = [
        ['id' => 'PR-01', 'name' => 'Beasiswa Anak Yatim',     'cat' => 'Pendidikan',  'target' => 50,  'collected' => 48.3, 'pct' => 96.6, 'deadline' => '31 Des 2024', 'status' => 'active', 'image' => '', 'desc' => 'Program beasiswa pendidikan untuk anak-anak yatim piatu kurang mampu.', 'gradient' => 'linear-gradient(135deg,#0D1B3E,#2A4080)'],
        ['id' => 'PR-02', 'name' => 'Renovasi Panti Asuhan',   'cat' => 'Sosial',      'target' => 80,  'collected' => 72.1, 'pct' => 90.1, 'deadline' => '15 Jan 2025', 'status' => 'active', 'image' => '', 'desc' => 'Perbaikan fasilitas panti asuhan demi kenyamanan penghuni.',          'gradient' => 'linear-gradient(135deg,#065F46,#0F9D58)'],
        ['id' => 'PR-03', 'name' => 'Bantuan Bencana Alam',    'cat' => 'Kedaruratan', 'target' => 120, 'collected' => 55,   'pct' => 45.8, 'deadline' => '28 Feb 2025', 'status' => 'active', 'image' => '', 'desc' => 'Penyaluran bantuan darurat untuk korban bencana alam.',               'gradient' => 'linear-gradient(135deg,#B45309,#F59E0B)'],
        ['id' => 'PR-04', 'name' => 'Pengobatan Gratis',       'cat' => 'Kesehatan',   'target' => 30,  'collected' => 30,   'pct' => 100,  'deadline' => '01 Des 2024', 'status' => 'closed', 'image' => '', 'desc' => 'Layanan pemeriksaan dan pengobatan gratis untuk dhuafa.',             'gradient' => 'linear-gradient(135deg,#7C3AED,#A78BFA)'],
    ];
}

if (!isset($_SESSION['staffList'])) {
    $_SESSION['staffList'] = [
        ['id' => 'STF-01', 'name' => 'Dina Ramadhani', 'email' => 'dina@sipedo.org', 'role' => 'Staff Verifikasi', 'since' => '01 Nov 2024', 'status' => 'active'],
        ['id' => 'STF-02', 'name' => 'Reza Kurniawan', 'email' => 'reza@sipedo.org', 'role' => 'Staff Verifikasi', 'since' => '15 Nov 2024', 'status' => 'active'],
        ['id' => 'STF-03', 'name' => 'Hana Permata', 'email' => 'hana@sipedo.org', 'role' => 'Staff Senior', 'since' => '10 Okt 2024', 'status' => 'active'],
    ];
}

if (!isset($_SESSION['logs'])) {
    $_SESSION['logs'] = [
        ['no' => 15, 'time' => '12 Des 2024, 10:05', 'actor' => 'Admin Ahmad H.', 'role' => 'Admin', 'desc' => 'Menambah staff baru', 'ref' => 'STF-04'],
        ['no' => 14, 'time' => '12 Des 2024, 09:42', 'actor' => 'Staff Dina', 'role' => 'Staff', 'desc' => 'Memverifikasi donasi', 'ref' => '#DN-2022'],
        ['no' => 13, 'time' => '12 Des 2024, 09:30', 'actor' => 'Staff Reza', 'role' => 'Staff', 'desc' => 'Menolak donasi', 'ref' => '#DN-2020'],
    ];
}

