


SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


CREATE DATABASE IF NOT EXISTS `sipedo`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sipedo`;


CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)     NOT NULL,
  `email`      VARCHAR(150)     NOT NULL,
  `password`   VARCHAR(255)     NOT NULL COMMENT 'bcrypt hash',
  `role`       ENUM('admin','staff','donatur') NOT NULL DEFAULT 'donatur',
  `initials`   CHAR(3)          NOT NULL DEFAULT '',
  `color`      CHAR(7)          NOT NULL DEFAULT '#059669' COMMENT 'HEX warna avatar',
  `photo`      VARCHAR(255)     NOT NULL DEFAULT '' COMMENT 'path relatif dari public/',
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Semua akun pengguna SIPEDO';


CREATE TABLE IF NOT EXISTS `programs` (
  `id`          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  `kode`        VARCHAR(10)       NOT NULL COMMENT 'PR-01, PR-02, dst.',
  `name`        VARCHAR(150)      NOT NULL,
  `description` TEXT,
  `category`    ENUM(
                  'Pendidikan','Kesehatan','Sosial','Kedaruratan',
                  'Bencana Alam','Lingkungan','Pangan','Keagamaan','Infrastruktur'
                ) NOT NULL,
  `target`      DECIMAL(15,2)     NOT NULL COMMENT 'dalam Rupiah penuh',
  `collected`   DECIMAL(15,2)     NOT NULL DEFAULT 0 COMMENT 'dalam Rupiah penuh',
  `pct`         DECIMAL(5,2)      NOT NULL DEFAULT 0 COMMENT 'persentase 0-100',
  `deadline`    DATE              NOT NULL,
  `status`      ENUM('active','inactive','closed','deleted') NOT NULL DEFAULT 'active',
  `image`       VARCHAR(255)      NOT NULL DEFAULT '' COMMENT 'path relatif dari public/',
  `gradient`    VARCHAR(100)      NOT NULL DEFAULT 'linear-gradient(135deg,#0D1B3E,#2A4080)',
  `created_by`  INT UNSIGNED      NOT NULL COMMENT 'FK → users.id (staff/admin)',
  `created_at`  TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_programs_kode` (`kode`),
  KEY `idx_programs_status`   (`status`),
  KEY `idx_programs_category` (`category`),
  CONSTRAINT `fk_programs_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Program donasi';


CREATE TABLE IF NOT EXISTS `donations` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `kode`         VARCHAR(15)     NOT NULL COMMENT 'DN-2024, dst.',
  `user_id`      INT UNSIGNED    NOT NULL COMMENT 'FK → users.id (donatur)',
  `program_id`   INT UNSIGNED    NOT NULL COMMENT 'FK → programs.id',
  `amount`       DECIMAL(15,2)   NOT NULL COMMENT 'dalam Rupiah',
  `method`       ENUM(
                   'BCA Transfer','Mandiri Transfer','BRI Transfer',
                   'BNI Transfer','QRIS','Cash'
                 ) NOT NULL,
  `proof`        VARCHAR(255)    NOT NULL DEFAULT '' COMMENT 'path bukti transfer',
  `status`       ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `processed_by` INT UNSIGNED    NULL COMMENT 'FK → users.id (staff yg verifikasi)',
  `processed_at` TIMESTAMP       NULL,
  `note`         VARCHAR(255)    NOT NULL DEFAULT '' COMMENT 'catatan penolakan, dsb.',
  `donated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_donations_kode` (`kode`),
  KEY `idx_donations_status`     (`status`),
  KEY `idx_donations_user`       (`user_id`),
  KEY `idx_donations_program`    (`program_id`),
  KEY `idx_donations_processed`  (`processed_by`),
  CONSTRAINT `fk_donations_user`
    FOREIGN KEY (`user_id`)    REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_donations_program`
    FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_donations_processed_by`
    FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Transaksi donasi';


CREATE TABLE IF NOT EXISTS `staff_profiles` (
  `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`   INT UNSIGNED  NOT NULL COMMENT 'FK → users.id',
  `kode`      VARCHAR(10)   NOT NULL COMMENT 'STF-01, dst.',
  `jabatan`   VARCHAR(80)   NOT NULL DEFAULT 'Staff Verifikasi',
  `joined_at` DATE          NOT NULL,
  `status`    ENUM('active','inactive') NOT NULL DEFAULT 'active',

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_staff_user`  (`user_id`),
  UNIQUE KEY `uq_staff_kode`  (`kode`),
  KEY `idx_staff_status` (`status`),
  CONSTRAINT `fk_staff_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Profil tambahan staff';


CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED  NULL COMMENT 'FK → users.id, NULL jika sistem',
  `actor_name`  VARCHAR(100)  NOT NULL COMMENT 'nama aktor saat aksi terjadi',
  `role`        VARCHAR(20)   NOT NULL,
  `description` VARCHAR(255)  NOT NULL,
  `ref`         VARCHAR(50)   NOT NULL DEFAULT '' COMMENT 'kode referensi: DN-xxx, PR-xx, dsb.',
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_logs_user`       (`user_id`),
  KEY `idx_logs_created_at` (`created_at`),
  CONSTRAINT `fk_logs_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit log aktivitas pengguna';


CREATE TABLE IF NOT EXISTS `settings` (
  `key`         VARCHAR(80)   NOT NULL,
  `value`       TEXT          NOT NULL,
  `description` VARCHAR(255)  NOT NULL DEFAULT '',
  `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Konfigurasi aplikasi';


INSERT INTO `users` (`id`,`name`,`email`,`password`,`role`,`initials`,`color`,`photo`) VALUES
(1, 'Ahmad Haris',    'admin@s.id',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',   'AH', '#2563eb', ''),
(2, 'Dina Ramadhani', 'staff@s.id',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff',   'DR', '#d97706', 'assets/uploads/avatars/avatar-staffsid-20260504095100.png'),
(3, 'Siti Rahayu',    'don@s.id',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donatur', 'SR', '#059669', 'assets/uploads/avatars/avatar-donsid-20260504095100.png'),
(4, 'Reza Kurniawan', 'reza@sipedo.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff',   'RK', '#d97706', ''),
(5, 'Hana Permata',   'hana@sipedo.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff',   'HP', '#d97706', ''),
(6, 'Budi Pratama',   'budi@mail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donatur', 'BP', '#7c3aed', ''),
(7, 'Rina Nurcahya',  'rina@mail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donatur', 'RN', '#d97706', ''),
(8, 'Andi Setiawan',  'andi@mail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donatur', 'AS', '#059669', ''),
(9, 'Maya Kusuma',    'maya@mail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donatur', 'MK', '#dc2626', '');


INSERT INTO `staff_profiles` (`user_id`,`kode`,`jabatan`,`joined_at`,`status`) VALUES
(2, 'STF-01', 'Staff Verifikasi', '2024-11-01', 'active'),
(4, 'STF-02', 'Staff Verifikasi', '2024-11-15', 'active'),
(5, 'STF-03', 'Staff Senior',     '2024-10-10', 'active');


INSERT INTO `programs` (`id`,`kode`,`name`,`description`,`category`,`target`,`collected`,`pct`,`deadline`,`status`,`image`,`gradient`,`created_by`) VALUES
(1, 'PR-01', 'Beasiswa Anak Yatim',
   'Program beasiswa pendidikan untuk anak-anak yatim piatu kurang mampu.',
   'Pendidikan', 50000000.00, 48300000.00, 96.60, '2024-12-31', 'active',
   'assets/images/beasiswa-anak-yatim.jpg',
   'linear-gradient(135deg,#0D1B3E,#2A4080)', 2),

(2, 'PR-02', 'Renovasi Panti Asuhan',
   'Perbaikan fasilitas panti asuhan demi kenyamanan penghuni.',
   'Sosial', 80000000.00, 72100000.00, 90.10, '2025-01-15', 'active',
   'assets/images/renovasi-panti-asuhan.jpg',
   'linear-gradient(135deg,#065F46,#0F9D58)', 2),

(3, 'PR-03', 'Bantuan Bencana Alam',
   'Penyaluran bantuan darurat untuk korban bencana alam.',
   'Kedaruratan', 120000000.00, 55000000.00, 45.80, '2025-02-28', 'active',
   'assets/images/bantuan-bencana-alam.jpg',
   'linear-gradient(135deg,#B45309,#F59E0B)', 4),

(4, 'PR-04', 'Pengobatan Gratis',
   'Layanan pemeriksaan dan pengobatan gratis untuk dhuafa.',
   'Kesehatan', 30000000.00, 30000000.00, 100.00, '2024-12-01', 'closed',
   'assets/images/pengobatan-gratis.jpg',
   'linear-gradient(135deg,#7C3AED,#A78BFA)', 5);

UPDATE `programs` SET `image` = CASE `kode`
  WHEN 'PR-01' THEN 'assets/images/beasiswa-anak-yatim.jpg'
  WHEN 'PR-02' THEN 'assets/images/renovasi-panti-asuhan.jpg'
  WHEN 'PR-03' THEN 'assets/images/bantuan-bencana-alam.jpg'
  WHEN 'PR-04' THEN 'assets/images/pengobatan-gratis.jpg'
  ELSE `image`
END
WHERE `kode` IN ('PR-01','PR-02','PR-03','PR-04');

INSERT INTO `donations` (`id`,`kode`,`user_id`,`program_id`,`amount`,`method`,`proof`,`status`,`processed_by`,`processed_at`,`donated_at`) VALUES
(1, 'DN-2024', 3, 1, 500000.00,  'BCA Transfer',     '',
   'pending',  NULL, NULL, '2024-12-12 08:00:00'),

(2, 'DN-2023', 6, 2, 1200000.00, 'Mandiri Transfer',
   'assets/uploads/proofs/bukti-20260504-090452-a058d2.png',
   'pending',  NULL, NULL, '2024-12-12 07:30:00'),

(3, 'DN-2022', 7, 3, 250000.00,  'BRI Transfer',
   'assets/uploads/proofs/bukti-20260504-090221-4248e1.png',
   'verified', 2, '2024-12-12 09:42:00', '2024-12-11 14:00:00'),

(4, 'DN-2021', 8, 1, 750000.00,  'QRIS',
   'assets/uploads/proofs/bukti-20260504-093511-cb17df.png',
   'verified', 4, '2024-12-11 10:00:00', '2024-12-11 09:00:00'),

(5, 'DN-2020', 9, 4, 2000000.00, 'BNI Transfer',
   'assets/uploads/proofs/bukti-20260504-093658-12dfd3.png',
   'rejected', 2, '2024-12-10 11:00:00', '2024-12-10 08:00:00');


INSERT INTO `activity_logs` (`id`,`user_id`,`actor_name`,`role`,`description`,`ref`,`created_at`) VALUES
(13, 4, 'Staff Reza',     'Staff', 'Menolak donasi',           '#DN-2020', '2024-12-12 09:30:00'),
(14, 2, 'Staff Dina',     'Staff', 'Memverifikasi donasi',     '#DN-2022', '2024-12-12 09:42:00'),
(15, 1, 'Admin Ahmad H.', 'Admin', 'Menambah staff baru',      'STF-04',   '2024-12-12 10:05:00');


INSERT INTO `settings` (`key`,`value`,`description`) VALUES
('app_name',              'SIPEDO',                          'Nama aplikasi'),
('app_subtitle',          'Sistem Pengelolaan Donasi',       'Subtitle aplikasi'),
('contact_email',         'admin@sipedo.org',                'Email kontak publik'),
('verification_deadline', '24',                              'Batas waktu verifikasi (jam)'),
('default_role',          'donatur',                         'Role default saat registrasi'),
('bank_bca',              '1234567890',                      'Nomor rekening BCA'),
('bank_bca_name',         'Yayasan SIPEDO',                  'Nama pemilik rekening BCA'),
('bank_mandiri',          '1100009876543',                   'Nomor rekening Mandiri'),
('bank_mandiri_name',     'Yayasan SIPEDO',                  'Nama pemilik rekening Mandiri'),
('bank_bri',              '0090010123456789',                'Nomor rekening BRI'),
('bank_bri_name',         'Yayasan SIPEDO',                  'Nama pemilik rekening BRI'),
('max_upload_mb',         '2',                               'Ukuran maksimal upload file (MB)');


SET FOREIGN_KEY_CHECKS = 1;


CREATE TABLE IF NOT EXISTS `program_staff` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id` int UNSIGNED NOT NULL COMMENT 'FK → programs.id',
  `staff_id` int UNSIGNED NOT NULL COMMENT 'FK → users.id (staff)',
  `added_by` int UNSIGNED NOT NULL COMMENT 'FK → users.id (staff/admin yang menambahkan)',
  `role_in_program` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Anggota' COMMENT 'Peran dalam program',
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_program_staff` (`program_id`,`staff_id`),
  KEY `idx_ps_program` (`program_id`),
  KEY `idx_ps_staff` (`staff_id`),
  CONSTRAINT `fk_ps_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ps_staff` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ps_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relasi staff ke program (lintas pembuat)';


INSERT IGNORE INTO `program_staff` (`program_id`, `staff_id`, `added_by`, `role_in_program`)
SELECT p.id, p.created_by, p.created_by, 'Koordinator'
FROM programs p
JOIN users u ON u.id = p.created_by AND u.role = 'staff'
WHERE p.status <> 'deleted';
