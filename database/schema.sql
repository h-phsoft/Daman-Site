-- =====================================================================
-- نظام إدارة محتوى موقع الصندوق (Vanilla PHP + MySQL)
-- Database Schema — MySQL 5.7+ / MariaDB 10.3+
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci / Engine: InnoDB
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- جدول المستخدمين (لوحة التحكم)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(60)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('admin','editor') NOT NULL DEFAULT 'admin',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- إعدادات الموقع
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key`   VARCHAR(80)  NOT NULL,
  `setting_value` TEXT         NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- الصفحات العامة (تُستخدم لروابط القوائم والمحتوى المنسّق)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`       VARCHAR(120) NOT NULL,
  `title`      VARCHAR(200) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`),
  KEY `idx_pages_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- أقسام الصفحات (فقرات محتوى مرتبطة بصفحة عبر slug الصفحة)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `page_sections`;
CREATE TABLE `page_sections` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id`     INT UNSIGNED NOT NULL,
  `title`       VARCHAR(200) NOT NULL,
  `image`       VARCHAR(255) NULL,
  `content_html` LONGTEXT    NULL,
  `sort_order`  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_sections_page` (`page_id`),
  KEY `idx_sections_sort` (`sort_order`),
  CONSTRAINT `fk_sections_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- المنتجات
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(200) NOT NULL,
  `image`        VARCHAR(255) NULL,
  `short_desc`   TEXT         NULL,
  `content_html` LONGTEXT     NULL,
  `sort_order`   INT NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_products_sort` (`sort_order`),
  KEY `idx_products_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- المشاريع المستهدفة
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(200) NOT NULL,
  `image`        VARCHAR(255) NULL,
  `content_html` LONGTEXT     NULL,
  `sort_order`   INT NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_projects_sort` (`sort_order`),
  KEY `idx_projects_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- أعضاء مجلس الإدارة
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `board_members`;
CREATE TABLE `board_members` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  `position`   VARCHAR(150) NULL,
  `image`      VARCHAR(255) NULL,
  `bio_html`   LONGTEXT     NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_board_sort` (`sort_order`),
  KEY `idx_board_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- الإدارة التنفيذية
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `executives`;
CREATE TABLE `executives` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  `position`   VARCHAR(150) NULL,
  `image`      VARCHAR(255) NULL,
  `bio_html`   LONGTEXT     NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_exec_sort` (`sort_order`),
  KEY `idx_exec_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- الأخبار
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(200) NOT NULL,
  `slug`         VARCHAR(220) NOT NULL,
  `image`        VARCHAR(255) NULL,
  `publish_date` DATE NOT NULL,
  `excerpt_html` MEDIUMTEXT   NULL,
  `content_html` LONGTEXT     NULL,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_news_slug` (`slug`),
  KEY `idx_news_date` (`publish_date`),
  KEY `idx_news_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- التقارير السنوية (ملفات قابلة للتحميل)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(200) NOT NULL,
  `file_path`    VARCHAR(255) NOT NULL,
  `publish_date` DATE NOT NULL,
  `sort_order`   INT NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_reports_sort` (`sort_order`),
  KEY `idx_reports_date` (`publish_date`),
  KEY `idx_reports_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- الأسئلة المتكررة
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question`   VARCHAR(300) NOT NULL,
  `answer_html` LONGTEXT    NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_faqs_sort` (`sort_order`),
  KEY `idx_faqs_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- المواقع الهامة
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `important_links`;
CREATE TABLE `important_links` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(200) NOT NULL,
  `image`      VARCHAR(255) NULL,
  `url`        VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_links_sort` (`sort_order`),
  KEY `idx_links_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- السلايدر الرئيسي
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `slider`;
CREATE TABLE `slider` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image`      VARCHAR(255) NOT NULL,
  `title`      VARCHAR(200) NULL,
  `subtitle`   VARCHAR(300) NULL,
  `cta_text`   VARCHAR(100) NULL,
  `cta_link`   VARCHAR(300) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_slider_sort` (`sort_order`),
  KEY `idx_slider_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- رسائل التواصل / المقترحات والشكاوى
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`       ENUM('contact','suggestion','complaint') NOT NULL DEFAULT 'contact',
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(190) NULL,
  `phone`      VARCHAR(50)  NULL,
  `message`    TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_msg_type` (`type`),
  KEY `idx_msg_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- البيانات الأولية (Seed Data)
-- =====================================================================

-- كلمة مرور admin الافتراضية: Admin@123 (يُرجى تغييرها فورًا من لوحة التحكم)
INSERT INTO `users` (`username`, `password_hash`, `role`) VALUES
('admin', '$2y$10$h/72SFeVX98lfc2eKpxFx.6OYT4jspwZgWf75Zd2hpSYwlUZ1fW1S', 'admin');

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'صندوق الإسكان والتطوير'),
('site_tagline', 'نحو سكن كريم وحياة أفضل'),
('logo', ''),  -- فارغ = الشعار الافتراضي (logo.png / logow.png)
('address', 'الرياض - المملكة العربية السعودية'),
('phone1', '0110000000'),
('phone2', '0500000000'),
('email', 'info@example.gov'),
('facebook', 'https://facebook.com'),
('twitter', 'https://x.com'),
('instagram', 'https://instagram.com'),
('linkedin', 'https://linkedin.com'),
('youtube', 'https://youtube.com'),
('meta_description', 'الموقع الرسمي لصندوق الإسكان والتطوير - خدماتنا، أخبارنا، تقاريرنا السنوية.'),
('footer_about', 'صندوق حكومي يهدف إلى توفير حلول سكنية وتمويلية مبتكرة تدعم الاستقرار الأسري وتنمية المجتمع.');

INSERT INTO `pages` (`id`, `slug`, `title`, `sort_order`) VALUES
(1, 'objectives',   'الإحداث والأهداف', 1),
(2, 'director',     'كلمة المدير العام', 2),
(3, 'how-to-apply', 'أسلوب التقدم للخدمة', 3),
(4, 'file-path',    'مسار الملف', 4);

INSERT INTO `page_sections` (`page_id`, `title`, `image`, `content_html`, `sort_order`) VALUES
(1, 'إحداث الصندوق', NULL, '<p>أُنشئ الصندوق بمرسوم سامٍ بهدف توفير solutions سكنية وتمويلية متكاملة، وقد أُحدثت هيكلته التنظيمية ليتوافق مع رؤية التنمية الوطنية.</p>', 1),
(1, 'الأهداف الاستراتيجية', NULL, '<ul><li>تيسير تملّك المسكن الملائم للمواطنين.</li><li>تنظيم سوق التمويل العقاري ودعم استقراره.</li><li>تمكين التطوير العمراني المستدام.</li><li>رفع كفاءة الخدمات الرقمية للمستفيدين.</li></ul>', 2),
(2, 'كلمة المدير العام', 'placeholder-director.svg', '<p>يسعدني أن أرحّب بكم في الموقع الرسمي للصندوق. نعمل جاهدين لتقديم خدمات نوعية تسهم في تحقيق الاستقرار السكني للأسر، مستندين في ذلك على قيم الشفافية والجودة والابتكار.</p><p>إن ما تحقق من إنجازات ما كان ليتم لولا دعم القيادة الحكيمة وتعاون شركائنا، ونعدكم بمواصلة العمل لتحقيق تطلعات مواطنينا الأعزاء.</p>', 1),
(3, 'شروط الأهلية', NULL, '<p>يشترط في مقدّم الطلب أن يكون سعودي الجنسية، وألا يقل عمره عن 18 عامًا، وأن يكون من ضمن الفئات المستهدفة.</p>', 1),
(3, 'خطوات التقديم', NULL, '<ol><li>تسجيل الدخول إلى البوابة الإلكترونية.</li><li>إكمال بيانات الطلب ورفع المستندات.</li><li>دراسة الطلب والتحقق من الأهلية.</li><li>إصدار القرار وإشعار المستفيد.</li></ol>', 2),
(3, 'المستندات المطلوبة', NULL, '<ul><li>هوية وطنية سارية.</li><li>كشف حساب بنكي.</li><li>صك الملكية أو عقد الإيجار إن وُجد.</li></ul>', 3),
(4, 'مسار ملف الطلب', NULL, '<p>يُرسل الطلب إلكترونيًا إلى قسم الاستلام ثم يُحوّل للتحقق من المستندات.</p>', 1),
(4, 'مراحل المعالجة', NULL, '<p>بعد التحقق تُحال الدراسة الفنية والمالية إلى اللجان المختصة، ثم يصدر القرار النهائي ويُبلّغ به مقدم الطلب عبر الرسائل النصية والبريد الإلكتروني.</p>', 1);

INSERT INTO `products` (`title`, `image`, `short_desc`, `content_html`, `sort_order`, `is_active`) VALUES
('التمويل المدعوم', 'placeholder-product1.svg', 'برنامج تمويل سكني مدعوم التكلفة يساعد الأسر على تملّك المسكن المناسب بأقساط مريحة.', '<h2>التمويل المدعوم</h2><p>يوفّر البرنامج تمويلاً سكنيًا مدعوم التكلفة بشروط ميسّرة وسقف تمويل يصل إلى مليون ريال، مع خيارات مرنة للسداد تمتد حتى 25 عامًا.</p><ul><li>معدل ربح تنافسي ومدعوم.</li><li>دفعة أولى مخفّضة.</li><li>موافقات فورية عبر البوابة الإلكترونية.</li></ul>', 1, 1),
('مسكن المطوّرين', 'placeholder-product2.svg', 'وحدات سكنية جاهزة مطوّرة من قبل شركاء معتمدين بجودة وضمانات متكاملة.', '<h2>مسكن المطوّرين</h2><p>يتضمن البرنامج وحدات سكنية مكتملة التشطيب مطوّرة من قبل شبكة من المطوّرين المعتمدين لدى الصندوق، مع ضمانات على الهيكل والإنهاءات.</p>', 2, 1),
('باقة الترميم والتحسين', 'placeholder-product3.svg', 'حلول تمويلية لترميم المساكن القائمة وتحسين كفاءتها السكنية والطاقة.', '<h2>باقة الترميم والتحسين</h2><p>مخصص لملاك المساكن القائمة الراغبين في الترميم أو التوسعة أو تحسين كفاءة الطاقة، بإجراءات سريعة ومبالغ تمويلية مناسبة.</p>', 3, 1);

INSERT INTO `projects` (`title`, `image`, `content_html`, `sort_order`, `is_active`) VALUES
('مجمع الواحة السكني', 'placeholder-project1.svg', '<p>مشروع تطوير سكني متكامل يضم أكثر من 1200 وحدة سكنية مع مرافق خدمية وتجارية ومساحات خضراء.</p>', 1, 1),
('مدينة المستقبل العمرانية', 'placeholder-project2.svg', '<p>تطوير عمراني مستدام يراعي معايير الكفاءة في استخدام المياه والطاقة ويوفر بيئة سكنية ذكية.</p>', 2, 1);

INSERT INTO `board_members` (`name`, `position`, `image`, `bio_html`, `sort_order`, `is_active`) VALUES
('معالي المهندس / عبدالله الأحمد', 'رئيس مجلس الإدارة', 'placeholder-member1.svg', '<p>خبرة تتجاوز 25 عامًا في قطاع الإسكان والتطوير العمراني.</p>', 1, 1),
('سعادة الدكتور / سعد القحطاني', 'عضو المجلس', 'placeholder-member2.svg', '<p>أكاديمي ومستشار في السياسات الإسكانية والتمويل التنموي.</p>', 2, 1),
('سعادة الأستاذة / نورة العتيبي', 'عضو المجلس', 'placeholder-member3.svg', '<p>خبيرة في الحوكمة وإدارة المخاطر المالية.</p>', 3, 1);

INSERT INTO `executives` (`name`, `position`, `image`, `bio_html`, `sort_order`, `is_active`) VALUES
('الأستاذ / محمد الشمري', 'المدير العام', 'placeholder-exec1.svg', '<p>يقود تنفيذ استراتيجية الصندوق وبرامجه التشغيلية.</p>', 1, 1),
('المهندس / خالد الدوسري', 'نائب المدير للعمليات', 'placeholder-exec2.svg', '<p>يشرف على العمليات التمويلية والتطويرية.</p>', 2, 1),
('الأستاذة / سارة الحربي', 'نائب المدير للتخطيط', 'placeholder-exec3.svg', '<p>تقود التحول الرقمي والتخطيط الاستراتيجي.</p>', 3, 1);

INSERT INTO `news` (`title`, `slug`, `image`, `publish_date`, `excerpt_html`, `content_html`, `is_active`) VALUES
('الصندوق يطلق برنامج التمويل المعجّل', 'launch-accelerated-financing', 'placeholder-news1.svg', '2026-09-14', '<p>أطلق الصندوق اليوم برنامجًا جديدًا يختصر مدة إصدار الموافقات التمويلية إلى أقل من أسبوع عمل واحد.</p>', '<h2>برنامج التمويل المعجّل</h2><p>في إطار جهوده لتحسين تجربة المستفيدين، أطلق الصندوق برنامج التمويل المعجّل الذي يعتمد على التكامل مع الجهات الحكومية لإصدار القرارات آليًا.</p><p>وأوضح المتحدث الرسمي أن البرنامج يستهدف في مرحلته الأولى 40 ألف أسرة.</p>', 1),
('نتائج الربع الثالث: نمو في عدد العقود', 'q3-contract-growth', 'placeholder-news2.svg', '2026-10-05', '<p>سجّل الصندوق نموًا في عدد العقود المبرمة خلال الربع الثالث بنسبة 18% مقارنة بالربع السابق.</p>', '<h2>نمو العقود</h2><p>أظهر التقرير الربعي نموًا ملحوظًا في حجم التمويل السكني الممنوح، مدفوعًا بتوسع شبكة المطوّرين المعتمدين.</p>', 1),
('ورشة عمل حول جودة البناء', 'quality-workshop', 'placeholder-news3.svg', '2026-10-21', '<p>نظّم الصندوق ورشة عمل مع شركاء القطاع الخاص لبحث معايير جودة البناء والاستدامة.</p>', '<h2>جودة البناء</h2><p>ناقشت الورشة تحديث أدلة الجودة والفحص الفني للوحدات السكنية قبل التخصيص.</p>', 1);

INSERT INTO `reports` (`title`, `file_path`, `publish_date`, `sort_order`, `is_active`) VALUES
('التقرير السنوي 2025', 'sample-report.pdf', '2026-04-01', 1, 1),
('التقرير السنوي 2024', 'sample-report.pdf', '2025-04-01', 2, 1);

INSERT INTO `faqs` (`question`, `answer_html`, `sort_order`, `is_active`) VALUES
('كيف أقدّم طلب الحصول على خدمة الصندوق؟', '<p>يمكنك التقديم عبر البوابة الإلكترونية من قسم «التقدم للخدمة» متبعًا الخطوات الموضحة هناك.</p>', 1, 1),
('ما هي مدة دراسة الطلب؟', '<p>تختلف حسب نوع الخدمة، وتتراوح عادة بين 5 و30 يوم عمل.</p>', 2, 1),
('هل يمكن تحديث المستندات بعد تقديم الطلب؟', '<p>نعم، من خلال حسابك في البوابة وقسم «طلباتي».</p>', 3, 1);

INSERT INTO `important_links` (`title`, `image`, `url`, `sort_order`, `is_active`) VALUES
('وزارة الشؤون البلدية والإسكان', 'placeholder-site1.svg', 'https://www.mbw.gov.sa', 1, 1),
('منصة أبشر', 'placeholder-site2.svg', 'https://absher.sa', 2, 1),
('الهيئة العامة للعقار', 'placeholder-site3.svg', 'https://reg.gov.sa', 3, 1);

INSERT INTO `slider` (`image`, `title`, `subtitle`, `cta_text`, `cta_link`, `sort_order`, `is_active`) VALUES
('slide1.svg', 'نحو سكن كريم', 'حلول تمويلية متكاملة تلبي طموحات الأسرة السعودية', 'تعرّف على خدماتنا', 'pages/products/index.php', 1, 1),
('slide2.svg', 'شركاؤنا في التطوير', 'شبكة مطوّرين معتمدين وجودة بناء موثوقة', 'استعرض المشاريع', 'pages/products/projects.php', 2, 1),
('slide3.svg', 'تحوّل رقمي كامل', 'قدّم طلبك وتابع حالته إلكترونيًا على مدار الساعة', 'ابدأ التقديم', 'pages/apply/how-to-apply.php', 3, 1);
