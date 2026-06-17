<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>SIPEDO - Satu Langkah Kecil untuk Perubahan yang Besar</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Libre+Caslon+Text:wght@400;700&amp;family=Manrope:wght@400;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    safelist: ["border-secondary","border-transparent","text-secondary","text-on-surface-variant","font-bold"],
    theme: {
      extend: {
        "colors": {
                "error-container": "#ffdad6",
                "surface-tint": "#465f86",
                "tertiary": "#070b0c",
                "primary-container": "#002144",
                "on-secondary-fixed": "#00210c",
                "secondary": "#006d36",
                "on-primary": "#ffffff",
                "primary-fixed": "#d5e3ff",
                "on-primary-fixed": "#001c3b",
                "surface-container": "#e5eeff",
                "surface": "#f8f9ff",
                "inverse-surface": "#213145",
                "background": "#f8f9ff",
                "tertiary-fixed-dim": "#c4c7c9",
                "secondary-fixed-dim": "#66dd8b",
                "on-tertiary": "#ffffff",
                "on-tertiary-container": "#85898b",
                "on-primary-fixed-variant": "#2d486c",
                "on-primary-container": "#7089b2",
                "outline": "#74777f",
                "outline-variant": "#c4c6cf",
                "tertiary-container": "#1e2223",
                "surface-dim": "#cbdbf5",
                "on-surface": "#0b1c30",
                "on-tertiary-fixed-variant": "#434749",
                "inverse-primary": "#aec8f3",
                "secondary-container": "#83fba5",
                "on-error": "#ffffff",
                "on-background": "#0b1c30",
                "on-secondary-fixed-variant": "#005227",
                "on-secondary-container": "#00743a",
                "on-error-container": "#93000a",
                "on-secondary": "#ffffff",
                "error": "#ba1a1a",
                "tertiary-fixed": "#e0e3e5",
                "on-surface-variant": "#43474e",
                "secondary-fixed": "#83fba5",
                "surface-container-low": "#eff4ff",
                "surface-variant": "#d3e4fe",
                "inverse-on-surface": "#eaf1ff",
                "on-tertiary-fixed": "#181c1e",
                "surface-container-lowest": "#ffffff",
                "surface-container-highest": "#d3e4fe",
                "surface-container-high": "#dce9ff",
                "primary": "#000a1b",
                "primary-fixed-dim": "#aec8f3",
                "surface-bright": "#f8f9ff"
        },
        "borderRadius": {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
        },
        "spacing": {
                "container-max": "1200px",
                "margin-mobile": "20px",
                "gutter": "24px",
                "base": "8px",
                "margin-desktop": "64px"
        },
        "fontFamily": {
                "body-md": [
                        "Manrope"
                ],
                "headline-md": [
                        "Libre Caslon Text"
                ],
                "display-lg": [
                        "Libre Caslon Text"
                ],
                "display-lg-mobile": [
                        "Libre Caslon Text"
                ],
                "label-sm": [
                        "Manrope"
                ],
                "body-lg": [
                        "Manrope"
                ]
        },
        "fontSize": {
                "body-md": [
                        "16px",
                        {
                                "lineHeight": "1.6",
                                "fontWeight": "400"
                        }
                ],
                "headline-md": [
                        "24px",
                        {
                                "lineHeight": "1.4",
                                "fontWeight": "600"
                        }
                ],
                "display-lg": [
                        "48px",
                        {
                                "lineHeight": "1.2",
                                "letterSpacing": "-0.02em",
                                "fontWeight": "700"
                        }
                ],
                "display-lg-mobile": [
                        "32px",
                        {
                                "lineHeight": "1.2",
                                "fontWeight": "700"
                        }
                ],
                "label-sm": [
                        "12px",
                        {
                                "lineHeight": "1",
                                "letterSpacing": "0.05em",
                                "fontWeight": "700"
                        }
                ],
                "body-lg": [
                        "18px",
                        {
                                "lineHeight": "1.6",
                                "fontWeight": "400"
                        }
                ]
        }
},
    },
  }
</script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined[data-weight="fill"] {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen flex flex-col pt-[88px]">

<nav class="bg-surface dark:bg-primary docked full-width top-0 bg-surface/90 backdrop-blur-md shadow-sm dark:shadow-none transition-all duration-300 fixed top-0 left-0 w-full z-50 flex justify-between items-center px-margin-mobile md:px-margin-desktop py-4 mx-auto border-b border-outline-variant/10">
<div class="flex justify-between items-center w-full max-w-container-max mx-auto">
<a class="flex items-center" href="#beranda">
  <img src="public/assets/images/sipedo-logo.png" alt="SIPEDO" style="height:40px;width:auto;object-fit:contain;">
</a>
<div id="mainNavMenu" class="hidden md:flex space-x-10 items-center font-body-md text-body-md">
<a class="nav-link pb-1 border-b-2 border-transparent text-on-surface-variant dark:text-outline-variant hover:text-secondary transition-colors duration-200" href="#program" data-section="program">Program</a>
<a class="nav-link pb-1 border-b-2 border-transparent text-on-surface-variant dark:text-outline-variant hover:text-secondary transition-colors duration-200" href="#statistik" data-section="statistik">Statistik</a>
<a class="nav-link pb-1 border-b-2 border-transparent text-on-surface-variant dark:text-outline-variant hover:text-secondary transition-colors duration-200" href="#leaderboard" data-section="leaderboard">Leaderboard</a>
<a class="nav-link pb-1 border-b-2 border-transparent text-on-surface-variant dark:text-outline-variant hover:text-secondary transition-colors duration-200" href="#tentang" data-section="tentang">Tentang Kami</a>
</div>
<a href="index.php?route=auth/login" class="bg-secondary text-on-secondary px-8 py-3 rounded-full font-label-sm hover:scale-105 transition-transform shadow-lg hidden md:block">Mulai Berdonasi</a>
<button type="button" id="mobileMenuBtn" class="md:hidden text-on-surface p-2 bg-surface-container rounded-full" aria-label="Buka menu"><span class="material-symbols-outlined">menu</span></button>
</div>
</nav>

<main class="flex-grow">

<section id="beranda" class="relative bg-primary-container text-on-primary overflow-hidden">
<div class="absolute inset-0 z-0">
<img alt="Hero Background" class="w-full h-full object-cover" data-alt="A heartwarming, high-quality photograph of a volunteer smiling while helping a young child in a rural setting, soft natural lighting, emotional and hopeful atmosphere, professional photography, shallow depth of field." src="public/assets/images/hero.jpg"/>
<div class="absolute inset-0 bg-gradient-to-r from-primary-container/90 via-primary-container/70 to-transparent"></div>
</div>
<div class="relative z-10 max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-24 md:py-32 flex flex-col items-start justify-center min-h-[600px]">
<div class="max-w-3xl backdrop-blur-md bg-primary-container/30 p-8 md:p-12 rounded-3xl border border-white/10 shadow-2xl">
<div class="mb-7">
  <img src="public/assets/images/sipedo-logo.png" alt="SIPEDO" style="height:52px;width:auto;object-fit:contain;filter:brightness(0) invert(1);opacity:0.92;">
</div>
<h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-on-primary mb-6 leading-tight drop-shadow-md">Satu Langkah Kecil untuk Perubahan yang Besar</h1>
<p class="font-body-lg text-body-lg text-primary-fixed-dim mb-10 max-w-2xl drop-shadow-sm">SIPEDO menghubungkan Anda dengan program amal terverifikasi. Transparan, aman, dan berdampak nyata bagi mereka yang membutuhkan.</p>
<div class="flex flex-col sm:flex-row gap-4">
<a href="index.php?route=auth/login" class="inline-flex items-center justify-center bg-secondary text-on-secondary px-10 py-4 rounded-full font-label-sm text-[16px] hover:scale-105 transition-transform shadow-[0_10px_30px_rgba(0,109,54,0.4)]">Mulai Berdonasi</a>
<a href="#program" class="inline-flex items-center justify-center bg-white/10 text-on-primary px-10 py-4 rounded-full font-label-sm text-[16px] hover:bg-white/20 transition-colors border border-white/20 backdrop-blur-sm">Pelajari Lebih Lanjut</a>
</div>
</div>
</div>
</section>

<section id="statistik" class="py-16 bg-surface-container-lowest -mt-16 relative z-20 mx-margin-mobile md:mx-auto max-w-container-max rounded-2xl shadow-[0_8px_30px_rgba(0,33,68,0.08)] border border-outline-variant/10">
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 px-8 md:px-12 text-center divide-y md:divide-y-0 md:divide-x divide-outline-variant/20">
<div class="py-4 md:py-0 flex flex-col items-center justify-center">
<div class="font-display-lg text-[40px] md:text-[48px] font-bold text-secondary mb-2 drop-shadow-sm"><?= e((string)($donaturUnik ?? 0)) ?></div>
<div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Total Donatur</div>
</div>
<div class="py-4 md:py-0 flex flex-col items-center justify-center">
<div class="font-display-lg text-[40px] md:text-[48px] font-bold text-primary mb-2 drop-shadow-sm"><?= e((string)($totalProgram ?? 0)) ?></div>
<div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Program Aktif</div>
</div>
<div class="py-4 md:py-0 flex flex-col items-center justify-center">
<div class="font-display-lg text-[40px] md:text-[48px] font-bold text-secondary mb-2 drop-shadow-sm"><?= e(formatRupiahLP((int)($totalCollected ?? 0))) ?></div>
<div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Donasi Terverifikasi</div>
</div>
</div>
</section>

<section id="program" class="py-24 max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="text-center mb-16 max-w-3xl mx-auto">
<h2 class="font-headline-md text-[32px] md:text-[40px] text-primary mb-4 drop-shadow-sm">Pilih Program yang Menyentuh Hatimu</h2>
<p class="font-body-lg text-body-lg text-on-surface-variant">Setiap donasi Anda disalurkan langsung kepada penerima manfaat secara transparan dengan laporan berkala.</p>
</div>
<?php
$landingPrograms = array_values($displayPrograms ?? []);
?>
<?php if (empty($landingPrograms)): ?>
<div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-8 text-center text-on-surface-variant">Belum ada program aktif yang tersedia saat ini.</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<?php foreach ($landingPrograms as $program): ?>
<?php
$programId = $program['id'] ?? '';
$programName = $program['name'] ?? 'Program Donasi';
$programCat = $program['cat'] ?? 'Program';
$programDesc = $program['desc'] ?? 'Program bantuan SIPEDO yang membutuhkan dukungan donatur.';
$programImage = sipedo_program_image($program);
$programCollected = ((float)($program['collected'] ?? 0)) * 1000000;
$programTarget = ((float)($program['target'] ?? 0)) * 1000000;
$programPct = min(100, (float)($program['pct'] ?? 0));
$programFallbackGradient = $program['gradient'] ?? 'linear-gradient(135deg,#0D1B3E,#2A4080)';
?>
<article class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,33,68,0.06)] border border-outline-variant/10 flex flex-col transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(0,33,68,0.12)] group">
<div class="relative h-56 overflow-hidden" <?= $programImage === '' ? 'style="background:' . e($programFallbackGradient) . ';"' : '' ?>>
<?php if ($programImage !== ''): ?>
<img alt="<?= e($programName) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" src="<?= e(pub($programImage)) ?>"/>
<?php endif; ?>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-60"></div>
<div class="absolute top-4 left-4 bg-primary/90 backdrop-blur-sm text-on-primary px-3 py-1 rounded-full font-label-sm text-[11px] uppercase tracking-wider shadow-sm"><?= e($programCat) ?></div>
</div>
<div class="p-6 flex flex-col flex-grow">
<h3 class="font-headline-md text-[20px] md:text-[22px] font-bold text-primary mb-2 line-clamp-2 group-hover:text-secondary transition-colors"><?= e($programName) ?></h3>
<p class="font-body-md text-body-md text-on-surface-variant mb-6 line-clamp-3 flex-grow"><?= e($programDesc) ?></p>
<div class="space-y-2 mb-6">
<div class="flex justify-between font-label-sm text-label-sm items-end gap-3">
<span class="text-secondary font-bold text-[16px]"><?= e(formatRupiahFull((int)$programCollected)) ?></span>
<span class="text-on-surface-variant text-[12px]">dari <?= e(formatRupiahFull((int)$programTarget)) ?></span>
</div>
<div class="w-full bg-surface-container-highest rounded-full h-2 overflow-hidden shadow-inner">
<div class="bg-secondary h-full rounded-full transition-all duration-1000 ease-out" style="width: <?= e($programPct) ?>%"></div>
</div>
</div>
<a href="index.php?route=auth/login" class="block text-center w-full bg-surface text-primary border border-outline-variant/30 hover:bg-secondary hover:text-on-secondary hover:border-secondary px-6 py-3 rounded-xl font-label-sm text-[14px] transition-all duration-300 shadow-sm hover:shadow-md mt-auto">Donasi Sekarang</a>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<div class="mt-12 text-center">
<a href="index.php?route=auth/login" class="inline-flex text-primary font-label-sm text-[16px] px-8 py-3 rounded-full border-2 border-primary hover:bg-primary hover:text-on-primary transition-colors shadow-sm">Lihat Semua Program</a>
</div>
</section>


<section id="leaderboard" class="py-24 bg-surface border-t border-outline-variant/10">
<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
  <div class="text-center mb-16 max-w-3xl mx-auto">
    <h2 class="font-headline-md text-[32px] md:text-[40px] text-primary mb-4">Donatur Terbaik SIPEDO</h2>
    <p class="font-body-lg text-body-lg text-on-surface-variant">Terima kasih atas kebaikan Anda yang tiada henti menginspirasi banyak orang untuk turut berbagi.</p>
  </div>

  <?php
    $leaderboard = $topDonors ?? [];
    if (empty($leaderboard)) {
        $leaderboard = [
            ['nama' => 'Budi Santoso', 'initials' => 'BS', 'total' => 12500000, 'count' => 8],
            ['nama' => 'Ahmad Fauzi', 'initials' => 'AF', 'total' => 25400000, 'count' => 12],
            ['nama' => 'Siti Rahma', 'initials' => 'SR', 'total' => 8200000, 'count' => 6],
        ];
    }
    $leaderboard = array_slice(array_values($leaderboard), 0, 3);
    while (count($leaderboard) < 3) {
        $leaderboard[] = ['nama' => 'Donatur', 'initials' => 'DN', 'total' => 0, 'count' => 0];
    }
    $center    = $leaderboard[0]; // rank 1 → posisi tengah (paling besar/menonjol)
    $sideLeft  = $leaderboard[1]; // rank 2 → posisi kiri
    $sideRight = $leaderboard[2]; // rank 3 → posisi kanan

    $formatInit = function($d) {
        $in = trim((string)($d['initials'] ?? ''));
        if ($in === '') {
            $nama = strtoupper(trim((string)($d['nama'] ?? 'DN')));
            $parts = preg_split('/\s+/', $nama);
            $in = '';
            foreach ($parts as $p) { if ($p !== '') $in .= substr($p,0,1); if (strlen($in) >= 2) break; }
        }
        return substr(strtoupper($in ?: 'DN'), 0, 2);
    };
  ?>

  <div class="relative max-w-5xl mx-auto">
    <div class="absolute inset-x-10 top-10 h-64 bg-[radial-gradient(circle_at_center,rgba(0,109,54,0.10),transparent_65%)] pointer-events-none"></div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-6 items-end">

      <?php
        $cards = [
          ['item' => $sideLeft, 'rank' => 2, 'order' => 'md:order-1', 'scale' => 'md:mt-16', 'tone' => 'bg-white', 'avatar' => 'from-[#dce8ff] to-[#bfd4ff]', 'bar' => 'w-[74%] bg-primary', 'iconBg' => 'bg-[#dfe8f8]', 'iconColor' => 'text-primary'],
          ['item' => $center, 'rank' => 1, 'order' => 'md:order-2', 'scale' => 'md:-mt-4', 'tone' => 'bg-gradient-to-b from-[#ffffff] to-[#f7fbff]', 'avatar' => 'from-[#d8f4e6] to-[#8fd7ae]', 'bar' => 'w-full bg-secondary', 'iconBg' => 'bg-[#e3f7ec]', 'iconColor' => 'text-secondary'],
          ['item' => $sideRight, 'rank' => 3, 'order' => 'md:order-3', 'scale' => 'md:mt-16', 'tone' => 'bg-white', 'avatar' => 'from-[#ffecc8] to-[#ffd28b]', 'bar' => 'w-[58%] bg-[#d39a2f]', 'iconBg' => 'bg-[#fff3da]', 'iconColor' => 'text-[#b7791f]'],
        ];
      ?>

      <?php foreach ($cards as $cfg):
        $d = $cfg['item'];
        $rank = $cfg['rank'];
        $initials = $formatInit($d);
        $name = e($d['nama'] ?? 'Donatur');
        $count = (int)($d['count'] ?? 0);
        $total = (int)($d['total'] ?? 0);
        $trophy = $rank === 1 ? 'emoji_events' : 'military_tech';
      ?>
      <article class="<?= $cfg['order'] ?> <?= $cfg['scale'] ?> relative rounded-[32px] border border-outline-variant/10 <?= $cfg['tone'] ?> shadow-[0_18px_50px_rgba(0,33,68,0.10)] overflow-hidden transition-all duration-300 hover:-translate-y-2">
        <div class="absolute top-0 inset-x-0 h-1.5 <?= $rank === 1 ? 'bg-secondary' : ($rank === 2 ? 'bg-primary' : 'bg-[#d39a2f]') ?>"></div>
        <div class="px-8 pt-8 pb-7 text-center">
          <div class="mx-auto mb-5 w-16 h-16 rounded-full <?= $cfg['iconBg'] ?> flex items-center justify-center shadow-sm">
            <span class="material-symbols-outlined <?= $cfg['iconColor'] ?> text-[30px]"><?= $trophy ?></span>
          </div>

          <div class="mx-auto mb-5 <?= $rank === 1 ? 'w-32 h-32 text-[42px]' : 'w-24 h-24 text-[30px]' ?> rounded-full bg-gradient-to-br <?= $cfg['avatar'] ?> flex items-center justify-center text-primary font-black shadow-inner border border-white/80">
            <?= e($initials) ?>
          </div>

          <p class="text-[11px] uppercase tracking-[0.22em] text-on-surface-variant mb-2">Peringkat <?= $rank ?></p>
          <h3 class="<?= $rank === 1 ? 'text-[28px]' : 'text-[23px]' ?> font-bold text-primary leading-tight mb-2"><?= $name ?></h3>
          <p class="text-on-surface-variant text-[14px] mb-6"><?= $count ?> kali berdonasi</p>

          <div class="rounded-2xl bg-surface-container border border-outline-variant/10 px-5 py-4 mb-5">
            <p class="text-[11px] uppercase tracking-[0.18em] text-on-surface-variant mb-1">Total Donasi</p>
            <p class="<?= $rank === 1 ? 'text-[30px]' : 'text-[24px]' ?> font-extrabold <?= $rank === 1 ? 'text-secondary' : 'text-primary' ?>"><?= formatRupiahFull($total) ?></p>
          </div>

          <div class="w-full bg-surface-container-highest rounded-full h-2.5 overflow-hidden shadow-inner mb-1">
            <div class="h-full rounded-full <?= $cfg['bar'] ?>"></div>
          </div>
          <p class="text-[12px] text-on-surface-variant mt-3">Kontributor inspiratif SIPEDO</p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</section>
</main>


<footer id="tentang" class="bg-[#001c3b] text-white/70 font-body-md text-body-md py-16">
<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">

<div class="space-y-6">
<div class="flex items-center gap-2">
<img src="public/assets/images/sipedo-logo.png" alt="SIPEDO" style="height:38px;width:auto;object-fit:contain;filter:brightness(0) invert(1);opacity:0.9;">
</div>
<p class="text-white/60 leading-relaxed">Platform donasi digital yang transparan dan terpercaya. Setiap rupiah yang Anda berikan tercatat dan berdampak nyata.</p>
<div class="flex gap-4 pt-2">
<a class="w-10 h-10 flex items-center justify-center rounded-full border border-white/20 hover:bg-white/10 transition-colors" href="#tentang">
<svg class="w-5 h-5 fill-current" viewbox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.584.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c.796 0 1.441.645 1.441 1.44s-.645 1.44-1.441 1.44c-.795 0-1.44-.645-1.44-1.44s.645-1.44 1.44-1.44z"></path></svg>
</a>
<a class="w-10 h-10 flex items-center justify-center rounded-full border border-white/20 hover:bg-white/10 transition-colors" href="#tentang">
<svg class="w-5 h-5 fill-current" viewbox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>
</a>
<a class="w-10 h-10 flex items-center justify-center rounded-full border border-white/20 hover:bg-white/10 transition-colors" href="#tentang">
<svg class="w-5 h-5 fill-current" viewbox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"></path></svg>
</a>
</div>
</div>

<div>
<h5 class="text-white font-bold uppercase tracking-widest text-sm mb-8">Navigasi</h5>
<ul class="space-y-4">
<li class=""><a class="hover:text-white transition-colors" href="#beranda">Beranda</a></li>
<li class=""><a class="hover:text-white transition-colors" href="#program">Program Charity</a></li>
<li class=""><a class="hover:text-white transition-colors" href="#leaderboard">Leaderboard</a></li>
<li class=""><a class="hover:text-white transition-colors" href="index.php?route=auth/login">Masuk / Daftar</a></li>
</ul>
</div>

<div>
<h5 class="text-white font-bold uppercase tracking-widest text-sm mb-8">Dukungan</h5>
<ul class="space-y-4">
<li class=""><a class="hover:text-white transition-colors" href="#tentang">FAQ</a></li>
<li class=""><a class="hover:text-white transition-colors" href="#program">Cara Berdonasi</a></li>
<li class=""><a class="hover:text-white transition-colors" href="#statistik">Laporan Keuangan</a></li>
<li class=""><a class="hover:text-white transition-colors" href="#program">Verifikasi Program</a></li>
</ul>
</div>

<div>
<h5 class="text-white font-bold uppercase tracking-widest text-sm mb-8">Kontak</h5>
<p class="text-white/55 leading-relaxed mb-6">
Jika Anda ingin mendaftar menjadi staff, hubungi email di bawah ini.
</p>
<ul class="space-y-6">
<li class="flex gap-3 items-center">
<span class="material-symbols-outlined text-white/40">badge</span>
<a class="hover:text-white transition-colors font-semibold text-white/80" href="mailto:sipedo@gmail.com">sipedo@gmail.com</a>
</li>
<li class="flex gap-3">
<span class="material-symbols-outlined text-white/40">location_on</span>
<span class="leading-relaxed">Jl. Sudirman No. 88, Jakarta Pusat<br/>DKI Jakarta 10220</span>
</li>
<li class="flex gap-3 items-center">
<span class="material-symbols-outlined text-white/40">mail</span>
<a class="hover:text-white transition-colors" href="mailto:halo@sipedo.id">halo@sipedo.id</a>
</li>
<li class="flex gap-3 items-center">
<span class="material-symbols-outlined text-white/40">call</span>
<a class="hover:text-white transition-colors" href="tel:+622155550123">+62 21 5555 0123</a>
</li>
</ul>
</div>
</div>

<div class="pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
<div class="text-white/40">© 2026 <span class="text-white font-bold">SIPEDO</span>. Sistem Informasi Pengelolaan Donasi. Hak Cipta Dilindungi Undang-Undang.</div>
</div>
</div>

<button type="button" onclick="window.scrollTo({top:0, behavior:'smooth'})" class="fixed bottom-8 right-8 bg-[#002144] text-white p-4 rounded-full shadow-2xl hover:bg-primary transition-all z-[60] border border-white/10 group" aria-label="Kembali ke atas"><span class="material-symbols-outlined group-hover:-translate-y-1 transition-transform">arrow_upward</span></button>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // Mobile menu toggle
  const btn = document.getElementById('mobileMenuBtn');
  const menu = document.getElementById('mainNavMenu');
  if (btn && menu) {
    btn.addEventListener('click', function () {
      menu.classList.toggle('hidden');
      menu.classList.toggle('flex');
      menu.classList.toggle('flex-col');
      menu.classList.toggle('absolute');
      menu.classList.toggle('top-full');
      menu.classList.toggle('left-0');
      menu.classList.toggle('w-full');
      menu.classList.toggle('bg-surface');
      menu.classList.toggle('p-6');
      menu.classList.toggle('shadow-lg');
      menu.classList.toggle('space-x-10');
      menu.classList.toggle('space-y-4');
    });
  }

  // Scroll-spy: garis bawah berpindah sesuai section aktif
  const navLinks = document.querySelectorAll('.nav-link[data-section]');

  // Map setiap section ke nav-link yang sesuai (urutan sesuai posisi HTML)
  const sectionNavMap = [
    { sectionId: 'statistik', navSection: 'statistik' },
    { sectionId: 'program',   navSection: 'program'   },
    { sectionId: 'leaderboard', navSection: 'leaderboard' },
    { sectionId: 'tentang',   navSection: 'tentang'   },
  ];
  const sections = sectionNavMap
    .map(s => ({ el: document.getElementById(s.sectionId), navSection: s.navSection }))
    .filter(s => s.el);

  function setActive(activeNavSection) {
    navLinks.forEach(function (link) {
      const isActive = link.dataset.section === activeNavSection;
      link.classList.toggle('border-secondary', isActive);
      link.classList.toggle('border-transparent', !isActive);
      link.classList.toggle('text-secondary', isActive);
      link.classList.toggle('font-bold', isActive);
      link.classList.toggle('text-on-surface-variant', !isActive);
    });
  }

  let scrollLock = false;

  function onScroll() {
    if (scrollLock) return;
    const scrollY = window.scrollY + 150;
    let current = 'program';
    sections.forEach(function (s) {
      if (s.el.offsetTop <= scrollY) current = s.navSection;
    });
    setActive(current);
  }

  // Klik nav → smooth scroll ke section + langsung set aktif
  navLinks.forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.getElementById(link.dataset.section);
      if (target) {
        scrollLock = true;
        setActive(link.dataset.section);
        target.scrollIntoView({ behavior: 'smooth' });
        setTimeout(function () { scrollLock = false; }, 1000);
      }
    });
  });

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
});
</script>

</body></html>
