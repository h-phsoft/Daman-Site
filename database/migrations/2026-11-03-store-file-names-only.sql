-- =====================================================================
-- ترحيل: تخزين اسم الملف فقط في قاعدة البيانات (بدون مسارات)
-- المجلدات أصبحت معرّفة في config.php (UPLOAD_DIRS)، لذا يُزال أي مسار
-- مخزّن ويبقى اسم الملف وحده. آمن للتشغيل أكثر من مرة.
-- الروابط الخارجية الكاملة (http/https) لا تُمس.
-- =====================================================================

SET NAMES utf8mb4;

UPDATE `slider`          SET `image`     = SUBSTRING_INDEX(`image`, '/', -1)     WHERE `image`     LIKE '%/%' AND `image`     NOT LIKE 'http%';
UPDATE `news`            SET `image`     = SUBSTRING_INDEX(`image`, '/', -1)     WHERE `image`     LIKE '%/%' AND `image`     NOT LIKE 'http%';
UPDATE `products`        SET `image`     = SUBSTRING_INDEX(`image`, '/', -1)     WHERE `image`     LIKE '%/%' AND `image`     NOT LIKE 'http%';
UPDATE `projects`        SET `image`     = SUBSTRING_INDEX(`image`, '/', -1)     WHERE `image`     LIKE '%/%' AND `image`     NOT LIKE 'http%';
UPDATE `page_sections`   SET `image`     = SUBSTRING_INDEX(`image`, '/', -1)     WHERE `image`     LIKE '%/%' AND `image`     NOT LIKE 'http%';
UPDATE `board_members`   SET `image`     = SUBSTRING_INDEX(`image`, '/', -1)     WHERE `image`     LIKE '%/%' AND `image`     NOT LIKE 'http%';
UPDATE `executives`      SET `image`     = SUBSTRING_INDEX(`image`, '/', -1)     WHERE `image`     LIKE '%/%' AND `image`     NOT LIKE 'http%';
UPDATE `important_links` SET `image`     = SUBSTRING_INDEX(`image`, '/', -1)     WHERE `image`     LIKE '%/%' AND `image`     NOT LIKE 'http%';
UPDATE `reports`         SET `file_path` = SUBSTRING_INDEX(`file_path`, '/', -1) WHERE `file_path` LIKE '%/%' AND `file_path` NOT LIKE 'http%';

-- الشعار: القيمة الافتراضية القديمة تصبح فارغة (= شعارا ضمان الافتراضيان)، والمرفوع يبقى اسمه فقط
UPDATE `settings` SET `setting_value` = ''
  WHERE `setting_key` = 'logo' AND `setting_value` IN ('assets/img/logo.svg', 'img/logo.svg');
UPDATE `settings` SET `setting_value` = SUBSTRING_INDEX(`setting_value`, '/', -1)
  WHERE `setting_key` = 'logo' AND `setting_value` LIKE '%/%' AND `setting_value` NOT LIKE 'http%';
