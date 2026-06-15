<?php

$s = $_SESSION['settings'] ?? [];
$app_name             = $s['app_name']              ?? 'SIPEDO';
$contact_email        = $s['contact_email']         ?? 'admin@sipedo.org';
$verification_deadline= $s['verification_deadline'] ?? '24';
$default_role         = $s['default_role']          ?? 'donatur';
?>

<form method="POST" action="<?= base_url('settings/update') ?>">
    <input type="hidden" name="action" value="update_settings">

    <div class="grid two">
        <div class="card">
            <h3>Informasi Organisasi</h3>
            <div class="field">
                <label>Nama Organisasi</label>
                <input type="text" name="app_name" value="<?= e($app_name) ?>">
            </div>
            <div class="field">
                <label>Email Kontak</label>
                <input type="email" name="contact_email" value="<?= e($contact_email) ?>">
            </div>
        </div>

        <div class="card">
            <h3>Kebijakan Verifikasi</h3>
            <div class="field">
                <label>Batas Waktu Verifikasi (jam)</label>
                <input type="number" name="verification_deadline" value="<?= e($verification_deadline) ?>" min="1">
            </div>
            <div class="field">
                <label>Role Default</label>
                <select name="default_role">
                    <option value="donatur" <?= $default_role === 'donatur' ? 'selected' : '' ?>>Donatur</option>
                    <option value="staff"   <?= $default_role === 'staff'   ? 'selected' : '' ?>>Staff</option>
                </select>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn" type="submit">Simpan Pengaturan</button>
    </div>
</form>
