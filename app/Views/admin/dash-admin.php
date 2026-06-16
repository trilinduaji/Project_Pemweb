<?php
$pending = count(array_filter($_SESSION['donations'], fn($d) => $d['status'] === 'pending'));
$activePrograms = count(array_filter($_SESSION['programs'], fn($p) => $p['status'] === 'active'));
$activeStaff = count(array_filter($_SESSION['staffList'], fn($s) => $s['status'] === 'active'));


$totalDana = 0;
foreach ($_SESSION['donations'] as $d) {
    if ($d['status'] === 'verified') {

        $totalDana += (int) str_replace('.', '', $d['amount']);
    }
}
if ($totalDana >= 1000000) {
    $totalDanaFmt = number_format($totalDana / 1000000, 1, ',', '.') . ' Jt';
} elseif ($totalDana >= 1000) {
    $totalDanaFmt = number_format($totalDana / 1000, 1, ',', '.') . ' Rb';
} else {
    $totalDanaFmt = number_format($totalDana, 0, ',', '.') ;
}


$totalPengguna = count($_SESSION['users'] ?? []);
$totalPenggunaFmt = $totalPengguna >= 1000
    ? number_format($totalPengguna / 1000, 1, ',', '.') . ' Rb'
    : $totalPengguna;
?>
<div class="stats">
    <div class="card"><div class="label">Total Dana Masuk</div><div class="value"><?= $totalDanaFmt ?></div></div>
    <div class="card"><div class="label">Total Pengguna</div><div class="value"><?= $totalPenggunaFmt ?></div></div>
    <div class="card"><div class="label">Program Aktif</div><div class="value"><?= $activePrograms ?></div></div>
    <div class="card"><div class="label">Staff Aktif</div><div class="value"><?= $activeStaff ?></div></div>
</div>

<div class="section-head">
    <h3 class="section-title">Rekap Donasi Terbaru</h3>
    <a class="btn light" href="index.php?route=app&page=rekap-donasi">Lihat Semua</a>
</div>
<div class="panel table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Donatur</th><th>Program</th><th>Jumlah</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach (array_slice($_SESSION['donations'], 0, 5) as $d): ?>
                <tr>
                    <td class="id">#<?= e($d['id']) ?></td>
                    <td><?= avatar($d['init'], $d['col']) ?><?= e($d['donor']) ?></td>
                    <td><?= e($d['program']) ?></td>
                    <td class="amount">Rp <?= e($d['amount']) ?></td>
                    <td><?= badge($d['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="section-head">
    <h3 class="section-title">Program Bantuan</h3>
    <a class="btn light" href="index.php?route=app&page=program-admin">Kelola Program</a>
</div>
<div class="program-grid">
    <?php foreach ($_SESSION['programs'] as $p): ?>
        <?php if (($p['status'] ?? '') === 'deleted') continue; ?>
        <a class="program-card-v2 program-card-link" href="index.php?route=app&page=program-detail&id=<?= e($p['id']) ?>">
            <?php $programImage = sipedo_program_image($p); ?>
            <div class="pc-banner" <?= $programImage === '' ? 'style="background:' . e($p['gradient'] ?? 'linear-gradient(135deg,#0D1B3E,#2A4080)') . ';"' : '' ?>>
                <?php if ($programImage !== ''): ?>
                    <img src="<?= e(pub($programImage)) ?>" alt="<?= e($p['name']) ?>">
                <?php endif; ?>
                <div class="pc-banner-badge"><?= program_badge($p['status']) ?></div>
            </div>
            <div class="pc-body">
                <h4 class="pc-title"><?= e($p['name']) ?></h4>
                <p class="pc-desc"><?= e($p['desc'] ?? '') ?></p>
                <div class="pc-meta">
                    <span><?= e($p['cat']) ?></span>
                    <span>Tenggat: <?= e($p['deadline']) ?></span>
                </div>
                <div class="pc-stats">
                    <div>
                        <div class="pc-label">Terkumpul</div>
                        <div class="pc-value pc-emerald">Rp <?= e($p['collected']) ?> Jt</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="pc-label">Target</div>
                        <div class="pc-value">Rp <?= e($p['target']) ?> Jt</div>
                    </div>
                </div>
                <div class="progress"><span style="width:<?= e($p['pct']) ?>%"></span></div>
                <div class="pc-pct"><?= e($p['pct']) ?>% tercapai</div>
                <span class="pc-cta">Klik untuk Detail →</span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
    
