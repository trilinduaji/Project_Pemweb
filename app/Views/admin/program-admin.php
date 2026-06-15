<?php
$programs = $_SESSION['programs'] ?? [];

$countActive  = count(array_filter($programs, fn($p) => ($p['status'] ?? '') === 'active'));
$countClosed  = count(array_filter($programs, fn($p) => ($p['status'] ?? '') === 'closed'));
$countInactive = count(array_filter($programs, fn($p) => ($p['status'] ?? '') === 'inactive'));
$countDeleted = count(array_filter($programs, fn($p) => ($p['status'] ?? '') === 'deleted'));

$q          = trim($_GET['q'] ?? '');
$statusFilt = $_GET['status'] ?? '';
$catFilt    = $_GET['cat'] ?? '';

$categories = array_values(array_unique(array_map(fn($p) => $p['cat'] ?? '', $programs)));
sort($categories);

$visible = array_filter($programs, function ($p) use ($q, $statusFilt, $catFilt) {
    if (($p['status'] ?? '') === 'deleted' && $statusFilt !== 'deleted') return false;
    if ($q !== '' && stripos($p['name'] ?? '', $q) === false) return false;
    if ($statusFilt !== '' && ($p['status'] ?? '') !== $statusFilt) return false;
    if ($catFilt !== '' && ($p['cat'] ?? '') !== $catFilt) return false;
    return true;
});
?>
<div class="stats">
    <div class="card"><div class="label">Program Aktif</div><div class="value"><?= $countActive ?></div></div>
    <div class="card"><div class="label">Program Selesai</div><div class="value"><?= $countClosed ?></div></div>
    <div class="card"><div class="label">Program Draft</div><div class="value"><?= $countInactive ?></div></div>
    <div class="card"><div class="label">Program Dihapus</div><div class="value"><?= $countDeleted ?></div></div>
</div>

<div class="section-head">
    <h3 class="section-title">Daftar Program</h3>
    <form action="index.php" method="get" class="filter-bar">
        <input type="hidden" name="route" value="app">
        <input type="hidden" name="page" value="program-admin">
        <input type="text" name="q" placeholder="Cari nama program..." value="<?= e($q) ?>">
        <select name="status">
            <option value="">Semua Status</option>
            <option value="active"  <?= $statusFilt === 'active'  ? 'selected' : '' ?>>Aktif</option>
            <option value="closed"  <?= $statusFilt === 'closed'  ? 'selected' : '' ?>>Selesai</option>
            <option value="inactive" <?= $statusFilt === 'inactive' ? 'selected' : '' ?>>Draft/Nonaktif</option>
            <option value="deleted" <?= $statusFilt === 'deleted' ? 'selected' : '' ?>>Dihapus</option>
        </select>
        <select name="cat">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $c): if ($c === '') continue; ?>
                <option value="<?= e($c) ?>" <?= $catFilt === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Filter</button>
        <?php if ($q !== '' || $statusFilt !== '' || $catFilt !== ''): ?>
            <a class="btn light" href="index.php?route=app&page=program-admin">Reset</a>
        <?php endif; ?>
    </form>
</div>
<div class="panel table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Nama</th><th>Kategori</th><th>Target</th><th>Terkumpul</th><th>Progress</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php if (empty($visible)): ?>
                <tr><td colspan="8" style="text-align:center;color:#6b7280;padding:24px;">Tidak ada program yang cocok dengan filter.</td></tr>
            <?php endif; ?>
            <?php foreach ($visible as $p): ?>
                <tr>
                    <td class="id"><a href="index.php?route=app&page=program-detail&id=<?= e($p['id']) ?>" style="color:inherit;">#<?= e($p['id']) ?></a></td>
                    <td><a href="index.php?route=app&page=program-detail&id=<?= e($p['id']) ?>" style="color:#1A9C6B;font-weight:600;"><?= e($p['name']) ?></a></td>
                    <td><?= e($p['cat']) ?></td>
                    <td>Rp <?= e($p['target']) ?> Jt</td>
                    <td class="amount">Rp <?= e($p['collected']) ?> Jt</td>
                    <td><?= progress_bar($p['pct']) ?></td>
                    <td><?= badge($p['status']) ?></td>
                    <td class="actions">
                        <?php if ($p['status'] === 'active'): ?>
                            <form action="index.php?route=program/close" method="post">
                                <input type="hidden" name="action" value="close">
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                                <button class="btn amber" type="submit">Tutup</button>
                            </form>
                        <?php elseif ($p['status'] === 'closed'): ?>
                            <form action="index.php?route=program/reopen" method="post" onsubmit="return confirm('Buka kembali program <?= e($p['name']) ?>?');">
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                                <button class="btn green" type="submit">Buka Kembali</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($p['status'] !== 'deleted'): ?>
                            <form action="index.php?route=program/delete" method="post" onsubmit="return confirm('Hapus program <?= e($p['name']) ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                                <button class="btn red" type="submit">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
