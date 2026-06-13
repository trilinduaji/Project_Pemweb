# Project_Pemweb
# SIPEDO — Sistem Informasi Pengelolaan Donasi
## Struktur MVC

```
sipedo_mvc/
├── index.php                  ← Entry point tunggal
├── .htaccess                  ← Konfigurasi Apache
├── routes/
│   └── web.php                ← Definisi semua route
├── core/
│   ├── App.php                ← Bootstrap & autoloader
│   ├── Router.php             ← Router sederhana
│   ├── View.php               ← View renderer
│   └── helpers.php            ← Fungsi helper global
├── config/
│   └── session.php            ← Konfigurasi session & data awal
├── app/
│   ├── Controllers/           ← CONTROLLER
│   │   ├── LandingController.php
│   │   ├── AuthController.php
│   │   ├── AppController.php
│   │   ├── DonationController.php
│   │   ├── ProgramController.php
│   │   ├── ProfileController.php
│   │   └── StaffController.php
│   ├── Models/                ← MODEL
│   │   ├── UserModel.php
│   │   ├── ProgramModel.php
│   │   ├── DonationModel.php
│   │   └── StaffModel.php
│   └── Views/                 ← VIEW
│       ├── landing/           ← Halaman publik (landing page)
│       ├── auth/              ← Login & register
│       ├── shared/            ← Layout bersama
│       ├── admin/             ← Halaman admin
│       ├── staff/             ← Halaman staff
│       └── donatur/           ← Halaman donatur
└── public/
    └── assets/
        ├── css/               ← Stylesheet
        └── uploads/           ← File upload (foto, bukti, program)
```

## Alur Navigasi
1. `index.php` → Landing Page (publik)
2. `index.php?route=auth/login` → Halaman Login
3. `index.php?route=app` → Dashboard (setelah login)
4. `index.php?route=app&page=<nama-halaman>` → Halaman spesifik

## Demo Akun
| Role    | Email        | Password |
|---------|-------------|----------|
| Admin   | admin@s.id  | 123      |
| Staff   | staff@s.id  | 123      |
| Donatur | don@s.id    | 123      |

## Cara Menjalankan
Jalankan dengan PHP built-in server dari root folder:
```
php -S localhost:8000
```
Lalu buka: http://localhost:8000
