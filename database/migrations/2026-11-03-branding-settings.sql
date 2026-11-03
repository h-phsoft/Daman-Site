-- =====================================================================
-- ترحيل: الشعاران والأيقونتان والنمط الافتراضي في جدول settings
-- كانت في config.php (LOGO_LIGHT, LOGO_DARK, DEFAULT_THEME) وصارت تُدار
-- من صفحة «إعدادات الموقع». الملفات الافتراضية في uploads/img.
-- شغّله بعد 2026-11-03-store-file-names-only.sql. آمن للتشغيل أكثر من مرة.
-- =====================================================================

SET NAMES utf8mb4;

-- القيم الافتراضية (لا تغيّر قيمة موجودة)
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('logo_light', 'logo.png'),
('logo_dark', 'logow.png'),
('favicon', 'favicon.png'),
('apple_touch_icon', 'apple-touch-icon.png'),
('default_theme', 'light');

-- إن كان المدير قد رفع شعارًا مخصصًا واحدًا (الإعداد القديم logo) فيُستخدم للنمطين
UPDATE `settings` s
  JOIN `settings` old ON old.`setting_key` = 'logo'
                     AND old.`setting_value` IS NOT NULL AND old.`setting_value` <> ''
   SET s.`setting_value` = old.`setting_value`
 WHERE s.`setting_key` IN ('logo_light', 'logo_dark');

-- الإعداد القديم لم يعد مستخدمًا
DELETE FROM `settings` WHERE `setting_key` = 'logo';
