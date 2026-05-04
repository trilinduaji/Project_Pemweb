<?php
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$role = current_role();
$defaultPage = ['admin' => 'dash-admin', 'staff' => 'dash-staff', 'donatur' => 'dash-donatur'][$role] ?? 'dash-donatur';
$requestedPage = $_GET['page'] ?? $defaultPage;
$page = $defaultPage;
$user = current_user();

$menus = [
    'admin' => [
        'Manajemen' => [
            'dash-admin' => 'Dashboard',
            'pengguna' => 'Pengguna & Staff',
            'program-admin' => 'Program Bantuan',
            'rekap-donasi' => 'Rekap Donasi',
        ],
        'Sistem' => [
            'log' => 'Log Aktivitas',
            'pengaturan' => 'Pengaturan Sistem',
            'profil-admin' => 'Profil Saya',
        ],
    ],
    'staff' => [
        'Menu Staff' => [
            'dash-staff' => 'Dashboard',
            'verifikasi' => 'Verifikasi Donasi',
            'program-staff' => 'Program Bantuan',
            'tambah-program' => 'Tambah Program',
            'progress-staff' => 'Progress & Donatur',
            'riwayat-staff' => 'Riwayat Verifikasi',
            'profil-staff' => 'Profil Saya',
        ],
    ],
    'donatur' => [
        'Menu Donatur' => [
            'dash-donatur' => 'Beranda',
            'program-donatur' => 'Jelajahi Program',
            'riwayat-donasi' => 'Riwayat Donasi',
            'profil-donatur' => 'Profil Saya',
        ],
    ],
];

$titles = [
    'dash-admin' => 'Dashboard Admin',
    'pengguna' => 'Pengguna & Staff',
    'program-admin' => 'Program Bantuan',
    'rekap-donasi' => 'Rekap Donasi',
    'log' => 'Log Aktivitas',
    'laporan' => 'Laporan & Ekspor',
    'pengaturan' => 'Pengaturan Sistem',
    'profil-admin' => 'Profil Saya',
    'dash-staff' => 'Dashboard Staff',
    'verifikasi' => 'Panel Verifikasi Donasi',
    'program-staff' => 'Program Bantuan',
    'tambah-program' => 'Tambah Program Baru',
    'edit-program' => 'Edit Program',
    'progress-staff' => 'Progress & Donatur',
    'riwayat-staff' => 'Riwayat Verifikasi',
    'profil-staff' => 'Profil Saya',
    'dash-donatur' => 'Beranda',
    'program-donatur' => 'Jelajahi Program',
    'program-detail' => 'Detail Program',
    'riwayat-donasi' => 'Riwayat Donasi',
    'profil-donatur' => 'Profil Saya',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPEDO - <?= e($titles[$defaultPage] ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php
    $pageCss = __DIR__ . '/assets/css/' . basename($defaultPage) . '.css';
    if (file_exists($pageCss)):
    ?>
        <link rel="stylesheet" href="assets/css/<?= e(basename($defaultPage)) ?>.css">
    <?php endif; ?>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <h1>SIPEDO</h1>
                <p>Sistem Pengelolaan Donasi</p>
            </div>

            <div class="user">
                <?= user_avatar($user) ?>
                <div>
                    <strong><?= e($user['name']) ?></strong>
                    <small><?= e(ucfirst($role)) ?></small>
                </div>
            </div>

            <?php foreach ($menus[$role] as $group => $items): ?>
                <div class="nav-title"><?= e($group) ?></div>
                <nav class="nav">
                    <?php foreach ($items as $key => $label): ?>
                        <a class="<?= is_active_page($page, $key) ?>" href="app.php?page=<?= e($key) ?>"><?= e($label) ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endforeach; ?>

            <form class="logout" action="actions/auth.php" method="post">
                <input type="hidden" name="action" value="logout">
                <button class="btn red full" type="submit">Keluar dari Akun</button>
            </form>
        </aside>

        <main class="main">
            <header class="topbar">
                <h2 class="page-title"><?= e($titles[$page] ?? 'Dashboard') ?></h2>
            </header>

            <section class="content">
                <?php show_flash(); ?>
                <?php
                $file = __DIR__ . '/pages/' . $defaultPage . '.php';
                include $file;
                ?>
            </section>
        </main>
    </div>
</body>
</html>

