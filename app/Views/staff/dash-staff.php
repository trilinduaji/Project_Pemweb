<?php
$myUserId = (int)(current_user()['db_id'] ?? 0);
$myDonations = DonationModel::byStaff($myUserId);
$pending  = array_values(array_filter($myDonations, fn($d) => $d['status'] === 'pending'));
$verified = array_values(array_filter($myDonations, fn($d) => $d['status'] === 'verified'));
$rejected = array_values(array_filter($myDonations, fn($d) => $d['status'] === 'rejected'));

$programs = ProgramModel::byStaff($myUserId);
?>


<div class="stats">
    <div class="card" style="border-left:4px solid #f59e0b;">
        <div class="label">Donasi Pending</div>
        <div class="value" style="color:#d97706;"><?= count($pending) ?></div>
    </div>
    <div class="card" style="border-left:4px solid #059669;">
        <div class="label">Sudah Diterima</div>
        <div class="value" style="color:#059669;"><?= count($verified) ?></div>
    </div>
    <div class="card" style="border-left:4px solid #dc2626;">
        <div class="label">Ditolak</div>
        <div class="value" style="color:#dc2626;"><?= count($rejected) ?></div>
    </div>
    <div class="card">
        <div class="label">Total Program</div>
        <div class="value"><?= count(array_filter($programs, fn($p) => ($p['status'] ?? '') !== 'deleted')) ?></div>
    </div>
</div>


<div class="section-head" style="margin-top:8px;">
    <h3 class="section-title" style="display:flex;align-items:center;gap:10px;">
        Donasi Menunggu Verifikasi
        <?php if (count($pending) > 0): ?>
            <span style="background:#fef3c7;color:#92400e;font-family:'Poppins',sans-serif;
                         font-size:0.72rem;font-weight:700;padding:3px 9px;border-radius:20px;">
                <?= count($pending) ?> baru
            </span>
        <?php endif; ?>
    </h3>
    <a class="btn light" href="index.php?route=app&page=verifikasi">Lihat Semua &rarr;</a>
</div>

<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Donatur</th>
                <th>Program</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Bukti</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending)): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:28px;color:#6b7280;">
                        🎉 Tidak ada donasi yang menunggu verifikasi.
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($pending as $d): ?>
                <tr style="background:#fffbeb;">
                    <td class="id">#<?= e($d['id']) ?></td>
                    <td style="white-space:nowrap;">
                        <?= avatar($d['init'], $d['col']) ?><?= e($d['donor']) ?>
                    </td>
                    <td><?= e($d['program']) ?></td>
                    <td class="amount">Rp <?= e($d['amount']) ?></td>
                    <td><?= e($d['method']) ?></td>
                    <td>
                        <?php if (!empty($d['proof'])): ?>
                            <a class="btn light" href="<?= e(pub($d['proof'])) ?>" target="_blank"
                               style="font-size:0.72rem;padding:5px 9px;">Lihat Bukti</a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">

                        <form action="index.php?route=donation/verify" method="post" style="display:inline;">
                            <input type="hidden" name="action" value="verify">
                            <input type="hidden" name="id" value="<?= e($d['id']) ?>">
                            <button class="btn green" type="submit"
                                onclick="return confirm('Terima donasi #<?= e($d['id']) ?> dari <?= e($d['donor']) ?>?')">
                                ✓ Terima
                            </button>
                        </form>

                        <form action="index.php?route=donation/verify" method="post" style="display:inline;">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id" value="<?= e($d['id']) ?>">
                            <button class="btn red" type="submit"
                                onclick="return confirm('Tolak donasi #<?= e($d['id']) ?> dari <?= e($d['donor']) ?>?')">
                                ✕ Tolak
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<div class="section-head" style="margin-top:24px;">
    <h3 class="section-title">Program Bantuan</h3>
    <a class="btn light" href="index.php?route=app&page=program-staff">Lihat Semua</a>
</div>
<div class="program-grid">
    <?php foreach ($programs as $p): ?>
        <?php if (($p['status'] ?? '') === 'deleted') continue; ?>
        <a class="program-card-v2 program-card-link"
           href="index.php?route=app&page=program-detail&id=<?= e($p['id']) ?>">
            <?php $programImage = sipedo_program_image($p); ?>
            <div class="pc-banner"
                 <?= $programImage === '' ? 'style="background:' . e($p['gradient'] ?? 'linear-gradient(135deg,#0D1B3E,#2A4080)') . ';"' : '' ?>>
                <?php if ($programImage !== ''): ?>
                    <img src="<?= e(pub($programImage)) ?>" alt="<?= e($p['name']) ?>">
                <?php endif; ?>
                <div class="pc-banner-badge"><?= program_badge($p['status']) ?></div>
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
                <span class="pc-cta">Klik untuk Edit / Detail &rarr;</span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
