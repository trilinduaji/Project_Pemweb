<?php
// Ambil program milik staff ini
$myUserId   = current_user()['db_id'] ?? 0;
$myPrograms = ProgramModel::byStaff($myUserId);
$myProgIds  = array_column($myPrograms, 'id');

// Bangun top donors dari donasi verified, khusus untuk program staff ini
$donorMap = [];
foreach ($_SESSION['donations'] ?? [] as $d) {
    if (($d['status'] ?? '') !== 'verified') continue;
    if (!in_array($d['progId'] ?? '', $myProgIds, true)) continue;
    $key    = $d['donor'];
    $amount = (int) str_replace(['.', ','], ['', ''], $d['amount'] ?? '0');
    if (!isset($donorMap[$key])) {
        $donorMap[$key] = [
            'init'  => $d['init'] ?? '??',
            'col'   => $d['col']  ?? '#059669',
            'name'  => $d['donor'],
            'total' => 0,
            'progs' => [],
        ];
    }
    $donorMap[$key]['total'] += $amount;
    $progName = $d['program'] ?? '';
    if ($progName && !in_array($progName, $donorMap[$key]['progs'], true)) {
        $donorMap[$key]['progs'][] = $progName;
    }
}
usort($donorMap, fn($a, $b) => $b['total'] <=> $a['total']);
$topDonors = array_slice(array_values($donorMap), 0, 10);
$medals = [0 => '🥇', 1 => '🥈', 2 => '🥉'];
?>
<div class="section-head">
    <h3 class="section-title">Progress Dana Program</h3>
</div>
<div class="panel" style="padding:20px 24px;">
    <?php if (empty($myPrograms)): ?>
        <p style="color:#6b7280;text-align:center;padding:16px;">Belum ada program yang Anda kelola.</p>
    <?php endif; ?>
    <?php foreach ($myPrograms as $p): ?>
        <?php if (($p['status'] ?? '') === 'deleted') continue; ?>
        <div style="margin-bottom:18px;">
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px;">
                <strong><?= e($p['name']) ?></strong>
                <span class="amount"><?= e($p['pct']) ?>%</span>
            </div>
            <div class="progress"><span style="width:<?= e(min(100, (float)$p['pct'])) ?>%"></span></div>
            <div style="display:flex;justify-content:space-between;margin-top:4px;font-size:.8rem;color:#6b7280;">
                <span>Rp <?= e($p['collected']) ?> Jt</span>
                <span>Target: Rp <?= e($p['target']) ?> Jt</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="section-head" style="margin-top:24px;">
    <h3 class="section-title">Top Donatur</h3>
</div>
<div class="panel table-wrap">
    <table>
        <thead><tr><th>Peringkat</th><th>Donatur</th><th>Program Didukung</th><th>Total Donasi</th></tr></thead>
        <tbody>
            <?php if (empty($topDonors)): ?>
                <tr><td colspan="4" style="text-align:center;color:#6b7280;padding:24px;">Belum ada donasi terverifikasi untuk program Anda.</td></tr>
            <?php endif; ?>
            <?php foreach ($topDonors as $idx => $d): ?>
                <tr>
                    <td class="id">
                        <?= $medals[$idx] ?? '#' . ($idx + 1) ?>
                    </td>
                    <td><?= avatar($d['init'], $d['col']) ?><?= e($d['name']) ?></td>
                    <td><?= e(implode(', ', $d['progs'])) ?></td>
                    <td class="amount">Rp <?= e(number_format($d['total'], 0, ',', '.')) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
