<?php
$qUser = trim($_GET['q_user'] ?? '');

$donors = [];
$idx = 1;
foreach ($_SESSION['users'] ?? [] as $email => $u) {
    if (($u['role'] ?? '') !== 'donatur') continue;

    $totalAmount = 0;
    $donationCount = 0;
    foreach ($_SESSION['donations'] ?? [] as $d) {
        if ($d['donor'] === $u['name']) {
            $totalAmount += (int) str_replace('.', '', $d['amount']);
            $donationCount++;
        }
    }

    $donors[] = [
        'id' => 'USR-' . str_pad((string) $idx, 2, '0', STR_PAD_LEFT),
        'name' => $u['name'],
        'email' => $email,
        'total' => $totalAmount,
        'count' => $donationCount,
        'status' => $donationCount > 0 ? 'active' : 'inactive',
    ];
    $idx++;
}

if ($qUser !== '') {
    $needle = strtolower($qUser);
    $donors = array_values(array_filter($donors, function ($d) use ($needle) {
        return stripos($d['name'], $needle) !== false
            || stripos($d['email'], $needle) !== false
            || stripos($d['id'], $needle) !== false;
    }));
}
?>
<div class="section-head">
    <h3 class="section-title">Manajemen Staff</h3>
</div>
<div class="card">
    <form action="index.php?route=staff/add" method="post" class="grid two">
        <input type="hidden" name="action" value="add">
        <div class="field">
            <label>Nama Staff</label>
            <input type="text" name="name" placeholder="Nama staff" required>
        </div>
        <div class="field">
            <label>Email Staff</label>
            <input type="email" name="email" placeholder="staff@sipedo.org" required>
        </div>
        <button class="btn" type="submit">Tambah Staff</button>
    </form>
</div>

<div class="section-head">
    <h3 class="section-title">Daftar Staff</h3>
</div>
<div class="panel table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
        <tbody>
            <?php if (empty($_SESSION['staffList'])): ?>
                <tr><td colspan="6" style="text-align:center;color:#6b7280;padding:18px;">Belum ada staff terdaftar.</td></tr>
            <?php endif; ?>
            <?php foreach ($_SESSION['staffList'] as $s): ?>
                <tr>
                    <td class="id">#<?= e($s['id']) ?></td>
                    <td><?= e($s['name']) ?></td>
                    <td class="muted"><?= e($s['email']) ?></td>
                    <td><?= e($s['role']) ?></td>
                    <td><?= badge($s['status']) ?></td>
                    <td class="actions" style="justify-content:flex-end;text-align:right;">
                        <?php if ($s['status'] === 'active'): ?>
                            <form action="index.php?route=staff/set-status" method="post">
                                <input type="hidden" name="action" value="deactivate">
                                <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                                <button class="btn amber" type="submit">Nonaktifkan</button>
                            </form>
                        <?php else: ?>
                            <form action="index.php?route=staff/set-status" method="post">
                                <input type="hidden" name="action" value="activate">
                                <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                                <button class="btn green" type="submit">Aktifkan</button>
                            </form>
                        <?php endif; ?>
                        <form action="index.php?route=staff/delete" method="post" onsubmit="return confirm('Hapus staff <?= e($s['name']) ?>?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                            <button class="btn red" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="section-head" style="margin-top:32px;">
    <h3 class="section-title">Tambah Program Baru</h3>
</div>
<div class="panel" style="padding:24px;">
    <form action="index.php?route=program/add" method="post" enctype="multipart/form-data"
          id="formTambahProgramAdmin">
        <input type="hidden" name="action" value="add">

        <div class="field">
            <label>Judul Program <span style="color:#dc2626;">*</span></label>
            <input type="text" name="name" placeholder="cth: Beasiswa Anak Yatim 2026" maxlength="150" required>
            <small style="color:#6b7280;">Maks. 150 karakter</small>
        </div>

        <div class="field">
            <label>Deskripsi Program</label>
            <textarea name="description" rows="3" placeholder="Jelaskan tujuan, manfaat, dan sasaran penerima program..."></textarea>
        </div>

        <div class="grid two">
            <div class="field">
                <label>Target Dana (Rp) <span style="color:#dc2626;">*</span></label>
                <input type="number" name="target" placeholder="cth: 50000000" min="100000" step="50000" required>
                <small style="color:#6b7280;">Minimal Rp 100.000</small>
            </div>
            <div class="field">
                <label>Tanggal Selesai <span style="color:#dc2626;">*</span></label>
                <input type="date" name="deadline" id="inputDeadlineAdmin"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                <small style="color:#6b7280;">Harus lebih dari hari ini.</small>
            </div>
        </div>

        <div class="grid two">
            <div class="field">
                <label>Kategori <span style="color:#dc2626;">*</span></label>
                <select name="category" required>
                    <option value="">Pilih Kategori</option>
                    <option value="Pendidikan">Pendidikan</option>
                    <option value="Kesehatan">Kesehatan</option>
                    <option value="Keagamaan">Keagamaan</option>
                    <option value="Pangan">Pangan &amp; Gizi</option>
                    <option value="Infrastruktur">Infrastruktur</option>
                    <option value="Lingkungan">Lingkungan</option>
                    <option value="Sosial">Sosial</option>
                    <option value="Kedaruratan">Kedaruratan</option>
                </select>
            </div>
            <div class="field">
                <label>Status Awal</label>
                <select name="status">
                    <option value="active">Aktif (langsung publish)</option>
                    <option value="draft">Draft (simpan dulu)</option>
                </select>
            </div>
        </div>

        <div class="field">
            <label>Gambar Banner Program</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <small style="color:#6b7280;">JPG / PNG / WEBP — maks. 2 MB. Rasio ideal 16:9 (mis. 800×450 px). Kosongkan jika tidak ingin upload gambar.</small>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px;">
            <button class="btn green" type="submit">Simpan Program</button>
        </div>
    </form>
</div>

<script>
(function () {
    var today = new Date(); today.setHours(0, 0, 0, 0);
    var inp = document.getElementById('inputDeadlineAdmin');
    if (!inp) return;
    inp.addEventListener('change', function () {
        var chosen = new Date(this.value); chosen.setHours(0, 0, 0, 0);
        if (chosen <= today) { alert('Tanggal selesai harus setelah hari ini.'); this.value = ''; }
    });
    document.getElementById('formTambahProgramAdmin').addEventListener('submit', function (e) {
        var val = inp.value;
        if (!val) return;
        var chosen = new Date(val); chosen.setHours(0, 0, 0, 0);
        if (chosen <= today) { e.preventDefault(); alert('Tanggal selesai harus setelah hari ini.'); inp.value = ''; }
    });
})();
</script>

<div class="section-head">
    <h3 class="section-title">Daftar User Donatur</h3>
    <form action="index.php" method="get" class="filter-bar">
        <input type="hidden" name="route" value="app">
        <input type="hidden" name="page" value="pengguna">
        <input type="text" name="q_user" placeholder="Cari nama / email / ID..." value="<?= e($qUser) ?>">
        <button class="btn" type="submit">Cari</button>
        <?php if ($qUser !== ''): ?>
            <a class="btn light" href="index.php?route=app&page=pengguna">Reset</a>
        <?php endif; ?>
    </form>
</div>
<div class="panel table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
        <tbody>
            <?php if (empty($donors)): ?>
                <tr><td colspan="5" style="text-align:center;color:#6b7280;padding:18px;">
                    <?= $qUser !== '' ? 'Tidak ada donatur yang cocok dengan pencarian.' : 'Belum ada user donatur terdaftar.' ?>
                </td></tr>
            <?php endif; ?>
            <?php foreach ($donors as $d): ?>
                <tr>
                    <td class="id">#<?= e($d['id']) ?></td>
                    <td><?= e($d['name']) ?></td>
                    <td class="muted"><?= e($d['email']) ?></td>
                    <td>Donatur<?= $d['count'] > 0 ? ' · ' . $d['count'] . ' donasi' : '' ?></td>
                    <td><?= badge($d['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
