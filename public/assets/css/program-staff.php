<?php

$myUserId = current_user()['db_id'] ?? 0;
$allPrograms = ProgramModel::byStaff($myUserId);
$programs = array_values(array_filter($allPrograms, fn($p) => ($p['status'] ?? '') !== 'deleted' && ($p['status'] ?? '') !== 'inactive'));
$draftCount = count(array_filter($allPrograms, fn($p) => ($p['status'] ?? '') === 'inactive'));
?>
<div class="section-head">
    <h3 class="section-title">Program yang Dikelola</h3>
    <a class="btn green" href="index.php?route=app&page=tambah-program">+ Tambah Program Baru</a>
</div>

<div class="program-tabs">
    <a class="program-tab active" href="index.php?route=app&page=program-staff">
        Program Terbit <span><?= e(count($programs)) ?></span>
    </a>
    <a class="program-tab" href="index.php?route=app&page=draft-program">
        Draft <span><?= e($draftCount) ?></span>
    </a>
</div>

<?php if (empty($programs)): ?>
    <div class="empty-state">
        <h4>Belum ada program yang dipublikasikan</h4>
        <p>Program berstatus aktif atau selesai akan muncul di sini. Draft yang belum dipublikasikan bisa dibuka dari tab Draft.</p>
        <a class="btn green" href="index.php?route=app&page=draft-program">Lihat Draft Program</a>
    </div>
<?php else: ?>
    <div class="program-grid">
        <?php foreach ($programs as $p): ?>
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
                    <p class="pc-desc"><?php
                        $d = trim($p['desc'] ?? '');
                        $firstLine = trim(explode("\n", $d)[0]);
                        $d = $firstLine !== '' ? $firstLine : $d;
                        echo e(mb_strlen($d) > 90 ? mb_substr($d, 0, 87) . '...' : $d);
                    ?></p>
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
                    <span class="pc-cta">Klik untuk Edit / Detail -></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
