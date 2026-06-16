<?php

$myUserId = current_user()['db_id'] ?? 0;
$allPrograms = ProgramModel::byStaff($myUserId);
$draftPrograms = array_values(array_filter($allPrograms, fn($p) => ($p['status'] ?? '') === 'inactive'));
$publishedCount = count(array_filter($allPrograms, fn($p) => ($p['status'] ?? '') !== 'deleted' && ($p['status'] ?? '') !== 'inactive'));
?>
<div class="section-head">
    <div>
        <h3 class="section-title">Draft Program</h3>
        <p class="section-subtitle">Program yang sudah disimpan, tetapi belum dipublikasikan.</p>
    </div>
    <a class="btn green" href="index.php?route=app&page=tambah-program">+ Tambah Program Baru</a>
</div>

<div class="program-tabs">
    <a class="program-tab" href="index.php?route=app&page=program-staff">
        Program Terbit <span><?= e($publishedCount) ?></span>
    </a>
    <a class="program-tab active" href="index.php?route=app&page=draft-program">
        Draft <span><?= e(count($draftPrograms)) ?></span>
    </a>
</div>

<?php if (empty($draftPrograms)): ?>
    <div class="empty-state">
        <h4>Belum ada draft program</h4>
        <p>Program yang disimpan sebagai draft akan muncul di sini agar bisa dibuka, diedit, lalu dipublikasikan saat sudah siap.</p>
        <a class="btn green" href="index.php?route=app&page=tambah-program">Buat Draft Program</a>
    </div>
<?php else: ?>
    <div class="program-grid">
        <?php foreach ($draftPrograms as $p): ?>
            <div class="program-card-v2 draft-card">
                <a class="program-card-link" href="index.php?route=app&page=program-detail&id=<?= e($p['id']) ?>&from=draft-program">
                    <?php $programImage = sipedo_program_image($p); ?>
                    <div class="pc-banner" <?= $programImage === '' ? 'style="background:' . e($p['gradient'] ?? 'linear-gradient(135deg,#0D1B3E,#2A4080)') . ';"' : '' ?>>
                        <?php if ($programImage !== ''): ?>
                            <img src="<?= e(pub($programImage)) ?>" alt="<?= e($p['name']) ?>">
                        <?php endif; ?>
                        <div class="pc-banner-badge"><span class="badge badge-inactive">Draft</span></div>
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
                    </div>
                </a>

                <div class="draft-actions">
                    <a class="btn light" href="index.php?route=app&page=edit-program&id=<?= e($p['id']) ?>">Edit</a>
                    <form action="index.php?route=program/publish" method="post" onsubmit="return confirm('Publikasikan program <?= e($p['name']) ?>?');">
                        <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                        <button class="btn green" type="submit">Publish</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
