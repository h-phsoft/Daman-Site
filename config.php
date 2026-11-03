<?php
/**
 * ملف الإعدادات العام — اضبط قيم قاعدة البيانات هنا عند النشر.
 * Production-ready config for shared hosting & local dev.
 */

declare(strict_types=1);

/* ------------------------------------------------------------------ */
/* إعدادات قاعدة البيانات                                              */
/* ------------------------------------------------------------------ */
define('DB_HOST', 'localhost');        // على الاستضافة المشتركة غالبًا localhost
define('DB_PORT', 3306);
define('DB_NAME', 'fund_cms');
define('DB_USER', 'fund_user');
define('DB_PASS', 'Fund#Pass2026');
define('DB_CHARSET', 'utf8mb4');

/* ------------------------------------------------------------------ */
/* إعدادات الموقع                                                      */
/* ------------------------------------------------------------------ */
// الرابط الأساسي للموقع بدون شرطة مائلة في النهاية. مثال: https://example.gov.sa
define('BASE_URL', 'http://localhost:8000');

// مسار الجذر الفعلي للملفات على القرص
define('ROOT_PATH', __DIR__);

/* ------------------------------------------------------------------ */
/* المسارات — تُعرَّف هنا فقط                                          */
/* قاعدة البيانات تخزّن اسم الملف وحده (مثل 20261103-ab12.jpg)،         */
/* ويُبنى المسار الكامل من هذه الإعدادات حسب نوع المحتوى.              */
/* ------------------------------------------------------------------ */

// جذر مجلد الرفع على القرص، ورابطه النسبي بالنسبة إلى BASE_URL
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', 'uploads');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB

// مجلد كل نوع محتوى داخل مجلد الرفع (النوع => المجلد)
const UPLOAD_DIRS = [
    'slider'     => 'slider',    // صور السلايدر
    'news'       => 'news',      // صور الأخبار
    'products'   => 'products',  // صور المنتجات
    'projects'   => 'products',  // صور المشاريع المستهدفة
    'sections'   => 'products',  // صور فقرات الصفحات
    'board'      => 'team',      // صور أعضاء مجلس الإدارة
    'executives' => 'team',      // صور الإدارة التنفيذية
    'links'      => 'links',     // شعارات المواقع الهامة
    'reports'    => 'reports',   // ملفات التقارير السنوية (PDF)
    'logo'       => 'img',       // الشعار المخصص المرفوع من الإعدادات
];

// مجلد الأصول الثابتة (CSS/JS/صور) بالنسبة إلى BASE_URL
define('ASSETS_URL', 'assets');

// مجلد الصور الافتراضية (الشعار، الأيقونة، والصور البديلة عند عدم وجود صورة) داخل الأصول
define('DEFAULT_IMG_DIR', 'img');

/* ------------------------------------------------------------------ */
/* الشعار والنمط                                                       */
/* ------------------------------------------------------------------ */
// ملفا الشعار داخل DEFAULT_IMG_DIR: واحد لكل نمط
define('LOGO_LIGHT', 'logo.png');   // للنمط الفاتح (شعار ملوّن على خلفية فاتحة)
define('LOGO_DARK',  'logow.png');  // للنمط الداكن (شعار أبيض على خلفية داكنة)

// النمط الافتراضي للزائر الجديد: 'light' أو 'dark'
define('DEFAULT_THEME', 'light');

/* ------------------------------------------------------------------ */
/* البيئة: development | production                                    */
/* ------------------------------------------------------------------ */
define('APP_ENV', 'development');

if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/logs/php-error.log');

/* المنطقة الزمنية */
date_default_timezone_set('Asia/Riyadh');

/* جلسة آمنة */
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => APP_ENV === 'production',
]);
