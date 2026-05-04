<?php
$user = current_user();
$programs = array_values(array_filter($_SESSION['programs'], fn($p) => ($p['status'] ?? '') === 'active'));
$myDonations = array_values(array_filter($_SESSION['donations'], fn($d) => $d['donor'] === $user['name']));

$totalDonation = 0;
$supportedPrograms = [];
foreach ($myDonations as $donation) {
    $totalDonation += (int) str_replace('.', '', $donation['amount']);
    $supportedPrograms[$donation['progId']] = true;
}

$allDonorTotals = [];
foreach ($_SESSION['donations'] as $donation) {
    $amount = (int) str_replace('.', '', $donation['amount']);
    if (!isset($allDonorTotals[$donation['donor']])) {
        $allDonorTotals[$donation['donor']] = [
            'name' => $donation['donor'],
            'init' => $donation['init'],
            'col' => $donation['col'],
            'total' => 0,
        ];
    }
    $allDonorTotals[$donation['donor']]['total'] += $amount;
}
usort($allDonorTotals, fn($a, $b) => $b['total'] <=> $a['total']);

$myRank = '—';
foreach ($allDonorTotals as $idx => $donor) {
    if ($donor['name'] === $user['name']) {
        $myRank = '#' . ($idx + 1);
        break;
    }
}

$totalCollected = array_sum(array_map(fn($p) => (float) $p['collected'], $_SESSION['programs']));
$totalDonors = count(array_unique(array_map(fn($d) => $d['donor'], $_SESSION['donations'])));
$categories = array_values(array_unique(array_map(fn($p) => $p['cat'], $programs)));

function rupiah_dashboard($value) {
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function rupiah_short_dashboard($value) {
    if ($value >= 1000000) return 'Rp ' . rtrim(rtrim(number_format($value / 1000000, 1, ',', '.'), '0'), ',') . 'Jt';
    if ($value >= 1000) return 'Rp ' . rtrim(rtrim(number_format($value / 1000, 1, ',', '.'), '0'), ',') . 'K';
    return 'Rp ' . number_format($value, 0, ',', '.');
}

function program_icon_dashboard($category) {
    $icons = [
        'Pendidikan' => '📚',
        'Kesehatan' => '🏥',
        'Sosial' => '🤝',
        'Kedaruratan' => '🏘️',
        'Bencana Alam' => '🏘️',
        'Lingkungan' => '🌱',
        'Pangan' => '🍚',
        'Keagamaan' => '🕌',
        'Infrastruktur' => '🏗️',
    ];
    return $icons[$category] ?? '💚';
}

function program_gradient_dashboard($idx) {
    $gradients = [
        'linear-gradient(135deg, #1A3A5C 0%, #2A5A8C 100%)',
        'linear-gradient(135deg, #0F4C3A 0%, #1A7A5E 100%)',
        'linear-gradient(135deg, #7C1F1F 0%, #C0392B 100%)',
        'linear-gradient(135deg, #4A1D5C 0%, #7B3FAD 100%)',
        'linear-gradient(135deg, #1A4A2E 0%, #2D8C52 100%)',
    ];
    return $gradients[$idx % count($gradients)];
}
?>

<div class="donor-landing" id="donorLanding">
    <section class="dl-hero">
        <div class="dl-hero-content">
            <p class="dl-eyebrow">Dashboard Donatur</p>
            <h1>Pilih Program,<br>Ubah <em>Kehidupan</em></h1>
            <p class="dl-hero-copy">Setiap donasi Anda berdampak nyata. Temukan program bantuan yang sesuai dengan hati nurani Anda dan pantau kontribusi kebaikan Anda di SIPEDO.</p>
            <div class="dl-search-row">
                <input type="search" id="programSearch" class="dl-search" placeholder="Cari program bantuan..." autocomplete="off">
                <button type="button" class="dl-search-btn" id="resetProgramFilter">Reset</button>
            </div>
        </div>
        <div class="dl-hero-stats">
            <div class="dl-hero-stat">
                <strong><?= count($programs) ?></strong>
                <span>Program Aktif</span>
            </div>
            <div class="dl-hero-stat">
                <strong><?= e($totalDonors) ?></strong>
                <span>Donatur</span>
            </div>
            <div class="dl-hero-stat">
                <strong><?= e(number_format($totalCollected, 1, ',', '.')) ?>Jt</strong>
                <span>Dana Terkumpul</span>
            </div>
        </div>
    </section>

    <div class="dl-main-grid">
        <div class="dl-programs-column">
            <div class="dl-section-head">
                <div>
                    <h2>Program Bantuan</h2>
                    <p>Menampilkan program aktif yang bisa Anda dukung hari ini.</p>
                </div>
                <a href="app.php">Lihat Semua →</a>
            </div>

            <div class="dl-filter-tabs" id="categoryFilters">
                <button type="button" class="dl-filter active" data-category="all">Semua</button>
                <?php foreach ($categories as $category): ?>
                    <button type="button" class="dl-filter" data-category="<?= e($category) ?>"><?= e($category) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="dl-program-grid" id="programGrid">
                <?php foreach (array_slice($programs, 0, 6) as $idx => $p): ?>
                    <?php
                        $raised = ((float) $p['collected']) * 1000000;
                        $target = ((float) $p['target']) * 1000000;
                        $isUrgent = $p['pct'] >= 90 || stripos($p['cat'], 'darurat') !== false || stripos($p['cat'], 'bencana') !== false;
                    ?>
                    <a class="dl-program-card" href="app.php" data-title="<?= e(strtolower($p['name'])) ?>" data-category="<?= e($p['cat']) ?>">
                        <div class="dl-card-image" style="background: <?= !empty($p['image']) ? 'url(\'' . e($p['image']) . '\') center/cover' : e(program_gradient_dashboard($idx)) ?>;">
                            <?php if (empty($p['image'])): ?>
                                <span class="dl-card-emoji"><?= e(program_icon_dashboard($p['cat'])) ?></span>
                            <?php endif; ?>
                            <span class="dl-card-category"><?= e($p['cat']) ?></span>
                            <?php if ($isUrgent): ?><span class="dl-card-urgent">⚡ Mendesak</span><?php endif; ?>
                        </div>
                        <div class="dl-card-body">
                            <h3><?= e($p['name']) ?></h3>
                            <p class="dl-card-desc"><?= e($p['desc'] ?: 'Program ' . strtolower($p['cat']) . ' dengan deadline ' . $p['deadline'] . '. Bantu program ini mencapai target donasi.') ?></p>
                            <div class="dl-progress-meta">
                                <strong><?= e(rupiah_dashboard($raised)) ?></strong>
                                <span><?= e($p['pct']) ?>%</span>
                            </div>
                            <div class="dl-progress-track"><span style="width: <?= e(min(100, $p['pct'])) ?>%"></span></div>
                            <p class="dl-progress-goal">Target: <?= e(rupiah_dashboard($target)) ?> · Deadline <?= e($p['deadline']) ?></p>
                            <span class="dl-detail-cta">Lihat Detail &amp; Donasi →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <p class="dl-empty-state" id="emptyProgramState">Program tidak ditemukan. Coba kata kunci atau kategori lain.</p>
        </div>

        <aside class="dl-side-column">
            <section class="dl-widget">
                <div class="dl-widget-head">
                    <span>〽</span>
                    <div>
                        <h3>Donasi Saya</h3>
                        <p>Ringkasan aktivitas</p>
                    </div>
                </div>
                <div class="dl-quick-stats">
                    <div><strong><?= e(rupiah_short_dashboard($totalDonation)) ?></strong><span>Total Donasi</span></div>
                    <div><strong><?= count($myDonations) ?></strong><span>Kali Transaksi</span></div>
                    <div><strong><?= count($supportedPrograms) ?></strong><span>Program Didukung</span></div>
                    <div><strong><?= e($myRank) ?></strong><span>Peringkat Donatur</span></div>
                </div>
            </section>

            <section class="dl-widget">
                <div class="dl-widget-head">
                    <span>🏆</span>
                    <div>
                        <h3>Ranking Charity</h3>
                        <p>Top donatur saat ini</p>
                    </div>
                </div>
                <div class="dl-leaderboard">
                    <?php foreach (array_slice($allDonorTotals, 0, 5) as $idx => $donor): ?>
                        <div class="dl-rank-item <?= $donor['name'] === $user['name'] ? 'is-current' : '' ?>">
                            <b><?= $idx === 0 ? '🥇' : ($idx === 1 ? '🥈' : ($idx === 2 ? '🥉' : $idx + 1)) ?></b>
                            <?= avatar($donor['init'], $donor['col']) ?>
                            <div>
                                <strong><?= e($donor['name']) ?><?= $donor['name'] === $user['name'] ? ' (Anda)' : '' ?></strong>
                                <span><?= e(rupiah_dashboard($donor['total'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="dl-cta-widget">
                <h3>Tingkatkan Dampak Anda</h3>
                <p>Mulai dari nominal kecil, kontribusi rutin Anda bisa membantu lebih banyak penerima manfaat.</p>
                <a href="app.php">Jelajahi Semua Program</a>
            </section>
        </aside>
    </div>
</div>

<script>
(function () {
    const searchInput = document.getElementById('programSearch');
    const resetButton = document.getElementById('resetProgramFilter');
    const filters = document.querySelectorAll('.dl-filter');
    const cards = document.querySelectorAll('.dl-program-card');
    const emptyState = document.getElementById('emptyProgramState');
    let activeCategory = 'all';

    function applyFilter() {
        const keyword = (searchInput.value || '').trim().toLowerCase();
        let visible = 0;

        cards.forEach(function (card) {
            const title = card.dataset.title || '';
            const category = card.dataset.category || '';
            const matchKeyword = !keyword || title.includes(keyword) || category.toLowerCase().includes(keyword);
            const matchCategory = activeCategory === 'all' || category === activeCategory;
            const shouldShow = matchKeyword && matchCategory;

            card.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visible += 1;
        });

        emptyState.style.display = visible ? 'none' : 'block';
    }

    filters.forEach(function (button) {
        button.addEventListener('click', function () {
            filters.forEach(function (item) { item.classList.remove('active'); });
            button.classList.add('active');
            activeCategory = button.dataset.category;
            applyFilter();
        });
    });

    searchInput.addEventListener('input', applyFilter);
    resetButton.addEventListener('click', function () {
        searchInput.value = '';
        activeCategory = 'all';
        filters.forEach(function (item) { item.classList.toggle('active', item.dataset.category === 'all'); });
        applyFilter();
        searchInput.focus();
    });
})();
</script>
