<?php
$pending = count(array_filter($_SESSION['donations'], fn($d) => $d['status'] === 'pending'));
$activePrograms = count(array_filter($_SESSION['programs'], fn($p) => $p['status'] === 'active'));
$activeStaff = count(array_filter($_SESSION['staffList'], fn($s) => $s['status'] === 'active'));
?>
<div class="stats">
    <div class="card"><div class="label">Total Dana Masuk</div><div class="value">247,5 Jt</div></div>
    <div class="card"><div class="label">Total Pengguna</div><div class="value">1.342</div></div>
    <div class="card"><div class="label">Program Aktif</div><div class="value"><?= $activePrograms ?></div></div>
    <div class="card"><div class="label">Staff Aktif</div><div class="value"><?= $activeStaff ?></div></div>
</div>

<div class="section-head">
    <h3 class="section-title">Rekap Donasi Terbaru</h3>
    <a class="btn light" href="app.php">Lihat Semua</a>
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
    <a class="btn light" href="app.php">Kelola Program</a>
</div>
<div class="program-grid">
    <?php foreach ($_SESSION['programs'] as $p): ?>
        <?php if (($p['status'] ?? '') === 'deleted') continue; ?>
        <a class="program-card-v2 program-card-link" href="app.php">
            <div class="pc-banner" <?= empty($p['image']) ? 'style="background:' . e($p['gradient'] ?? 'linear-gradient(135deg,#0D1B3E,#2A4080)') . ';"' : '' ?>>
                <?php if (!empty($p['image'])): ?>
                    <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                <?php endif; ?>
                <div class="pc-banner-badge"><?= badge($p['status']) ?></div>
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

