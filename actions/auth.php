<?php
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $user = $_SESSION['users'][$email] ?? null;

    if (!$user || $user['pass'] !== $password || $user['role'] !== $role) {
        flash('Email, password, atau role salah.', 'error');
        redirect_to('../index.php?mode=login');
    }

    $_SESSION['currentUser'] = $user;
    $_SESSION['currentUser']['email'] = $email;
    $_SESSION['currentRole'] = $user['role'];
    redirect_to('../app.php');
}

if ($action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'donatur';

    if (!$name || !$email || !$password) {
        flash('Lengkapi semua field.', 'error');
        redirect_to('../index.php?mode=register');
    }

    $parts = preg_split('/\s+/', $name);
    $initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));

    $_SESSION['users'][$email] = [
        'pass' => $password,
        'role' => $role,
        'name' => $name,
        'initials' => $initials,
        'color' => $role === 'staff' ? '#d97706' : '#059669',
    ];

    flash('Akun berhasil dibuat. Silakan masuk.', 'success');
    redirect_to('../index.php?mode=login');
}

if ($action === 'logout') {
    unset($_SESSION['currentUser'], $_SESSION['currentRole']);
    redirect_to('../index.php');
}

redirect_to('../index.php');

