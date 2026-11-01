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

// مجلدات الرفع
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB

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
