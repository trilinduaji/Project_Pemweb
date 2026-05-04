<?php
$pending = array_filter($_SESSION['donations'], fn($d) => $d['status'] === 'pending');
$programs = $_SESSION['programs'] ?? [];
?>
<div class="stats">
    <div class="card"><div class="label">Donasi Pending</div><div class="value"><?= count($pending) ?></div></div>
    <div class="card"><div class="label">Diverifikasi Hari Ini</div><div class="value">4</div></div>
    <div class="card"><div class="label">Ditolak Hari Ini</div><div class="value">1</div></div>
    <div class="card"><div class="label">Total Diproses</div><div class="value">127</div></div>
</div>

<div class="section-head">
    <h3 class="section-title">Program yang Saya Daftarkan</h3>
    <a class="btn light" href="app.php">Lihat Semua</a>
</div>
<div class="program-grid">
    <?php foreach ($programs as $p): ?>
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
                <span class="pc-cta">Klik untuk Edit / Detail →</span>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div class="section-head" style="margin-top:24px;">
    <h3 class="section-title">Donasi Pending</h3>
    <a class="btn light" href="app.php">Panel Verifikasi</a>
</div>
<div class="panel table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Donatur</th><th>Program</th><th>Jumlah</th><th>Bukti</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php foreach ($pending as $d): ?>
                <tr>
                    <td class="id">#<?= e($d['id']) ?></td>
                    <td><?= avatar($d['init'], $d['col']) ?><?= e($d['donor']) ?></td>
                    <td><?= e($d['program']) ?></td>
                    <td class="amount">Rp <?= e($d['amount']) ?></td>
                    <td>
                        <?php if (!empty($d['proof'])): ?>
                            <a class="btn light" href="<?= e($d['proof']) ?>" target="_blank">Lihat Bukti</a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <form action="actions/donation.php" method="post">
                            <input type="hidden" name="action" value="verify">
                            <input type="hidden" name="id" value="<?= e($d['id']) ?>">
                            <button class="btn green" type="submit">Setujui</button>
                        </form>
                        <form action="actions/donation.php" method="post">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id" value="<?= e($d['id']) ?>">
                            <button class="btn red" type="submit">Tolak</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

