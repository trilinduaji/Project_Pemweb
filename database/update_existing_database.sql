USE `sipedo`;

ALTER TABLE `programs`
  MODIFY `status` ENUM('active','inactive','closed','deleted') NOT NULL DEFAULT 'active';

UPDATE `programs` SET `image` = CASE `kode`
  WHEN 'PR-01' THEN 'assets/images/beasiswa-anak-yatim.jpg'
  WHEN 'PR-02' THEN 'assets/images/renovasi-panti-asuhan.jpg'
  WHEN 'PR-03' THEN 'assets/images/bantuan-bencana-alam.jpg'
  WHEN 'PR-04' THEN 'assets/images/pengobatan-gratis.jpg'
  ELSE `image`
END
WHERE `kode` IN ('PR-01','PR-02','PR-03','PR-04')
  AND (`image` IS NULL OR `image` = '' OR `image` LIKE 'assets/uploads/programs/prog-20260504-%');

INSERT IGNORE INTO `settings` (`key`, `value`, `description`) VALUES
('max_upload_mb', '2', 'Ukuran maksimal upload file (MB)');

CREATE TABLE IF NOT EXISTS `program_staff` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id` int UNSIGNED NOT NULL,
  `staff_id` int UNSIGNED NOT NULL,
  `added_by` int UNSIGNED NOT NULL,
  `role_in_program` varchar(80) NOT NULL DEFAULT 'Anggota',
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_program_staff` (`program_id`,`staff_id`),
  KEY `idx_ps_program` (`program_id`),
  KEY `idx_ps_staff` (`staff_id`),
  CONSTRAINT `fk_ps_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ps_staff` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ps_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `program_staff` (`program_id`, `staff_id`, `added_by`, `role_in_program`)
SELECT p.id, p.created_by, p.created_by, 'Koordinator'
FROM programs p
JOIN users u ON u.id = p.created_by AND u.role = 'staff'
WHERE p.status <> 'deleted';
