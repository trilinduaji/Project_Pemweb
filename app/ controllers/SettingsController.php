<?php

class SettingsController
{
    public function update(): void
    {

        require_login();
        if (current_role() !== 'admin') {
            flash('Akses ditolak. Hanya admin yang dapat mengubah pengaturan.', 'error');
            redirect_to(app_url('pengaturan'));
        }


        $app_name              = trim($_POST['app_name']              ?? '');
        $contact_email         = trim($_POST['contact_email']         ?? '');
        $verification_deadline = trim($_POST['verification_deadline'] ?? '');
        $default_role          = trim($_POST['default_role']          ?? 'donatur');


        if ($app_name === '' || $contact_email === '' || $verification_deadline === '') {
            flash('Semua field wajib diisi.', 'error');
            redirect_to(app_url('pengaturan'));
        }
        if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            flash('Format email kontak tidak valid.', 'error');
            redirect_to(app_url('pengaturan'));
        }
        if (!is_numeric($verification_deadline) || (int)$verification_deadline < 1) {
            flash('Batas waktu verifikasi harus berupa angka positif.', 'error');
            redirect_to(app_url('pengaturan'));
        }
        if (!in_array($default_role, ['donatur', 'staff'], true)) {
            $default_role = 'donatur';
        }

        $fields = [
            'app_name'              => $app_name,
            'contact_email'         => $contact_email,
            'verification_deadline' => (string)(int)$verification_deadline,
            'default_role'          => $default_role,
        ];


        if (db_ready()) {
            $conn = db();
            $stmt = $conn->prepare(
                "UPDATE settings SET value = ?, updated_at = CURRENT_TIMESTAMP WHERE `key` = ?"
            );
            foreach ($fields as $key => $value) {
                $stmt->bind_param('ss', $value, $key);
                $stmt->execute();
            }
            $stmt->close();


            if (function_exists('sipedo_load_session_from_db')) {
                sipedo_load_session_from_db();
            }
        } else {

            if (!isset($_SESSION['settings'])) {
                $_SESSION['settings'] = [];
            }
            foreach ($fields as $key => $value) {
                $_SESSION['settings'][$key] = $value;
            }
        }


        add_log('Memperbarui pengaturan sistem', 'settings');
        flash('Pengaturan berhasil disimpan.', 'success');
        redirect_to(app_url('pengaturan'));
    }
}
