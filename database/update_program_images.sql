USE `sipedo`;

UPDATE `programs` SET `image` = CASE `kode`
  WHEN 'PR-01' THEN 'assets/images/beasiswa-anak-yatim.jpg'
  WHEN 'PR-02' THEN 'assets/images/renovasi-panti-asuhan.jpg'
  WHEN 'PR-03' THEN 'assets/images/bantuan-bencana-alam.jpg'
  WHEN 'PR-04' THEN 'assets/images/pengobatan-gratis.jpg'
  ELSE `image`
END
WHERE `kode` IN ('PR-01','PR-02','PR-03','PR-04')
  AND (`image` IS NULL OR `image` = '' OR `image` LIKE 'assets/uploads/programs/prog-20260504-%');
