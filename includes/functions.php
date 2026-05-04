<?php
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to($url) {
    header('Location: ' . $url);
    exit;
}

function current_user() {
    return $_SESSION['currentUser'] ?? null;
}

function current_role() {
    return $_SESSION['currentRole'] ?? null;
}

function require_login() {
    if (!current_user()) {
        redirect_to('index.php');
    }
}

function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function show_flash() {
    if (!isset($_SESSION['flash'])) return;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="flash flash-' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
}

function add_log($desc, $ref) {
    $user = current_user();
    $role = ucfirst(current_role() ?? 'User');
    $next = count($_SESSION['logs']) + 16;
    array_unshift($_SESSION['logs'], [
        'no' => $next,
        'time' => 'Baru saja',
        'actor' => $user['name'] ?? 'System',
        'role' => $role,
        'desc' => $desc,
        'ref' => $ref,
    ]);
}

function badge($status) {
    $labels = [
        'pending' => 'Pending',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'closed' => 'Selesai',
        'deleted' => 'Dihapus',
    ];
    return '<span class="badge badge-' . e($status) . '">' . e($labels[$status] ?? $status) . '</span>';
}

function avatar($initials, $color) {
    return '<span class="avatar" style="background:' . e($color) . '">' . e($initials) . '</span>';
}

function user_avatar($user) {
    if (!empty($user['photo'])) {
        return '<span class="avatar avatar-photo"><img src="' . e($user['photo']) . '" alt=""></span>';
    }
    return avatar($user['initials'] ?? '?', $user['color'] ?? '#666');
}

function progress_bar($pct) {
    return '<div class="progress-text">' . e($pct) . '%</div><div class="progress"><span style="width:' . e($pct) . '%"></span></div>';
}

function is_active_page($page, $target) {
    return $page === $target ? 'active' : '';
}

