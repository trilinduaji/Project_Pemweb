<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPEDO - <?= $mode === 'register' ? 'Daftar' : 'Masuk' ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <style>

        .auth-page {
            display: flex;
            align-items: stretch;
            justify-content: flex-start;
            padding: 0;
            background: #f0f4f8;
            min-height: 100vh;
        }


        .auth-image-panel {
            flex: 1 1 0;
            min-height: 100vh;
            background: var(--navy);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
        }
        .auth-image-panel img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.82;
        }
        .auth-image-overlay {
            position: relative;
            z-index: 2;
            padding: 40px 36px;
            color: white;
        }
        .auth-image-overlay h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            line-height: 1.3;
            margin-bottom: 10px;
        }
        .auth-image-overlay p {
            font-family: 'Poppins', sans-serif;
            font-size: 0.82rem;
            opacity: 0.78;
            line-height: 1.6;
        }


        .auth-form-panel {
            width: 500px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            background: white;
            min-height: 100vh;
            box-shadow: -8px 0 40px rgba(0,0,0,0.08);
        }
        .auth-form-panel .auth-inner {
            width: 100%;
            max-width: 420px;
        }


        .auth-card {
            width: 100%;
            padding: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        .logo {
            margin-bottom: 24px;
            text-align: center;
        }
        .login-logo {
            width: 200px;
            height: auto;
            display: inline-block;
            object-fit: contain;
        }

        @media (max-width: 800px) {
            .auth-image-panel { display: none; }
            .auth-form-panel {
                width: 100%;
                padding: 32px 20px;
            }
        }
    </style>
</head>
<body class="auth-page">


    <div class="auth-image-panel">
        <img src="https://i.pinimg.com/736x/b6/60/c8/b660c89d43597f457b040f21f83ddda8.jpg"
             alt="Donasi bersama SIPEDO"
             onerror="this.parentElement.style.background='linear-gradient(135deg,#0f1f3d,#1e3a5f)'">
        <div class="auth-image-overlay">
            <h2>Bersama, kita<br>wujudkan perubahan.</h2>
            <p>SIPEDO — Sistem Pengelolaan Donasi<br>yang transparan dan terpercaya.</p>
        </div>
    </div>


    <div class="auth-form-panel">
        <div class="auth-inner">
            <main class="auth-card">
                <div class="logo">
                    <img src="public/assets/images/sipedo-logo.png" alt="SIPEDO" class="login-logo">
                </div>

                <?php show_flash(); ?>

                <div class="tabs">
                    <a class="tab <?= $mode === 'login'    ? 'active' : '' ?>"
                       href="index.php?route=auth/login&mode=login">Masuk</a>
                    <a class="tab <?= $mode === 'register' ? 'active' : '' ?>"
                       href="index.php?route=auth/login&mode=register">Daftar</a>
                </div>

                <?php if ($mode === 'register'): ?>
                    <form action="index.php?route=auth/register" method="post">
                        <input type="hidden" name="role" value="donatur">
                        <div class="field">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" placeholder="Nama lengkap" required>
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="email@gmail.com" required>
                        </div>
                        <div class="field">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Minimal 3 karakter" required>
                        </div>
                        <button class="btn full" type="submit">Buat Akun</button>
                        <p class="hint">Sudah punya akun?
                            <a href="index.php?route=auth/login&mode=login">Masuk</a>
                        </p>
                        <p class="hint" style="margin-top:8px;font-size:0.7rem;color:#9ca3af;">
                            Pendaftaran hanya untuk donatur. Akun staff didaftarkan oleh admin.
                        </p>
                    </form>
                <?php else: ?>
                    <form action="index.php?route=auth/login" method="post">
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="email@sipedo.org" required>
                        </div>
                        <div class="field">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                        <button class="btn full" type="submit">Masuk</button>
                        <p class="hint">Demo: admin@s.id / staff@s.id / don@s.id — pass: 123</p>
                        <p class="hint">Belum punya akun?
                            <a href="index.php?route=auth/login&mode=register">Daftar sekarang</a>
                        </p>
                    </form>
                <?php endif; ?>

                <p style="text-align:center;margin-top:1.5rem;font-size:.8rem;opacity:.5">
                    <a href="index.php" style="color:inherit">← Kembali ke Beranda</a>
                </p>
            </main>
        </div>
    </div>

</body>
</html>
