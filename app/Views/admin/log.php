<div class="section-head">
    <h3 class="section-title">Log Aktivitas</h3>
</div>
<div class="panel table-wrap">
    <table>
        <thead><tr><th>#</th><th>Waktu</th><th>Pelaku</th><th>Role</th><th>Deskripsi</th><th>Referensi</th></tr></thead>
        <tbody>
            <?php foreach ($_SESSION['logs'] as $log): ?>
                <tr>
                    <td class="id"><?= e($log['no']) ?></td>
                    <td class="muted"><?= e($log['time']) ?></td>
                    <td><?= e($log['actor']) ?></td>
                    <td><?= e($log['role']) ?></td>
                    <td><?= e($log['desc']) ?></td>
                    <td class="id"><?= e($log['ref']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
