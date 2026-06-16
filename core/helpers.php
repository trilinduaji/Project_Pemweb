<?php


function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $url): never {
    header('Location: ' . $url);
    exit;
}

function current_user(): ?array {
    return $_SESSION['currentUser'] ?? null;
}

function current_role(): ?string {
    return $_SESSION['currentRole'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        redirect_to(base_url('auth/login'));
    }
}

function staff_is_inactive(): bool {
    if (current_role() !== 'staff') return false;
    return !StaffModel::isActiveUser((int)(current_user()['db_id'] ?? 0));
}

function require_active_staff_action(string $redirectPage, string $message = ''): void {
    if (!staff_is_inactive()) return;

    flash($message !== '' ? $message : 'Status staff kamu sedang nonaktif. Kamu hanya dapat melihat data dan tidak bisa melakukan aksi perubahan.', 'error');
    redirect_to(app_url($redirectPage));
}

function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function show_flash(): void {
    if (!isset($_SESSION['flash'])) return;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="flash flash-' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
}

function add_log(string $desc, string $ref): void {
    $user = current_user();
    $role = ucfirst(current_role() ?? 'User');
    $actor = $user['name'] ?? 'System';

    if (function_exists('db_ready') && db_ready()) {
        $conn = db();
        $userId = $user['db_id'] ?? null;
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, actor_name, role, description, ref) VALUES (?,?,?,?,?)");
        $stmt->bind_param('issss', $userId, $actor, $role, $desc, $ref);
        $stmt->execute();
        if (function_exists('sipedo_load_session_from_db')) sipedo_load_session_from_db();
        return;
    }

    $next = count($_SESSION['logs'] ?? []) + 16;
    array_unshift($_SESSION['logs'], [
        'no'    => $next,
        'time'  => 'Baru saja',
        'actor' => $actor,
        'role'  => $role,
        'desc'  => $desc,
        'ref'   => $ref,
    ]);
}

function badge(string $status): string {
    $labels = [
        'pending'  => 'Pending',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
        'active'   => 'Aktif',
        'inactive' => 'Nonaktif',
        'closed'   => 'Selesai',
        'deleted'  => 'Dihapus',
    ];
    return '<span class="badge badge-' . e($status) . '">' . e($labels[$status] ?? $status) . '</span>';
}

function program_badge(string $status): string {
    $labels = [
        'active'   => 'Aktif',
        'inactive' => 'Draft',
        'closed'   => 'Selesai',
        'deleted'  => 'Dihapus',
    ];
    return '<span class="badge badge-' . e($status) . '">' . e($labels[$status] ?? $status) . '</span>';
}

function avatar(string $initials, string $color): string {
    return '<span class="avatar" style="background:' . e($color) . '">' . e($initials) . '</span>';
}

function user_avatar(array $user): string {
    if (!empty($user['photo'])) {
        return '<span class="avatar avatar-photo"><img src="' . e(pub($user['photo'])) . '" alt=""></span>';
    }
    return avatar($user['initials'] ?? '?', $user['color'] ?? '#666');
}

function progress_bar($pct): string {
    return '<div class="progress-text">' . e($pct) . '%</div><div class="progress"><span style="width:' . e($pct) . '%"></span></div>';
}

function is_active_page(string $page, string $target): string {
    return $page === $target ? 'active' : '';
}

function base_url(string $path = ''): string {
    return '/index.php' . ($path ? '?route=' . $path : '');
}

function app_url(string $page = ''): string {
    return '/index.php?route=app' . ($page ? '&page=' . $page : '');
}

function asset_url(string $path): string {
    return '/public/assets/' . ltrim($path, '/');
}

function formatJuta(float $juta): string {
    if ($juta >= 1000) {
        return 'Rp ' . number_format($juta / 1000, 1, ',', '.') . ' M';
    }
    return 'Rp ' . number_format($juta, 1, ',', '.') . ' Jt';
}

function formatRupiahLP(int $rp): string {
    if ($rp >= 1_000_000_000) {
        return 'Rp ' . number_format($rp / 1_000_000_000, 1, ',', '.') . ' M';
    } elseif ($rp >= 1_000_000) {
        return 'Rp ' . number_format($rp / 1_000_000, 1, ',', '.') . ' Jt';
    }
    return 'Rp ' . number_format($rp, 0, ',', '.');
}

function formatRupiahFull(int $rp): string {
    return 'Rp ' . number_format($rp, 0, ',', '.');
}



function sipedo_default_program_image_from_data(array $program): string {
    $id = strtoupper(trim((string)($program['id'] ?? $program['kode'] ?? '')));
    $name = strtolower(trim((string)($program['name'] ?? '')));
    $cat = strtolower(trim((string)($program['cat'] ?? $program['category'] ?? '')));

    // Peta spesifik per kode program
    $map = [
        'PR-01' => 'assets/images/beasiswa-anak-yatim.jpg',
        'PR-02' => 'assets/images/renovasi-panti-asuhan.jpg',
        'PR-03' => 'assets/images/bantuan-bencana-alam.jpg',
        'PR-04' => 'assets/images/pengobatan-gratis.jpg',
    ];

    if (isset($map[$id])) return $map[$id];

    // Peta fallback per kategori (agar program baru tidak pakai foto program lain)
    $catMap = [
        'pendidikan'  => 'assets/images/card-pendidikan.jpg',
        'sosial'      => 'assets/images/card-sosial.jpg',
        'kesehatan'   => 'assets/images/card-kesehatan.jpg',
        'kedaruratan' => 'assets/images/card-kedaruratan.jpg',
        'darurat'     => 'assets/images/card-kedaruratan.jpg',
        'kemanusiaan' => 'assets/images/card-kemanusiaan.jpg',
        'bencana'     => 'assets/images/card-kedaruratan.jpg',
    ];

    // Cek kategori dulu
    foreach ($catMap as $keyword => $img) {
        if (str_contains($cat, $keyword)) return $img;
    }

    // Cek kata kunci di nama program
    if (str_contains($name, 'beasiswa') || str_contains($name, 'yatim') || str_contains($name, 'sekolah') || str_contains($name, 'pendidikan')) return $catMap['pendidikan'];
    if (str_contains($name, 'panti') || str_contains($name, 'renovasi') || str_contains($name, 'sosial'))                                        return $catMap['sosial'];
    if (str_contains($name, 'bencana') || str_contains($name, 'darurat') || str_contains($name, 'kedaruratan'))                                   return $catMap['kedaruratan'];
    if (str_contains($name, 'pengobatan') || str_contains($name, 'medis') || str_contains($name, 'kesehatan') || str_contains($name, 'gratis'))   return $catMap['kesehatan'];
    if (str_contains($name, 'kemanusiaan') || str_contains($name, 'bantuan'))                                                                      return $catMap['kemanusiaan'];

    return '';
}

function sipedo_program_image(array $program): string {
    $image = trim((string)($program['image'] ?? ''));
    if ($image !== '') {
        if (str_starts_with($image, 'public/')) return substr($image, 7);
        return ltrim($image, '/');
    }

    return sipedo_default_program_image_from_data($program);
}

function sipedo_program_has_image(array $program): bool {
    return sipedo_program_image($program) !== '';
}

function pub(string $path): string {
    if (empty($path)) return '';
    if (str_starts_with($path, 'public/') || str_starts_with($path, '/')) return $path;
    return 'public/' . $path;
}
