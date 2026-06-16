<?php

class AppController {
    private array $menus = [
        'admin' => [
            'Manajemen' => [
                'dash-admin'   => 'Dashboard',
                'pengguna'     => 'Pengguna & Staff',
                'program-admin'=> 'Program Bantuan',
                'rekap-donasi' => 'Rekap Donasi',
            ],
            'Sistem' => [
                'log'          => 'Log Aktivitas',
                'laporan'      => 'Laporan & Ekspor',
                'pengaturan'   => 'Pengaturan Sistem',
                'profil-admin' => 'Profil Saya',
            ],
        ],
        'staff' => [
            'Menu Staff' => [
                'dash-staff'    => 'Dashboard',
                'verifikasi'    => 'Verifikasi Donasi',
                'program-staff' => 'Program Bantuan',
                'draft-program' => 'Draft Program',
                'tambah-program'=> 'Tambah Program',
                'rekan-staff'   => 'Rekan Se-Program',
                'progress-staff'=> 'Progress & Donatur',
                'riwayat-staff' => 'Riwayat Verifikasi',
                'profil-staff'  => 'Profil Saya',
            ],
        ],
        'donatur' => [
            'Menu Donatur' => [
                'dash-donatur'   => 'Beranda',
                'program-donatur'=> 'Jelajahi Program',
                'riwayat-donasi' => 'Riwayat Donasi',
                'profil-donatur' => 'Profil Saya',
            ],
        ],
    ];

    private array $titles = [
        'dash-admin'    => 'Dashboard Admin',
        'pengguna'      => 'Pengguna & Staff',
        'program-admin' => 'Program Bantuan',
        'rekap-donasi'  => 'Rekap Donasi',
        'log'           => 'Log Aktivitas',
        'laporan'       => 'Laporan & Ekspor',
        'pengaturan'    => 'Pengaturan Sistem',
        'profil-admin'  => 'Profil Saya',
        'dash-staff'    => 'Dashboard Staff',
        'verifikasi'    => 'Panel Verifikasi Donasi',
        'program-staff' => 'Program Bantuan',
        'draft-program' => 'Draft Program',
        'tambah-program'=> 'Tambah Program Baru',
        'edit-program'  => 'Edit Program',
        'rekan-staff'   => 'Rekan Se-Program',
        'progress-staff'=> 'Progress & Donatur',
        'riwayat-staff' => 'Riwayat Verifikasi',
        'profil-staff'  => 'Profil Saya',
        'dash-donatur'  => 'Beranda',
        'program-donatur'=> 'Jelajahi Program',
        'program-detail'=> 'Detail Program',
        'riwayat-donasi'=> 'Riwayat Donasi',
        'profil-donatur'=> 'Profil Saya',
    ];

    public function show(): void {
        require_login();

        $role        = current_role();
        $defaultPage = ['admin' => 'dash-admin', 'staff' => 'dash-staff', 'donatur' => 'dash-donatur'][$role] ?? 'dash-donatur';
        $page        = $_GET['page'] ?? $defaultPage;
        $user        = current_user();
        $menus       = $this->menus[$role] ?? [];
        $titles      = $this->titles;


        $staffOnlyPages  = ['verifikasi','dash-staff','program-staff','draft-program','tambah-program',
                            'edit-program','rekan-staff','progress-staff','riwayat-staff','profil-staff'];
        $adminOnlyPages  = ['dash-admin','pengguna','program-admin','rekap-donasi',
                            'log','laporan','pengaturan','profil-admin'];
        $donaturOnlyPages= ['dash-donatur','program-donatur',
                            'riwayat-donasi','profil-donatur'];

        $isStaffPage  = in_array($page, $staffOnlyPages,   true);
        $isAdminPage  = in_array($page, $adminOnlyPages,   true);
        $isDonaturPage= in_array($page, $donaturOnlyPages, true);


        if ($role === 'donatur' && ($isStaffPage || $isAdminPage)) {
            flash('Akses ditolak.', 'error');
            redirect_to(app_url($defaultPage));
        }

        if ($role === 'staff' && $isAdminPage) {
            flash('Akses ditolak.', 'error');
            redirect_to(app_url($defaultPage));
        }

        if ($role === 'admin' && in_array($page, ['dash-donatur','program-donatur','riwayat-donasi'], true)) {
            redirect_to(app_url($defaultPage));
        }


        $pageCss = App::basePath() . '/public/assets/css/' . basename($page) . '.css';
        $hasPageCss = file_exists($pageCss);


        $pageFile = App::basePath() . '/app/Views/' . $this->resolveViewDir($role, $page) . '/' . basename($page) . '.php';
        if (!file_exists($pageFile)) {
            $pageFile = App::basePath() . '/app/Views/' . $this->resolveViewDir($role, $defaultPage) . '/' . basename($defaultPage) . '.php';
            $page = $defaultPage;
        }

        View::render('shared/layout', compact(
            'role', 'page', 'user', 'menus', 'titles', 'hasPageCss', 'pageFile', 'defaultPage'
        ));
    }

    private function resolveViewDir(string $role, string $page): string {

        $adminPages  = ['dash-admin','pengguna','program-admin','rekap-donasi','log','laporan','pengaturan','profil-admin'];
        $staffPages  = ['dash-staff','verifikasi','program-staff','draft-program','tambah-program','edit-program','rekan-staff','progress-staff','riwayat-staff','profil-staff'];
        $donaturPages= ['dash-donatur','program-donatur','riwayat-donasi','profil-donatur'];

        if (in_array($page, $adminPages,   true)) return 'admin';
        if (in_array($page, $staffPages,   true)) return 'staff';
        if (in_array($page, $donaturPages, true)) return 'donatur';
        // Halaman shared yang bisa diakses semua role — pakai view donatur
        if ($page === 'program-detail') return 'donatur';


        return match($role) {
            'admin'  => 'admin',
            'staff'  => 'staff',
            default  => 'donatur',
        };
    }
}
