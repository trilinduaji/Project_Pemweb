<?php

$donations = $_SESSION['donations'] ?? [];
$programs  = $_SESSION['programs']  ?? [];

$totalVerified   = 0;
$totalDana       = 0;
$activeDonorSet  = [];
$statusCount     = ['pending' => 0, 'verified' => 0, 'rejected' => 0];

foreach ($donations as $d) {
    $status = $d['status'] ?? 'pending';
    if (isset($statusCount[$status])) $statusCount[$status]++;

    if ($status === 'verified') {
        $totalVerified++;
        $totalDana += (int) str_replace(['.', ',', 'Rp', ' '], '', $d['amount'] ?? '0');
        if (!empty($d['donor'])) $activeDonorSet[$d['donor']] = true;
    }
}

$activePrograms = 0;
foreach ($programs as $p) {
    if (($p['status'] ?? '') === 'active') $activePrograms++;
}


$progStats = [];
foreach ($programs as $p) {
    // Field ID program di session adalah 'id' (kode PR-01 dst), bukan index numerik
    // Field donasi ke program di session adalah 'progId'
    $progId = $p['id'] ?? '';
    $collected = 0;
    foreach ($donations as $d) {
        if (($d['progId'] ?? '') === $progId && ($d['status'] ?? '') === 'verified') {
            $collected += (int) str_replace(['.', ',', 'Rp', ' '], '', $d['amount'] ?? '0');
        }
    }
    // Field kategori di session adalah 'cat', bukan 'category'
    // Target di session dalam satuan juta, konversi ke Rupiah
    $progStats[] = [
        'name'      => $p['name'] ?? $progId,
        'category'  => $p['cat'] ?? ($p['category'] ?? '-'),
        'target'    => (int)(((float)($p['target'] ?? 0)) * 1000000),
        'collected' => $collected,
    ];
}
usort($progStats, fn($a, $b) => $b['collected'] - $a['collected']);
$top5 = array_slice($progStats, 0, 5);


function fmt(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
?>

<div class="section-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <h3 class="section-title">Laporan &amp; Ekspor</h3>
    <button class="btn green" onclick="exportCSV()" id="btnEkspor">
        ⬇ Ekspor Laporan CSV
    </button>
</div>


<div class="grid two" style="margin-bottom:24px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#0f1f3d;"><?= $totalVerified ?></div>
        <div style="color:#6b7280;font-size:.9rem;margin-top:4px;">Donasi Terverifikasi</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#16a34a;"><?= fmt($totalDana) ?></div>
        <div style="color:#6b7280;font-size:.9rem;margin-top:4px;">Total Dana Terkumpul</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#2563eb;"><?= count($activeDonorSet) ?></div>
        <div style="color:#6b7280;font-size:.9rem;margin-top:4px;">Donatur Aktif</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:#d97706;"><?= $activePrograms ?></div>
        <div style="color:#6b7280;font-size:.9rem;margin-top:4px;">Program Aktif</div>
    </div>
</div>


<div class="section-head"><h3 class="section-title">Top 5 Program (Dana Terkumpul)</h3></div>
<div class="panel table-wrap" style="margin-bottom:24px;">
    <table id="tblTop5">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Program</th>
                <th>Kategori</th>
                <th>Target</th>
                <th>Terkumpul</th>
                <th>Progres</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($top5)): ?>
                <tr><td colspan="6" style="text-align:center;color:#6b7280;padding:18px;">Belum ada data program.</td></tr>
            <?php else: ?>
                <?php foreach ($top5 as $i => $p): ?>
                    <?php
                        $pct = $p['target'] > 0 ? min(100, round($p['collected'] / $p['target'] * 100)) : 0;
                    ?>
                    <tr>
                        <td class="id"><?= $i + 1 ?></td>
                        <td><?= e($p['name']) ?></td>
                        <td class="muted"><?= e($p['category']) ?></td>
                        <td><?= fmt($p['target']) ?></td>
                        <td style="font-weight:600;color:#16a34a;"><?= fmt($p['collected']) ?></td>
                        <td>
                            <div style="background:#e5e7eb;border-radius:999px;height:8px;min-width:80px;">
                                <div style="background:#16a34a;height:8px;border-radius:999px;width:<?= $pct ?>%;"></div>
                            </div>
                            <small class="muted"><?= $pct ?>%</small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div class="section-head"><h3 class="section-title">Donasi per Status</h3></div>
<div class="panel table-wrap">
    <table id="tblStatus">
        <thead>
            <tr><th>Status</th><th>Jumlah Donasi</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><?= badge('pending') ?></td>
                <td><strong><?= $statusCount['pending'] ?></strong></td>
                <td class="muted">Menunggu verifikasi staff</td>
            </tr>
            <tr>
                <td><?= badge('verified') ?></td>
                <td><strong><?= $statusCount['verified'] ?></strong></td>
                <td class="muted">Donasi sah &amp; tercatat</td>
            </tr>
            <tr>
                <td><?= badge('rejected') ?></td>
                <td><strong><?= $statusCount['rejected'] ?></strong></td>
                <td class="muted">Ditolak saat verifikasi</td>
            </tr>
            <tr style="background:#f9fafb;font-weight:600;">
                <td>Total</td>
                <td><?= array_sum($statusCount) ?></td>
                <td class="muted">Semua donasi masuk</td>
            </tr>
        </tbody>
    </table>
</div>


<script>
function exportCSV() {
    var rows = [];


    var now = new Date().toLocaleString('id-ID');
    rows.push(['LAPORAN SIPEDO', '', '', '', '', '']);
    rows.push(['Diekspor pada', now, '', '', '', '']);
    rows.push([]);


    rows.push(['=== RINGKASAN ===']);
    rows.push(['Donasi Terverifikasi', <?= $totalVerified ?>]);
    rows.push(['Total Dana Terkumpul', 'Rp <?= number_format($totalDana, 0, ',', '.') ?>']);
    rows.push(['Donatur Aktif', <?= count($activeDonorSet) ?>]);
    rows.push(['Program Aktif', <?= $activePrograms ?>]);
    rows.push([]);


    rows.push(['=== TOP 5 PROGRAM ===']);
    rows.push(['No', 'Nama Program', 'Kategori', 'Target (Rp)', 'Terkumpul (Rp)', 'Progres (%)']);
    <?php foreach ($top5 as $i => $p): ?>
    rows.push([
        <?= $i + 1 ?>,
        <?= json_encode($p['name']) ?>,
        <?= json_encode($p['category']) ?>,
        <?= $p['target'] ?>,
        <?= $p['collected'] ?>,
        <?= $p['target'] > 0 ? min(100, round($p['collected'] / $p['target'] * 100)) : 0 ?>
    ]);
    <?php endforeach; ?>
    rows.push([]);


    rows.push(['=== DONASI PER STATUS ===']);
    rows.push(['Status', 'Jumlah']);
    rows.push(['Pending',  <?= $statusCount['pending'] ?>]);
    rows.push(['Verified', <?= $statusCount['verified'] ?>]);
    rows.push(['Rejected', <?= $statusCount['rejected'] ?>]);
    rows.push(['Total',    <?= array_sum($statusCount) ?>]);


    var csv = rows.map(function(r) {
        return r.map(function(c) {
            var s = String(c === undefined || c === null ? '' : c);
            if (s.indexOf(',') >= 0 || s.indexOf('"') >= 0 || s.indexOf('\n') >= 0) {
                s = '"' + s.replace(/"/g, '""') + '"';
            }
            return s;
        }).join(',');
    }).join('\r\n');


    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href   = url;
    a.download = 'laporan-sipedo-' + new Date().toISOString().slice(0,10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
