<?php $user = current_user(); ?>
<div class="grid two">
    <div class="card">
        <h3 style="margin-bottom:14px;">Profil Saya</h3>

        <div class="profile-photo-preview">
            <?php if (!empty($user['photo'])): ?>
                <img src="<?= e(pub($user['photo'])) ?>" alt="Foto Profil">
            <?php else: ?>
                <span class="placeholder" style="background:<?= e($user['color']) ?>;width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                    <?= e($user['initials']) ?>
                </span>
            <?php endif; ?>
        </div>

        <form action="index.php?route=profile/update" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">

            <div class="field">
                <label>Foto Profil</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                <small style="color:#6b7280;">JPG / PNG / WEBP — maks. 2 MB. Kosongkan jika tidak ingin mengubah foto.</small>
            </div>

            <div class="field">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= e($user['name']) ?>" required>
            </div>

            <div class="field">
                <label>Email</label>
                <input value="<?= e($user['email'] ?? '') ?>" disabled>
                <small style="color:#6b7280;">Email tidak dapat diubah.</small>
            </div>

            <button class="btn green full" type="submit">Simpan Perubahan</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-bottom:14px;">Ubah Password</h3>
        <form action="index.php?route=profile/update" method="post">
            <input type="hidden" name="action" value="change_password">
            <div class="field">
                <label>Password Lama</label>
                <input type="password" name="old_password" required>
            </div>
            <div class="field">
                <label>Password Baru</label>
                <input type="password" name="new_password" minlength="3" required>
                <small style="color:#6b7280;">Minimal 3 karakter.</small>
            </div>
            <button class="btn full" type="submit">Ubah Password</button>
        </form>
    </div>
</div>
