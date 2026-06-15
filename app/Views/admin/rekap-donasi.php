<?php
$filterStatus  = $_GET['status']  ?? '';
$filterProgram = $_GET['program'] ?? '';


$programOptions = [];
foreach ($_SESSION['programs'] ?? [] as $p) {
    if (($p['status'] ?? '') !== 'deleted') {
        $programOptions[$p['id']] = $p['name'];
    }
}


$filtered = array_values(array_filter($_SESSION['donations'], function ($d) use ($filterStatus, $filterProgram) {
    if ($filterStatus  !== '' && $d['status']  !== $filterStatus)  return false;
    if ($filterProgram !== '' && ($d['progId'] ?? $d['program']) !== $filterProgram) return false;
    return true;
}));

$totalFiltered = count($filtered);
$totalAmount   = 0;
foreach ($filtered as $d) {
    $totalAmount += (int) str_replace('.', '', $d['amount']);
}
$totalAmountFmt = 'Rp ' . number_format($totalAmount, 0, ',', '.');
?>

<div class="section-head">
    <h3 class="section-title">Rekap Seluruh Donasi</h3>
    <button class="btn light" onclick="exportCSV()" type="button">&#8595; Ekspor CSV</button>
</div>

<div class="panel" style="padding:14px 16px;margin-bottom:16px;">
    <form action="index.php" method="get" class="filter-bar" style="flex-wrap:wrap;gap:8px;">
        <input type="hidden" name="route" value="app">
        <input type="hidden" name="page"  value="rekap-donasi">

        <select name="status">
            <option value="">Semua Status</option>
            <option value="pending"  <?= $filterStatus === 'pending'  ? 'selected' : '' ?>>Pending</option>
            <option value="verified" <?= $filterStatus === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
            <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
        </select>

        <select name="program">
            <option value="">Semua Program</option>
            <?php foreach ($programOptions as $pid => $pname): ?>
                <option value="<?= e($pid) ?>" <?= $filterProgram === $pid ? 'selected' : '' ?>>
                    <?= e($pname) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn" type="submit">Filter</button>

        <?php if ($filterStatus !== '' || $filterProgram !== ''): ?>
            <a class="btn light" href="index.php?route=app&page=rekap-donasi">Reset</a>
        <?php endif; ?>

        <span style="margin-left:auto;font-size:13px;color:var(--muted,#6b7280);align-self:center;">
            <?= $totalFiltered ?> data &nbsp;|&nbsp; Total: <?= $totalAmountFmt ?>
        </span>
    </form>
</div>

<div class="panel table-wrap">
    <table id="rekap-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Donatur</th>
                <th>Program</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Tanggal</th>
                <th>Diproses Oleh</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($filtered)): ?>
                <tr><td colspan="8" style="text-align:center;color:#6b7280;padding:24px;">
                    Tidak ada data donasi yang sesuai filter.
                </td></tr>
            <?php endif; ?>
            <?php foreach ($filtered as $d): ?>
                <tr>
                    <td class="id">#<?= e($d['id']) ?></td>
                    <td><?= avatar($d['init'], $d['col']) ?><?= e($d['donor']) ?></td>
                    <td><?= e($d['program']) ?></td>
                    <td class="amount">Rp <?= e($d['amount']) ?></td>
                    <td><?= e($d['method']) ?></td>
                    <td class="muted"><?= e($d['date']) ?></td>
                    <td><?= e($d['processedBy']) ?></td>
                    <td><?= badge($d['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function exportCSV() {
    var table = document.getElementById('rekap-table');
    if (!table) return;

    var rows = table.querySelectorAll('tr');
    var csv  = [];

    rows.forEach(function (row) {
        var cols = row.querySelectorAll('th, td');
        var rowData = [];
        cols.forEach(function (col) {
            var text = col.innerText.replace(/\n/g, ' ').trim();

            text = text.replace(/"/g, '""');
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });

    var csvContent = '\uFEFF' + csv.join('\n');
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');

    var now  = new Date();
    var ts   = now.getFullYear()
             + String(now.getMonth() + 1).padStart(2, '0')
             + String(now.getDate()).padStart(2, '0');

    a.href     = url;
    a.download = 'rekap-donasi-' + ts + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
