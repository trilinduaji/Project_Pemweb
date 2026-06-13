<?php

class ProgramController {
    public function add(): void {
        require_login();
        $role = current_role();
        if (!in_array($role, ['admin', 'staff'], true)) {
            flash('Anda tidak memiliki izin untuk menambah program.', 'error');
            redirect_to(app_url('dashboard'));
            return;
        }
        $role         = current_role();
        $redirectPage = $role === 'staff' ? 'program-staff' : 'program-admin';

        $name        = trim($_POST['name']        ?? '');
        $category    = $_POST['category']         ?? 'Sosial';
        $target      = (int) ($_POST['target']    ?? 0);
        $deadline    = $_POST['deadline']          ?? '-';
        $description = trim($_POST['description'] ?? '');
        $status      = ($_POST['status'] ?? 'active') === 'draft' ? 'inactive' : 'active';

        if (!$name || $target <= 0) {
            flash('Nama program dan target wajib diisi.', 'error');
            redirect_to(app_url('tambah-program'));
        }


        $today    = date('Y-m-d');
        $deadlineDate = $deadline;
        if ($deadlineDate <= $today) {
            $_SESSION['sipedo_deadline_error'] = true;
            redirect_to(app_url('tambah-program'));
        }

        $imageRel = '';
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageRel = $this->uploadImage($_FILES['image'], 'tambah-program');
            if ($imageRel === null) return;
        }

        ProgramModel::create([
            'name'        => $name,
            'category'    => $category,
            'target'      => $target,
            'deadline'    => $deadline,
            'description' => $description,
            'status'      => $status,
            'image'       => $imageRel,
        ]);

        add_log('Membuat program baru', $name);
        flash('Program baru berhasil ditambahkan.', 'success');
        redirect_to(app_url($redirectPage));
    }

    public function edit(): void {
        require_login();
        if (!in_array(current_role(), ['admin', 'staff'], true)) {
            flash('Anda tidak memiliki izin untuk mengedit program.', 'error');
            redirect_to(app_url('dashboard'));
            return;
        }
        $id          = $_POST['id']               ?? '';
        $name        = trim($_POST['name']        ?? '');
        $category    = $_POST['category']         ?? 'Sosial';
        $target      = (int) ($_POST['target']    ?? 0);
        $deadline    = $_POST['deadline']          ?? '-';
        $description = trim($_POST['description'] ?? '');
        $status      = $_POST['status']            ?? 'active';

        $allowedStatus = ['active', 'inactive', 'closed'];
        if (!in_array($status, $allowedStatus, true)) $status = 'active';

        if (!ProgramModel::canManage($id)) {
            flash('Kamu tidak memiliki izin mengubah program ini.', 'error');
            redirect_to(app_url(current_role() === 'staff' ? 'program-staff' : 'program-admin'));
        }

        if (!$name || $target <= 0) {
            flash('Nama program dan target wajib diisi.', 'error');
            redirect_to(app_url('edit-program&id=' . $id));
        }

        if ($deadline !== '' && $deadline < date('Y-m-d')) {
            flash('Tanggal selesai tidak boleh sudah lewat.', 'error');
            redirect_to(app_url('edit-program&id=' . $id));
        }

        $imageRel = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageRel = $this->uploadImage($_FILES['image'], 'edit-program&id=' . $id);
            if ($imageRel === null) return;
        }

        $updated = ProgramModel::update($id, [
            'name'        => $name,
            'category'    => $category,
            'target'      => $target,
            'deadline'    => $deadline,
            'description' => $description,
            'status'      => $status,
            'image'       => $imageRel ?? '',
        ]);

        if ($updated) {
            add_log('Mengubah program bantuan', '#' . $id);
            flash('Program berhasil diperbarui.', 'success');
        } else {
            flash('Program tidak ditemukan.', 'error');
        }
        redirect_to(app_url('program-detail&id=' . $id));
    }

    public function close(): void {
        require_login();
        $role = current_role();
        if (!in_array($role, ['admin', 'staff'], true)) {
            flash('Anda tidak memiliki izin untuk menutup program.', 'error');
            redirect_to(app_url('dashboard'));
            return;
        }
        $id   = $_POST['id'] ?? '';
        if (!ProgramModel::canManage($id)) {
            flash('Kamu tidak memiliki izin menutup program ini.', 'error');
            redirect_to(app_url($role === 'staff' ? 'program-staff' : 'program-admin'));
        }
        ProgramModel::setStatus($id, 'closed');
        add_log('Menutup program bantuan', '#' . $id);
        flash('Program berhasil ditutup.', 'success');
        redirect_to(app_url($role === 'staff' ? 'program-staff' : 'program-admin'));
    }

    public function reopen(): void {
        require_login();

        if (current_role() !== 'admin') {
            flash('Hanya admin yang dapat membuka kembali program yang sudah ditutup.', 'error');
            redirect_to(app_url('program-admin'));
        }
        $id = $_POST['id'] ?? '';
        ProgramModel::setStatus($id, 'active');
        add_log('Membuka kembali program bantuan', '#' . $id);
        flash('Program berhasil dibuka kembali.', 'success');
        redirect_to(app_url('program-admin'));
    }

    public function delete(): void {
        require_login();
        $role = current_role();
        if (!in_array($role, ['admin', 'staff'], true)) {
            flash('Anda tidak memiliki izin untuk menghapus program.', 'error');
            redirect_to(app_url('dashboard'));
            return;
        }
        $id   = $_POST['id'] ?? '';
        if (!ProgramModel::canManage($id)) {
            flash('Kamu tidak memiliki izin menghapus program ini.', 'error');
            redirect_to(app_url($role === 'staff' ? 'program-staff' : 'program-admin'));
        }
        ProgramModel::setStatus($id, 'deleted');
        add_log('Menghapus program bantuan', '#' . $id);
        flash('Program berhasil dihapus.', 'success');
        redirect_to(app_url($role === 'staff' ? 'program-staff' : 'program-admin'));
    }

    private function uploadImage(array $file, string $redirectPage): ?string {
        $allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $maxBytes = 2 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            flash('Upload gambar gagal (kode ' . $file['error'] . ').', 'error');
            redirect_to(app_url($redirectPage));
            return null;
        }
        if ($file['size'] > $maxBytes) {
            flash('Ukuran gambar maksimal 2 MB.', 'error');
            redirect_to(app_url($redirectPage));
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowed[$mime])) {
            flash('Format gambar harus JPG, PNG, atau WEBP.', 'error');
            redirect_to(app_url($redirectPage));
            return null;
        }

        $ext      = $allowed[$mime];
        $filename = 'prog-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
        $destDir  = App::basePath() . '/public/assets/uploads/programs';
        if (!is_dir($destDir)) mkdir($destDir, 0775, true);
        if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) {
            flash('Gagal menyimpan gambar ke server.', 'error');
            redirect_to(app_url($redirectPage));
            return null;
        }
        return 'assets/uploads/programs/' . $filename;
    }
}
