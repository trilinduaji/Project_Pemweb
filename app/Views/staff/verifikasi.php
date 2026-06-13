<?php
$allDonations = $_SESSION['donations'] ?? [];
$pendingCount = count(array_filter($allDonations, fn($d) => $d['status'] === 'pending'));


$filterStatus = $_GET['status'] ?? '';
$filterQ      = trim($_GET['q'] ?? '');

$visible = array_filter($allDonations, function($d) use ($filterStatus, $filterQ) {
    if ($filterStatus !== '' && $d['status'] !== $filterStatus) return false;
    if ($filterQ !== '' && stripos($d['donor'] ?? '', $filterQ) === false
                       && stripos($d['program'] ?? '', $filterQ) === false) return false;
    return true;
});
?>


<div class="stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
    <div class="card" style="border-left:4px solid #f59e0b;">
        <div class="label">Menunggu Verifikasi</div>
        <div class="value" style="color:#d97706;"><?= $pendingCount ?></div>
    </div>
    <div class="card" style="border-left:4px solid #059669;">
        <div class="label">Sudah Diterima</div>
        <div class="value" style="color:#059669;">
            <?= count(array_filter($allDonations, fn($d) => $d['status'] === 'verified')) ?>
        </div>
    </div>
    <div class="card" style="border-left:4px solid #dc2626;">
        <div class="label">Ditolak</div>
        <div class="value" style="color:#dc2626;">
            <?= count(array_filter($allDonations, fn($d) => $d['status'] === 'rejected')) ?>
        </div>
    </div>
</div>


<div class="section-head">
    <h3 class="section-title">Panel Verifikasi Donasi</h3>
    <form action="index.php" method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <input type="hidden" name="route" value="app">
        <input type="hidden" name="page" value="verifikasi">
        <input type="text" name="q" placeholder="Cari donatur / program…"
               value="<?= e($filterQ) ?>"
               style="padding:7px 11px;border:1.5px solid #dde3ec;border-radius:9px;font-size:0.8rem;width:200px;">
        <select name="status"
                style="padding:7px 11px;border:1.5px solid #dde3ec;border-radius:9px;font-size:0.8rem;">
            <option value="">Semua Status</option>
            <option value="pending"  <?= $filterStatus === 'pending'  ? 'selected':'' ?>>Pending</option>
            <option value="verified" <?= $filterStatus === 'verified' ? 'selected':'' ?>>Terverifikasi</option>
            <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected':'' ?>>Ditolak</option>
        </select>
        <button class="btn" type="submit">Filter</button>
        <?php if ($filterStatus !== '' || $filterQ !== ''): ?>
            <a class="btn light" href="index.php?route=app&page=verifikasi">Reset</a>
        <?php endif; ?>
    </form>
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
                <th>Status</th>
                <th>Catatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($visible)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;color:#6b7280;padding:32px;">
                        Tidak ada donasi yang sesuai filter.
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($visible as $d): ?>
                <tr <?= $d['status'] === 'pending' ? 'style="background:#fffbeb;"' : '' ?>>
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
                               style="font-size:0.72rem;padding:6px 10px;">Lihat Bukti</a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= badge($d['status']) ?></td>

                    <td style="max-width:160px;font-size:0.78rem;color:#6b7280;">
                        <?php if (!empty($d['note'])): ?>
                            <span title="<?= e($d['note']) ?>" style="color:#dc2626;">
                                <?= e(mb_strlen($d['note']) > 40 ? mb_substr($d['note'], 0, 40) . '…' : $d['note']) ?>
                            </span>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <?php if ($d['status'] === 'pending'): ?>

                            <button class="btn green" type="button"
                                title="Terima donasi ini"
                                onclick="sipedoShowVerifyForm('<?= e($d['id']) ?>', '<?= e(addslashes($d['donor'])) ?>')">
                                ✓ Terima
                            </button>

                            <button class="btn red" type="button"
                                title="Tolak donasi ini"
                                onclick="sipedoShowRejectForm('<?= e($d['id']) ?>', '<?= e(addslashes($d['donor'])) ?>')">
                                ✕ Tolak
                            </button>
                        <?php else: ?>
                            <span class="muted" style="font-size:0.78rem;">
                                <?= e($d['processedBy'] ?? '—') ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<!-- Modal Terima Donasi -->
<div id="sipedoVerifyModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:28px 28px 20px;width:100%;max-width:440px;box-shadow:0 8px 32px rgba(0,0,0,.18);">
        <h3 style="margin:0 0 6px;font-size:1rem;color:#1f2937;">✓ Terima Donasi</h3>
        <p id="sipedoVerifyDesc" style="color:#6b7280;font-size:0.84rem;margin:0 0 16px;"></p>
        <form id="sipedoVerifyForm" action="index.php?route=donation/verify" method="post">
            <input type="hidden" name="action" value="verify">
            <input type="hidden" name="id" id="sipedoVerifyId" value="">
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:0.84rem;font-weight:600;margin-bottom:6px;">
                    Catatan <span style="color:#9ca3af;font-weight:400;">(opsional)</span>
                </label>
                <textarea name="note" id="sipedoVerifyNote" rows="3"
                    placeholder="Contoh: Donasi sudah dikonfirmasi ke rekening, terima kasih atas kontribusinya."
                    style="width:100%;padding:8px 10px;border:1.5px solid #dde3ec;border-radius:8px;font-size:0.84rem;resize:vertical;box-sizing:border-box;"></textarea>
                <small style="color:#6b7280;">Catatan ini akan ditampilkan ke donatur sebagai informasi tambahan.</small>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" class="btn light" onclick="sipedoHideVerifyModal()">Batal</button>
                <button type="submit" class="btn green">✓ Terima Donasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak Donasi -->
<div id="sipedoRejectModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:28px 28px 20px;width:100%;max-width:440px;box-shadow:0 8px 32px rgba(0,0,0,.18);">
        <h3 style="margin:0 0 6px;font-size:1rem;color:#1f2937;">✕ Tolak Donasi</h3>
        <p id="sipedoRejectDesc" style="color:#6b7280;font-size:0.84rem;margin:0 0 16px;"></p>
        <form id="sipedoRejectForm" action="index.php?route=donation/verify" method="post">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="id" id="sipedoRejectId" value="">
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:0.84rem;font-weight:600;margin-bottom:6px;">
                    Alasan Penolakan <span style="color:#dc2626;">*</span>
                </label>
                <textarea name="note" id="sipedoRejectNote" rows="3" required
                    placeholder="Contoh: Bukti transfer tidak terbaca, nominal tidak sesuai, dll."
                    style="width:100%;padding:8px 10px;border:1.5px solid #dde3ec;border-radius:8px;font-size:0.84rem;resize:vertical;box-sizing:border-box;"></textarea>
                <small style="color:#6b7280;">Alasan ini akan ditampilkan ke donatur agar bisa memperbaiki pengiriman.</small>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" class="btn light" onclick="sipedoHideRejectModal()">Batal</button>
                <button type="submit" class="btn red">✕ Tolak Donasi</button>
            </div>
        </form>
    </div>
</div>

<script>
function sipedoShowVerifyForm(id, donor) {
    document.getElementById('sipedoVerifyId').value = id;
    document.getElementById('sipedoVerifyDesc').textContent = 'Donasi #' + id + ' dari ' + donor + ' akan diterima.';
    document.getElementById('sipedoVerifyNote').value = '';
    var modal = document.getElementById('sipedoVerifyModal');
    modal.style.display = 'flex';
    setTimeout(function() { document.getElementById('sipedoVerifyNote').focus(); }, 80);
}
function sipedoHideVerifyModal() {
    document.getElementById('sipedoVerifyModal').style.display = 'none';
}
document.getElementById('sipedoVerifyModal').addEventListener('click', function(e) {
    if (e.target === this) sipedoHideVerifyModal();
});

function sipedoShowRejectForm(id, donor) {
    document.getElementById('sipedoRejectId').value = id;
    document.getElementById('sipedoRejectDesc').textContent = 'Donasi #' + id + ' dari ' + donor + ' akan ditolak.';
    document.getElementById('sipedoRejectNote').value = '';
    var modal = document.getElementById('sipedoRejectModal');
    modal.style.display = 'flex';
    setTimeout(function() { document.getElementById('sipedoRejectNote').focus(); }, 80);
}
function sipedoHideRejectModal() {
    document.getElementById('sipedoRejectModal').style.display = 'none';
}
document.getElementById('sipedoRejectModal').addEventListener('click', function(e) {
    if (e.target === this) sipedoHideRejectModal();
});
</script>
