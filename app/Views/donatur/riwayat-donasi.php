<?php
$user = current_user();

$myDonations = array_values(array_filter($_SESSION['donations'] ?? [], function ($d) use ($user) {
    return ($d['donor'] ?? '') === ($user['name'] ?? '');
}));

$parseAmount = function ($amount): int {
    return (int) preg_replace('/[^\d]/', '', (string) $amount);
};

$parseDate = function ($date): int {
    $date = trim((string) $date);
    if ($date === '' || strtolower($date) === 'baru saja') return time();

    $bulan = [
        'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
        'mei' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'agu' => 'Aug',
        'sep' => 'Sep', 'okt' => 'Oct', 'nov' => 'Nov', 'des' => 'Dec',
    ];

    $normalized = strtolower($date);
    foreach ($bulan as $id => $en) {
        $normalized = preg_replace('/\b' . preg_quote($id, '/') . '\b/i', $en, $normalized);
    }

    $ts = strtotime($normalized);
    return $ts ?: 0;
};

usort($myDonations, function ($a, $b) use ($parseDate) {
    return $parseDate($b['date'] ?? '') <=> $parseDate($a['date'] ?? '');
});

$totalNominal = 0;
$pendingCount = 0;
$verifiedCount = 0;
$rejectedCount = 0;
$programsSupported = [];
$programsSupportedVerified = [];

foreach ($myDonations as $d) {
    $status = strtolower((string)($d['status'] ?? ''));


    if ($status === 'verified') {
        $totalNominal += $parseAmount($d['amount'] ?? 0);
        if (!empty($d['program'])) $programsSupportedVerified[$d['program']] = true;
    }

    if ($status === 'pending')  $pendingCount++;
    if ($status === 'verified') $verifiedCount++;
    if ($status === 'rejected') $rejectedCount++;

    if (!empty($d['program'])) {
        $programsSupported[$d['program']] = true;
    }
}
?>

<div class="donation-history-page">
    <section class="history-hero">
        <div class="history-hero-copy">
            <span class="history-eyebrow">Riwayat Donasi</span>
            <h3 class="history-title">Perjalanan Kebaikan Anda</h3>
            <p class="history-copy">
                Lihat seluruh transaksi donasi Anda dalam satu tampilan yang lebih rapi,
                jelas, dan konsisten dengan halaman SIPEDO lainnya.
            </p>
        </div>

        <div class="history-total-card">
            <small>Total Kontribusi Terverifikasi</small>
            <strong><?= e(formatRupiahFull($totalNominal)) ?></strong>
            <span>
                <?= e((string) $verifiedCount) ?> donasi terverifikasi
                · <?= e((string) count($programsSupportedVerified)) ?> program didukung
                <?php if ($pendingCount > 0): ?>
                · <em style="color:#f59e0b;"><?= e((string) $pendingCount) ?> menunggu verifikasi</em>
                <?php endif; ?>
            </span>
        </div>
    </section>

    <section class="history-stats">
        <article class="history-stat-card">
            <div class="history-stat-icon">💳</div>
            <div>
                <strong><?= e((string) count($myDonations)) ?></strong>
                <span>Total Donasi</span>
            </div>
        </article>

        <article class="history-stat-card">
            <div class="history-stat-icon waiting">🕒</div>
            <div>
                <strong><?= e((string) $pendingCount) ?></strong>
                <span>Menunggu Verifikasi</span>
            </div>
        </article>

        <article class="history-stat-card">
            <div class="history-stat-icon success">✔</div>
            <div>
                <strong><?= e((string) $verifiedCount) ?></strong>
                <span>Donasi Berhasil</span>
            </div>
        </article>

        <article class="history-stat-card">
            <div class="history-stat-icon support">🤝</div>
            <div>
                <strong><?= e((string) count($programsSupported)) ?></strong>
                <span>Program Didukung</span>
            </div>
        </article>
    </section>

    <?php if (!$myDonations): ?>
        <div class="history-empty">
            <div class="history-empty-icon">💙</div>
            <h3>Belum ada riwayat donasi</h3>
            <p>Donasi yang Anda lakukan akan muncul di halaman ini.</p>
            <a href="index.php?route=app&page=program-donatur" class="btn green">Jelajahi Program</a>
        </div>
    <?php else: ?>
        <section class="history-table-card">
            <div class="history-table-head">
                <div>
                    <h3>Daftar Transaksi</h3>
                    <p>Semua riwayat donasi Anda ditampilkan secara ringkas dan mudah dibaca.</p>
                </div>
            </div>

            <div class="history-table-wrap">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>ID Donasi</th>
                            <th>Program</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myDonations as $d): ?>
                            <?php $amountValue = $parseAmount($d['amount'] ?? 0); ?>
                            <tr>
                                <td>
                                    <div class="history-id-cell">
                                        <strong>#<?= e($d['id'] ?? '-') ?></strong>
                                        <small>Transaksi Donasi</small>
                                    </div>
                                </td>

                                <td>
                                    <div class="history-program-cell">
                                        <span class="history-program-icon">❤</span>
                                        <div>
                                            <strong><?= e($d['program'] ?? '-') ?></strong>
                                            <small>Program bantuan SIPEDO</small>
                                        </div>
                                    </div>
                                </td>

                                <td class="history-amount">
                                    <?= e(formatRupiahFull($amountValue)) ?>
                                </td>

                                <td>
                                    <span class="history-method"><?= e($d['method'] ?? '-') ?></span>
                                </td>

                                <td class="history-date">
                                    <?= e($d['date'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= badge(strtolower((string)($d['status'] ?? 'pending'))) ?>
                                </td>

                                <td style="font-size:0.8rem;max-width:180px;">
                                    <?php
                                    $note = $d['note'] ?? '';
                                    $status = strtolower((string)($d['status'] ?? ''));
                                    if ($status === 'rejected' && $note !== ''):
                                    ?>
                                        <span style="color:#dc2626;font-weight:500;" title="<?= e($note) ?>">
                                            ⚠ <?= e(mb_strlen($note) > 50 ? mb_substr($note, 0, 50) . '…' : $note) ?>
                                        </span>
                                    <?php elseif ($status === 'rejected'): ?>
                                        <span style="color:#dc2626;font-size:0.78rem;">Tidak ada keterangan</span>
                                    <?php else: ?>
                                        <span style="color:#9ca3af;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</div>
