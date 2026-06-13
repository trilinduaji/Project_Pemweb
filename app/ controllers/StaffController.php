<?php

class StaffController {
    public function add(): void {
        require_login();
        if (current_role() !== 'admin') {
            flash('Hanya admin yang dapat mengelola staff.', 'error');
            redirect_to(app_url());
        }

        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!$name || !$email) {
            flash('Nama dan email staff wajib diisi.', 'error');
            redirect_to(app_url('pengguna'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('Format email staff tidak valid.', 'error');
            redirect_to(app_url('pengguna'));
        }
        if (UserModel::findByEmail($email)) {
            flash('Email staff sudah terdaftar.', 'error');
            redirect_to(app_url('pengguna'));
        }

        StaffModel::create($name, $email);
        add_log('Menambah staff baru', $name);
        flash('Staff baru berhasil ditambahkan.', 'success');
        redirect_to(app_url('pengguna'));
    }

    public function setStatus(): void {
        require_login();
        if (current_role() !== 'admin') {
            flash('Hanya admin.', 'error');
            redirect_to(app_url());
        }

        $id     = $_POST['id']     ?? '';
        $action = $_POST['action'] ?? '';
        $status = $action === 'activate' ? 'active' : 'inactive';
        $desc   = $action === 'activate' ? 'Mengaktifkan staff' : 'Menonaktifkan staff';

        $name = StaffModel::setStatus($id, $status);
        if ($name) {
            add_log($desc, '#' . $id);
            flash('Status staff ' . $name . ' berhasil diperbarui.', 'success');
        } else {
            flash('Staff tidak ditemukan.', 'error');
        }
        redirect_to(app_url('pengguna'));
    }

    public function delete(): void {
        require_login();
        if (current_role() !== 'admin') {
            flash('Hanya admin.', 'error');
            redirect_to(app_url());
        }

        $id     = $_POST['id'] ?? '';
        $result = StaffModel::deleteWithTransfer($id);
        if ($result['name']) {
            $msg = 'Staff ' . $result['name'] . ' berhasil dihapus.';
            if (!empty($result['transferred'])) {
                $msg .= ' Kepemilikan ' . count($result['transferred']) . ' program telah dipindahkan ke rekan/admin.';
            }
            add_log('Menghapus staff', '#' . $id);
            flash($msg, 'success');
        } else {
            flash('Staff tidak ditemukan.', 'error');
        }
        redirect_to(app_url('pengguna'));
    }


    public function addRekan(): void {
        require_login();
        if (current_role() !== 'staff') {
            flash('Hanya staff yang dapat menambahkan rekan.', 'error');
            redirect_to(app_url());
        }

        $programId     = (int)($_POST['program_id']    ?? 0);
        $rekanUserId   = (int)($_POST['rekan_user_id'] ?? 0);
        $roleInProgram = trim($_POST['role_in_program'] ?? 'Anggota');
        $addedBy       = (int)(current_user()['db_id'] ?? 0);

        if (!$programId || !$rekanUserId) {
            flash('Program dan rekan harus dipilih.', 'error');
            redirect_to(app_url('rekan-staff'));
        }

        $result = StaffModel::addRekanToProgram($programId, $rekanUserId, $addedBy, $roleInProgram);

        if ($result === 'exists') {
            flash('Rekan sudah terdaftar di program ini.', 'error');
        } elseif ($result === 'not_owner') {
            flash('Kamu hanya bisa menambahkan rekan ke program yang kamu kelola.', 'error');
        } elseif ($result === true) {
            $rekan = UserModel::findByDbId($rekanUserId);
            add_log('Menambahkan rekan ke program', '#PR-' . $programId);
            flash('Rekan ' . ($rekan['name'] ?? '') . ' berhasil ditambahkan ke program.', 'success');
        } else {
            flash('Gagal menambahkan rekan.', 'error');
        }
        redirect_to(app_url('rekan-staff'));
    }


    public function removeRekan(): void {
        require_login();
        if (current_role() !== 'staff') {
            flash('Hanya staff yang dapat mengelola rekan.', 'error');
            redirect_to(app_url());
        }

        $psId    = (int)($_POST['ps_id'] ?? 0);
        $actorId = (int)(current_user()['db_id'] ?? 0);

        $result = StaffModel::removeRekanFromProgram($psId, $actorId);

        if ($result === 'not_owner') {
            flash('Kamu hanya bisa menghapus rekan dari program yang kamu kelola.', 'error');
        } elseif ($result === true) {
            flash('Rekan berhasil dikeluarkan dari program.', 'success');
        } else {
            flash('Data tidak ditemukan.', 'error');
        }
        redirect_to(app_url('rekan-staff'));
    }
}
