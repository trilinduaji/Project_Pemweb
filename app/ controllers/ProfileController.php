<?php

class ProfileController {
    public function update(): void {
        require_login();
        $user  = current_user();
        $email = $user['email'] ?? '';
        $role  = current_role();


        $allowedActions = ['update_profile', 'change_password'];
        $action = $_POST['action'] ?? '';
        if (!in_array($action, $allowedActions, true)) {
            redirect_to(app_url(match ($role) {
                'staff'  => 'profil-staff',
                'admin'  => 'profil-admin',
                default  => 'profil-donatur',
            }));
            return;
        }

        $redirectPage = match ($role) {
            'staff'  => 'profil-staff',
            'admin'  => 'profil-admin',
            default  => 'profil-donatur',
        };

        if ($action === 'update_profile') {
            $name = trim($_POST['name'] ?? '');
            if (!$name) {
                flash('Nama tidak boleh kosong.', 'error');
                redirect_to(app_url($redirectPage));
            }

            $photoRel = '';
            if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $photoRel = $this->uploadAvatar($_FILES['photo'], $email, $redirectPage);
                if ($photoRel === null) return;
            }

            UserModel::updateProfile($email, $name, $photoRel);
            add_log('Memperbarui profil', $name);
            flash('Profil berhasil diperbarui.', 'success');
            redirect_to(app_url($redirectPage));
        }

        if ($action === 'change_password') {
            $oldPass = $_POST['old_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';

            if (!UserModel::verifyPassword($email, $oldPass)) {
                flash('Password lama salah.', 'error');
                redirect_to(app_url($redirectPage));
            }
            if (strlen($newPass) < 3) {
                flash('Password baru minimal 3 karakter.', 'error');
                redirect_to(app_url($redirectPage));
            }

            UserModel::changePassword($email, $newPass);
            flash('Password berhasil diubah.', 'success');
            redirect_to(app_url($redirectPage));
        }

        redirect_to(app_url($redirectPage));
    }

    private function uploadAvatar(array $file, string $email, string $redirectPage): ?string {
        $allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $maxBytes = 2 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            flash('Upload foto gagal (kode ' . $file['error'] . ').', 'error');
            redirect_to(app_url($redirectPage));
            return null;
        }
        if ($file['size'] > $maxBytes) {
            flash('Ukuran foto maksimal 2 MB.', 'error');
            redirect_to(app_url($redirectPage));
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowed[$mime])) {
            flash('Format foto harus JPG, PNG, atau WEBP.', 'error');
            redirect_to(app_url($redirectPage));
            return null;
        }

        $ext      = $allowed[$mime];
        $filename = 'avatar-' . preg_replace('/[^a-z0-9]/i', '', $email) . '-' . date('YmdHis') . '.' . $ext;
        $destDir  = App::basePath() . '/public/assets/uploads/avatars';
        if (!is_dir($destDir)) mkdir($destDir, 0775, true);
        if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) {
            flash('Gagal menyimpan foto.', 'error');
            redirect_to(app_url($redirectPage));
            return null;
        }
        return 'assets/uploads/avatars/' . $filename;
    }
}
