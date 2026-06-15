<?php

class AuthController {
    public function showLogin(): void {
        if (current_user()) {
            redirect_to(app_url());
        }
        $mode = $_GET['mode'] ?? 'login';
        View::render('auth/login', compact('mode'));
    }

    public function login(): void {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';

        $user = UserModel::authenticate($email, $password);
        if (!$user) {
            flash('Email atau password salah.', 'error');
            redirect_to(base_url('auth/login') . '&mode=login');
        }

        $_SESSION['currentUser']          = $user;
        $_SESSION['currentUser']['email'] = $email;
        $_SESSION['currentRole']          = $user['role'];
        redirect_to(app_url());
    }

    public function register(): void {
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';


        $role = 'donatur';

        if (!$name || !$email || !$password) {
            flash('Lengkapi semua field.', 'error');
            redirect_to(base_url('auth/login') . '&mode=register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('Format email tidak valid.', 'error');
            redirect_to(base_url('auth/login') . '&mode=register');
        }
        if (UserModel::findByEmail($email)) {
            flash('Email sudah terdaftar.', 'error');
            redirect_to(base_url('auth/login') . '&mode=register');
        }

        UserModel::create($name, $email, $password, $role);
        flash('Akun berhasil dibuat. Silakan masuk.', 'success');
        redirect_to(base_url('auth/login') . '&mode=login');
    }

    public function logout(): void {
        unset($_SESSION['currentUser'], $_SESSION['currentRole']);
        redirect_to(base_url());
    }
}
