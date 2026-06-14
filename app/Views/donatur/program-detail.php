<?php
$id = $_GET['id'] ?? '';
$role = current_role();
$program = null;
foreach ($_SESSION['programs'] as $p) {
    if ($p['id'] === $id) {
        $program = $p;
        break;
    }
}

if (!$program || ($program['status'] ?? '') === 'deleted' || (($program['status'] ?? '') === 'inactive' && $role === 'donatur')) {
    echo '<div class="flash flash-error">Program tidak ditemukan atau belum dipublikasikan.</div>';
    echo '<a class="btn light" href="index.php?route=app&page=program-donatur">Kembali ke Daftar Program</a>';
    return;
}

$donations = array_values(array_filter(
    $_SESSION['donations'] ?? [],
    fn($d) => ($d['progId'] ?? '') === $program['id'] && ($d['status'] ?? '') === 'verified'
));

$raised = ((float) $program['collected']) * 1000000;
$target = ((float) $program['target']) * 1000000;
$remaining = max(0, $target - $raised);

function rupiah_detail($v) {
    return 'Rp ' . number_format((float) $v, 0, ',', '.');
}
?>
<?php
$canManageProgram = in_array($role, ['staff', 'admin'], true) && ProgramModel::canManage($program['id']);
$backPage = match ($role) {
    'staff' => 'program-staff',
    'admin' => 'program-admin',
    default => 'program-donatur',
};
?>
<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
    <a class="btn light" href="index.php?route=app&page=<?= e($backPage) ?>">← Kembali ke Daftar Program</a>
    <?php if ($role === 'staff' && $canManageProgram): ?>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a class="btn green" href="index.php?route=app&page=edit-program&id=<?= e($program['id']) ?>">✎ Edit Program</a>
            <?php if ($program['status'] === 'active'): ?>
                <form action="index.php?route=program/close" method="post" onsubmit="return confirm('Tutup program <?= e($program['name']) ?>?');">
                    <input type="hidden" name="action" value="close">
                    <input type="hidden" name="id" value="<?= e($program['id']) ?>">
                    <button class="btn amber" type="submit">Tutup Program</button>
                </form>
            <?php endif; ?>
        </div>
    <?php elseif ($role === 'admin' && $canManageProgram): ?>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a class="btn green" href="index.php?route=app&page=edit-program&id=<?= e($program['id']) ?>">✎ Edit Program</a>
            <?php if ($program['status'] === 'active'): ?>
                <form action="index.php?route=program/close" method="post" onsubmit="return confirm('Tutup program <?= e($program['name']) ?>?');">
                    <input type="hidden" name="action" value="close">
                    <input type="hidden" name="id" value="<?= e($program['id']) ?>">
                    <button class="btn amber" type="submit">Tutup Program</button>
                </form>
            <?php elseif ($program['status'] === 'closed'): ?>
                <form action="index.php?route=program/reopen" method="post" onsubmit="return confirm('Buka kembali program <?= e($program['name']) ?>?');">
                    <input type="hidden" name="id" value="<?= e($program['id']) ?>">
                    <button class="btn green" type="submit">Buka Kembali</button>
                </form>
            <?php endif; ?>
            <form action="index.php?route=program/delete" method="post" onsubmit="return confirm('Hapus program <?= e($program['name']) ?>?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= e($program['id']) ?>">
                <button class="btn red" type="submit">Hapus Program</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<article class="program-detail">
    <?php $programImage = sipedo_program_image($program); ?>
    <div class="pd-banner" <?= $programImage === '' ? 'style="background:' . e($program['gradient'] ?? 'linear-gradient(135deg,#0D1B3E,#2A4080)') . ';"' : '' ?>>
        <?php if ($programImage !== ''): ?>
            <img src="<?= e(pub($programImage)) ?>" alt="<?= e($program['name']) ?>">
        <?php endif; ?>
    </div>

    <div class="pd-body">
        <div class="pd-meta-row">
            <span class="pd-cat"><?= e($program['cat']) ?></span>
            <?= badge($program['status']) ?>
        </div>
        <h1 class="pd-title"><?= e($program['name']) ?></h1>
        <p class="pd-deadline">Tenggat: <strong><?= e($program['deadline']) ?></strong></p>

        <h3 class="pd-section-title">Deskripsi Program</h3>
        <div class="pd-desc"><?php
            $rawDesc = $program['desc'] ?: 'Program ' . strtolower($program['cat']) . ' dengan deadline ' . $program['deadline'] . '. Dukungan Anda membantu program ini mencapai target donasi yang ditetapkan.';
            // Tampilkan persis seperti yang ditulis staff: spasi, enter, jarak paragraf dijaga
            echo nl2br(htmlspecialchars($rawDesc, ENT_QUOTES, 'UTF-8'));
        ?></div>

        <div class="pd-stats">
            <div>
                <div class="pd-stat-label">Terkumpul</div>
                <div class="pd-stat-value pd-emerald"><?= e(rupiah_detail($raised)) ?></div>
            </div>
            <div>
                <div class="pd-stat-label">Target</div>
                <div class="pd-stat-value"><?= e(rupiah_detail($target)) ?></div>
            </div>
            <div>
                <div class="pd-stat-label">Kekurangan</div>
                <div class="pd-stat-value"><?= e(rupiah_detail($remaining)) ?></div>
            </div>
            <div>
                <div class="pd-stat-label">Donatur</div>
                <div class="pd-stat-value"><?= count($donations) ?></div>
            </div>
        </div>

        <div class="pd-progress-row">
            <div class="progress" style="flex:1;height:10px;"><span style="width:<?= e(min(100, (float) $program['pct'])) ?>%"></span></div>
            <strong class="pd-emerald"><?= e($program['pct']) ?>%</strong>
        </div>
    </div>
</article>

<?php if ($program['status'] === 'active' && $role === 'donatur'): ?>

    <div class="panel" style="padding:20px;margin-top:18px;border-left:4px solid #059669;">
        <h3 class="section-title" style="margin-bottom:12px;">📋 Informasi Rekening Transfer</h3>
        <p style="color:#6b7280;font-size:0.85rem;margin-bottom:14px;">Silakan transfer donasi ke salah satu rekening berikut, lalu unggah bukti transfernya di formulir di bawah.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px;">
                <div style="font-weight:700;color:#065f46;margin-bottom:4px;">🏦 BCA</div>
                <div style="font-size:1.1rem;font-weight:700;letter-spacing:1px;color:#1f2937;">1234567890</div>
                <div style="color:#6b7280;font-size:0.82rem;">a.n. Yayasan SIPEDO</div>
            </div>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px;">
                <div style="font-weight:700;color:#065f46;margin-bottom:4px;">🏦 Mandiri</div>
                <div style="font-size:1.1rem;font-weight:700;letter-spacing:1px;color:#1f2937;">1100009876543</div>
                <div style="color:#6b7280;font-size:0.82rem;">a.n. Yayasan SIPEDO</div>
            </div>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px;">
                <div style="font-weight:700;color:#065f46;margin-bottom:4px;">🏦 BRI</div>
                <div style="font-size:1.1rem;font-weight:700;letter-spacing:1px;color:#1f2937;">0090010123456789</div>
                <div style="color:#6b7280;font-size:0.82rem;">a.n. Yayasan SIPEDO</div>
            </div>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px;">
                <div style="font-weight:700;color:#065f46;margin-bottom:4px;">📱 QRIS</div>
                <div style="font-size:0.85rem;color:#1f2937;">Scan QRIS di halaman kasir atau tunjukkan kode QRIS SIPEDO kepada kasir.</div>
            </div>
        </div>
        <p style="color:#dc2626;font-size:0.82rem;margin-top:12px;">⚠️ Pastikan nama rekening tujuan sesuai sebelum melakukan transfer. Setelah transfer, segera unggah bukti di bawah agar donasi dapat diverifikasi.</p>
    </div>

    <div class="panel" style="padding:20px;margin-top:18px;">
        <h3 class="section-title" style="margin-bottom:14px;">Donasi untuk Program Ini</h3>
        <form action="index.php?route=donation/donate" method="post" enctype="multipart/form-data">
            <input type="hidden" name="program_id" value="<?= e($program['id']) ?>">
            <div class="grid two">
                <div class="field">
                    <label>Jumlah Donasi (Rp)</label>
                    <input type="number" name="amount" min="1000" placeholder="50000" required>
                </div>
                <div class="field">
                    <label>Metode Pembayaran</label>
                    <select name="method">
                        <option>BCA Transfer</option>
                        <option>Mandiri Transfer</option>
                        <option>BRI Transfer</option>
                        <option>QRIS</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Bukti Pembayaran <span style="color:#dc2626;">*</span></label>
                <input type="file" name="proof" accept="image/jpeg,image/png,image/webp,image/heic,image/heif" required>
                <small style="color:#6b7280;">JPG, JPEG, PNG, HEIC, atau WEBP — maks. 2 MB. Lampirkan screenshot bukti transfer. <strong style="color:#dc2626;">File PDF tidak diterima.</strong></small>
            </div>
            <button class="btn green full" type="submit">Donasi Sekarang</button>
        </form>
    </div>
<?php elseif ($role === 'donatur'): ?>
    <div class="panel" style="padding:20px;margin-top:18px;text-align:center;color:#6b7280;">
        Program ini sudah <?= e($program['status'] === 'closed' ? 'selesai' : 'tidak aktif') ?> dan tidak menerima donasi baru.
    </div>
<?php endif; ?>
