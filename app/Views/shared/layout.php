<?php $isInactiveStaff = ($role ?? '') === 'staff' && StaffModel::isActiveUser((int)($user['db_id'] ?? 0)) === false; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPEDO - <?= e($titles[$page] ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <?php if ($hasPageCss): ?>
        <link rel="stylesheet" href="public/assets/css/<?= e(basename($page)) ?>.css">
    <?php endif; ?>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <img src="public/assets/images/sipedo-logo.png" alt="SIPEDO" class="brand-logo">
            </div>

            <div class="user">
                <?= user_avatar($user) ?>
                <div>
                    <strong><?= e($user['name']) ?></strong>
                    <small><?= e(ucfirst($role)) ?></small>
                </div>
            </div>

            <?php foreach ($menus as $group => $items): ?>
                <div class="nav-title"><?= e($group) ?></div>
                <nav class="nav">
                    <?php foreach ($items as $key => $label): ?>
                        <a class="<?= is_active_page($page, $key) ?>"
                           href="index.php?route=app&page=<?= e($key) ?>"><?= e($label) ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endforeach; ?>

            <form class="logout" action="index.php?route=auth/logout" method="post">
                <button class="btn red full" type="submit">Keluar dari Akun</button>
            </form>
        </aside>

        <main class="main">
            <header class="topbar">
                <h2 class="page-title"><?= e($titles[$page] ?? 'Dashboard') ?></h2>
            </header>

            <section class="content">
                <?php show_flash(); ?>
                <?php if ($isInactiveStaff): ?>
                    <div class="flash flash-error">
                        Status staff kamu sedang nonaktif. Kamu hanya bisa melihat data dan tidak bisa melakukan aksi perubahan sampai admin mengaktifkan kembali akun staff kamu.
                    </div>
                <?php endif; ?>
                <?php include $pageFile; ?>
            </section>
        </main>
    </div>

<script>


(function() {
    const flashes = document.querySelectorAll('.flash, .alert, [class*="flash-"]');
    flashes.forEach(function(el) {
        el.style.transition = 'opacity 0.4s ease, transform 0.4s ease, max-height 0.4s ease';
        el.style.overflow = 'hidden';
        setTimeout(function() {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            el.style.maxHeight = '0';
            el.style.marginBottom = '0';
            el.style.padding = '0';
        }, 4000);
    });
})();


(function() {
    const active = document.querySelector('.nav a.active, .nav a[class~="active"]');
    if (active) {
        active.style.transition = 'box-shadow 0.3s ease';
        active.style.boxShadow = 'inset 3px 0 0 #0f1f3d';
    }
})();


(function() {
    document.querySelectorAll('.panel table tbody tr').forEach(function(row) {
        row.style.transition = 'background 0.18s ease';
        row.addEventListener('mouseenter', function() {
            if (!this.style.background || this.style.background === 'rgba(0, 0, 0, 0)') {
                this.dataset.origBg = this.style.background;
                this.style.background = '#f8faff';
            }
        });
        row.addEventListener('mouseleave', function() {
            if (this.dataset.origBg !== undefined) {
                this.style.background = this.dataset.origBg;
            }
        });
    });
})();


(function() {
    document.querySelectorAll('.btn').forEach(function(btn) {
        btn.style.position = 'relative';
        btn.style.overflow = 'hidden';
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.cssText = [
                'position:absolute',
                'border-radius:50%',
                'background:rgba(255,255,255,0.35)',
                'width:' + size + 'px',
                'height:' + size + 'px',
                'left:' + (e.clientX - rect.left - size/2) + 'px',
                'top:' + (e.clientY - rect.top  - size/2) + 'px',
                'transform:scale(0)',
                'animation:sipedo-ripple 0.55s linear',
                'pointer-events:none'
            ].join(';');
            this.appendChild(ripple);
            ripple.addEventListener('animationend', function() { ripple.remove(); });
        });
    });

    if (!document.getElementById('sipedo-ripple-style')) {
        const style = document.createElement('style');
        style.id = 'sipedo-ripple-style';
        style.textContent = '@keyframes sipedo-ripple{to{transform:scale(2.5);opacity:0}}';
        document.head.appendChild(style);
    }
})();


(function() {
    const cards = document.querySelectorAll('.card, .program-card-v2, .panel');
    if (!cards.length) return;
    const seen = new WeakSet();
    const obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting && !seen.has(entry.target)) {
                seen.add(entry.target);
                entry.target.style.animationPlayState = 'running';
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.06 });

    const style = document.createElement('style');
    style.textContent = [
        '.card,.program-card-v2,.panel{',
        '  animation: sipedo-card-in 0.4s ease both paused;',
        '}',
        '@keyframes sipedo-card-in{',
        '  from{opacity:0;transform:translateY(14px)}',
        '  to{opacity:1;transform:translateY(0)}',
        '}'
    ].join('');
    document.head.appendChild(style);
    cards.forEach(function(c, i) {
        c.style.animationDelay = (i * 0.05) + 's';
        obs.observe(c);
    });
})();


(function() {
    document.querySelectorAll('.progress span[style*="width"]').forEach(function(bar) {
        const target = bar.style.width;
        bar.style.width = '0';
        bar.style.transition = 'width 1s cubic-bezier(0.4,0,0.2,1)';
        setTimeout(function() { bar.style.width = target; }, 200);
    });
})();


(function() {
    const activeLink = document.querySelector('.nav a.active');
    if (activeLink) {
        activeLink.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
})();

// Nav click: beri efek "pressed" (biru lebih gelap sebentar) sebelum navigasi
(function() {
    document.querySelectorAll('.nav a').forEach(function(link) {
        link.addEventListener('mousedown', function() {
            this.style.background = 'rgba(37,99,235,0.20)';
        });
        link.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.background = '';
            }
        });
    });
})();


(function() {
    const topbar = document.querySelector('.topbar');
    const main   = document.querySelector('.main');
    if (!topbar || !main) return;
    topbar.style.transition = 'box-shadow 0.3s ease';
    main.addEventListener('scroll', function() {
        topbar.style.boxShadow = main.scrollTop > 8
            ? '0 2px 12px rgba(0,0,0,0.08)'
            : 'none';
    });
})();

<?php if ($isInactiveStaff): ?>
(function() {
    function showInactiveStaffWarning() {
        alert('Status staff kamu sedang nonaktif. Kamu hanya dapat melihat data dan tidak bisa melakukan aksi perubahan sampai admin mengaktifkan kembali akun staff kamu.');
    }

    document.querySelectorAll('form').forEach(function(form) {
        const method = (form.getAttribute('method') || 'get').toLowerCase();
        const action = form.getAttribute('action') || '';
        if (method !== 'post') return;
        if (action.indexOf('route=auth/logout') !== -1) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            showInactiveStaffWarning();
        });
    });
})();
<?php endif; ?>
</script>
</body>
</html>
