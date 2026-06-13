<?php $done = array_filter($_SESSION['donations'], fn($d) => $d['status'] !== 'pending'); ?>
<div class="section-head">
    <h3 class="section-title">Riwayat Verifikasi</h3>
</div>
<div class="panel table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Donatur</th><th>Jumlah</th><th>Program</th><th>Status</th><th>Catatan</th><th>Diproses Oleh</th></tr></thead>
        <tbody>
            <?php foreach ($done as $d): ?>
                <tr>
                    <td class="id">#<?= e($d['id']) ?></td>
                    <td><?= avatar($d['init'], $d['col']) ?><?= e($d['donor']) ?></td>
                    <td class="amount">Rp <?= e($d['amount']) ?></td>
                    <td><?= e($d['program']) ?></td>
                    <td><?= badge($d['status']) ?></td>
                    <td style="max-width:180px;font-size:0.78rem;">
                        <?php
                        $note   = $d['note']   ?? '';
                        $status = strtolower((string)($d['status'] ?? ''));
                        if ($note !== ''):
                        ?>
                            <span style="color:<?= $status === 'rejected' ? '#dc2626' : '#059669' ?>;font-weight:500;"
                                  title="<?= e($note) ?>">
                                <?= e(mb_strlen($note) > 45 ? mb_substr($note, 0, 45) . '…' : $note) ?>
                            </span>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($d['processedBy']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
