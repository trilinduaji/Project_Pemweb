<?php

class DonationController {
    public function verify(): void {
        require_login();


        $role = current_role();
        if (!in_array($role, ['staff', 'admin'], true)) {
            flash('Akses ditolak. Fitur ini hanya untuk staff.', 'error');
            redirect_to(app_url('riwayat-donasi'));
        }

        $id     = $_POST['id']     ?? '';
        $action = $_POST['action'] ?? '';

        $note   = trim($_POST['note'] ?? '');

        if (!in_array($action, ['verify', 'reject'], true)) {
            redirect_to(app_url('verifikasi'));
        }

        if (!DonationModel::canProcess($id)) {
            flash('Kamu hanya bisa memverifikasi donasi dari program yang kamu kelola atau program tempat kamu menjadi anggota.', 'error');
            redirect_to(app_url($role === 'staff' ? 'verifikasi' : 'rekap-donasi'));
        }

        // Catatan wajib diisi saat menolak donasi
        if ($action === 'reject' && $note === '') {
            flash('Alasan penolakan wajib diisi sebelum menolak donasi.', 'error');
            redirect_to(app_url('verifikasi'));
        }

        $status = $action === 'verify' ? 'verified' : 'rejected';
        $desc   = $action === 'verify' ? 'Memverifikasi donasi' : 'Menolak donasi';

        DonationModel::updateStatus($id, $status, current_user()['name'], $note);


        if (function_exists('sipedo_load_session_from_db')) {
            sipedo_load_session_from_db();
        }

        add_log($desc, '#' . $id);
        flash('Status donasi #' . $id . ' berhasil diperbarui.', 'success');
        redirect_to(app_url('verifikasi'));
    }

    public function donate(): void {
        require_login();

        $amount    = (int) ($_POST['amount']     ?? 0);
        $programId = $_POST['program_id']        ?? '';
        $method    = $_POST['method']            ?? 'BCA Transfer';

        $program = ProgramModel::findById($programId);
        if (!$program || ($program['status'] ?? '') !== 'active') {
            flash('Program tidak tersedia untuk menerima donasi.', 'error');
            redirect_to(app_url('program-donatur'));
        }
        $programName = $program['name'];

        if ($amount < 1000) {
            flash('Jumlah donasi minimal Rp 1.000.', 'error');
            redirect_to(app_url('program-detail&id=' . $programId));
        }

        if (empty($_FILES['proof']['name']) || $_FILES['proof']['error'] === UPLOAD_ERR_NO_FILE) {
            flash('Bukti pembayaran wajib diunggah.', 'error');
            redirect_to(app_url('program-detail&id=' . $programId));
        }

        $proofRel = $this->uploadFile($_FILES['proof'], 'proofs', 'bukti');
        if (!$proofRel) {
            redirect_to(app_url('program-detail&id=' . $programId));
        }

        $user = current_user();
        DonationModel::create([
            'donor'       => $user['name'],
            'initials'    => $user['initials'],
            'color'       => $user['color'],
            'programName' => $programName,
            'programId'   => $programId,
            'amount'      => $amount,
            'method'      => $method,
            'proof'       => $proofRel,
        ]);

        flash('Donasi berhasil dikirim. Menunggu verifikasi staff.', 'success');
        redirect_to(app_url('riwayat-donasi'));
    }

    private function uploadFile(array $file, string $folder, string $prefix): ?string {

        $allowed  = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/heic' => 'heic',
            'image/heif' => 'heic',
        ];
        $maxBytes = 2 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            flash('Upload gagal (kode ' . $file['error'] . ').', 'error');
            return null;
        }
        if ($file['size'] > $maxBytes) {
            flash('Ukuran file maksimal 2 MB.', 'error');
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);


        if (in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
            flash('File PDF tidak diperbolehkan. Bukti pembayaran harus berupa gambar (JPG, JPEG, PNG, HEIC, atau WEBP).', 'error');
            return null;
        }

        if (!isset($allowed[$mime])) {
            flash('Format file tidak valid. Gunakan JPG, JPEG, PNG, HEIC, atau WEBP.', 'error');
            return null;
        }

        $ext      = $allowed[$mime];
        $filename = $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
        $destDir  = App::basePath() . '/public/assets/uploads/' . $folder;
        if (!is_dir($destDir)) mkdir($destDir, 0775, true);
        if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) {
            flash('Gagal menyimpan file ke server.', 'error');
            return null;
        }
        return 'assets/uploads/' . $folder . '/' . $filename;
    }
}
