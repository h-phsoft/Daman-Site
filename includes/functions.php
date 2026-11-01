<?php
/**
 * دوال مساعدة عامة: أمان، إعدادات، مسارات، رفع ملفات، تحقق.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/* ================================================================== */
/* الأمان الأساسي                                                     */
/* ================================================================== */

/** تنقية المخرجات العامة */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** رابط مطلق من مسار نسبي */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return BASE_URL . ($path === '' ? '/' : '/' . $path);
}

/** رابط أصل (asset) */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** رابط صورة مرفوعة أو أصل افتراضي */
function upload_url(?string $path, string $fallback = 'img/no-image.svg'): string
{
    if ($path !== null && trim($path) !== '' && !preg_match('#^https?://#i', $path)) {
        if (str_starts_with($path, 'uploads/') || str_starts_with($path, 'assets/')) {
            return url($path);
        }
        return url('assets/' . $path);
    }
    if ($path !== null && preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return asset($fallback);
}

/** CSRF: توليد توكن */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** حقل CSRF مخفي للنماذج */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** التحقق من CSRF في الطلبات POST */
function csrf_verify(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $sent);
}

/** فرض التحقق من CSRF وإيقاف التنفيذ عند الفشل */
function csrf_require(): void
{
    if (!csrf_verify()) {
        http_response_code(419);
        exit('انتهت صلاحية الجلسة أو طلب غير صالح (CSRF). يرجى إعادة المحاولة.');
    }
}

/** رسائل فلاش */
function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}
function flash_get(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ================================================================== */
/* الإعدادات                                                          */
/* ================================================================== */

/** جلب كل الإعدادات كـ key=>value (مع كاش للطلب الواحد) */
function settings(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = [];
        foreach (fetchAll('SELECT setting_key, setting_value FROM settings') as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $v = settings()[$key] ?? null;
    return ($v === null || $v === '') ? $default : (string)$v;
}

function set_setting(string $key, string $value): void
{
    q(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$key, $value]
    );
    settings(true); // تحديث الكاش
}

/* ================================================================== */
/* عناوين SEO ديناميكية                                               */
/* ================================================================== */

function page_title(?string $title): string
{
    $site = setting('site_name', 'الصندوق');
    return $title ? ($title . ' | ' . $site) : ($site . ' — ' . setting('site_tagline', ''));
}

/* ================================================================== */
/* تاريخ عربي                                                        */
/* ================================================================== */

function ar_date(?string $date): string
{
    if (!$date) return '';
    $ts = strtotime($date);
    if ($ts === false) return '';
    $months = [1=>'يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    return sprintf('%d %s %d', (int)date('j', $ts), $months[(int)date('n', $ts)], (int)date('Y', $ts));
}

/* ================================================================== */
/* توليد slug عربي/لاتيني                                            */
/* ================================================================== */

function make_slug(string $text): string
{
    $text = trim($text);
    // إبقاء الأحرف العربية والحروف اللاتينية والأرقام
    $text = preg_replace('/[^\p{Arabic}\p{Latin}\p{Nd}]+/u', '-', $text) ?? '';
    $text = mb_strtolower(trim($text, '-'), 'UTF-8');
    $text = preg_replace('/-+/', '-', $text) ?? '';
    if ($text === '' || $text === '-') {
        $text = 'item-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }
    return mb_substr($text, 0, 200);
}

function unique_news_slug(string $title, ?int $ignoreId = null): string
{
    $base = make_slug($title);
    $slug = $base;
    $i = 1;
    while (true) {
        if ($ignoreId !== null) {
            $exists = fetchValue('SELECT id FROM news WHERE slug = ? AND id <> ?', [$slug, $ignoreId]);
        } else {
            $exists = fetchValue('SELECT id FROM news WHERE slug = ?', [$slug]);
        }
        if (!$exists) return $slug;
        $slug = $base . '-' . (++$i);
    }
}

/* ================================================================== */
/* تعقيم HTML قادم من المحرر (حماية إضافية عند العرض)               */
/* ================================================================== */

/**
 * يسمح فقط بوسوم المحتوى النصي التي ينتجها المحرر، ويزيل الوسوم الخطرة
 * وأحداث On* وأي bdi javascript. يُستخدم مع content_html القادم من DB.
 */
function clean_html(?string $html): string
{
    $html = (string)$html;
    if ($html === '') return '';

    $allowed = '<a><b><strong><i><em><u><s><p><br><hr><ul><ol><li><h1><h2><h3><h4><h5><h6>'
             . '<blockquote><table><thead><tbody><tr><th><td><figure><figcaption><img><span><div><pre><code>';

    // إزالة وسوم script/style وكائناتها ومحتواها
    $html = preg_replace('#<(script|style|iframe|object|embed|form|link|meta)[^>]*>.*?</\1>#is', '', $html) ?? '';
    $html = preg_replace('#<(script|style|iframe|object|embed|form|link|meta)[^>]*/?>#is', '', $html) ?? '';

    // إزالة سمات event handlers و javascript:
    $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? '';
    $html = preg_replace('#(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2#i', '$1="#"', $html) ?? '';

    // إزالة الوسوم غير المسموحة مع الإبقاء على محتواها النصي
    $html = strip_tags($html, $allowed);

    return $html;
}

/* ================================================================== */
/* التحقق من المدخلات                                                 */
/* ================================================================== */

function valid_email(?string $email): bool
{
    return is_string($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}

function valid_phone(?string $phone): bool
{
    return is_string($phone) && preg_match('/^[0-9+\-\s()]{7,20}$/', trim($phone)) === 1;
}

function valid_url(?string $url): bool
{
    if (!is_string($url)) return false;
    $u = trim($url);
    if ($u === '') return false;
    if (!preg_match('#^https?://#i', $u)) $u = 'https://' . $u;
    return filter_var($u, FILTER_VALIDATE_URL) !== false;
}

function normalize_url_input(string $url): string
{
    $u = trim($url);
    if ($u !== '' && !preg_match('#^[a-z][a-z0-9+.-]*://#i', $u)) {
        $u = 'https://' . $u;
    }
    return $u;
}

/* ================================================================== */
/* رفع الملفات (صور / PDF) مع فحص MIME وحجم وأسماء آمنة             */
/* ================================================================== */

/**
 * @param string $field   اسم حقل الرفع في $_FILES
 * @param string $subdir  مجلد فرعي داخل uploads/ (products, news, team, slider, links, reports)
 * @param array  $allowedMimes map: mime => extension allowed
 * @return array [ok(bool), pathOrError(string)]
 */
function upload_file(string $field, string $subdir, array $allowedMimes, int $maxSize = UPLOAD_MAX_SIZE): array
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [false, 'لم يتم اختيار ملف.'];
    }
    $f = $_FILES[$field];

    if ($f['error'] !== UPLOAD_ERR_OK) {
        return [false, 'فشل رفع الملف (رمز خطأ ' . (int)$f['error'] . ').'];
    }
    if ($f['size'] > $maxSize) {
        return [false, 'حجم الملف يتجاوز الحد المسموح (' . round($maxSize / 1048576, 1) . 'MB).'];
    }
    if (!is_uploaded_file($f['tmp_name'])) {
        return [false, 'ملف غير صالح.'];
    }

    // فحص MIME الحقيقي عبر finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($f['tmp_name']);
    if (!isset($allowedMimes[$mime])) {
        return [false, 'نوع الملف غير مسموح (' . e($mime) . ').'];
    }
    $ext = $allowedMimes[$mime];

    // منع ملفات متعددة التوسيع/خبيثة
    if (in_array($mime, ['image/svg+xml'], true)) {
        // SVG مقبول لكنه يُعرض بحذر؛ نكتفي بتعقيم أساسي
        $body = file_get_contents($f['tmp_name']);
        if (preg_match('#<script|\son\w+=#i', (string)$body)) {
            return [false, 'ملف SVG يحتوي سكربتات غير مسموحة.'];
        }
    }

    $safeDir = preg_replace('/[^a-z0-9_\-]/i', '', $subdir);
    $dir = UPLOAD_PATH . '/' . $safeDir;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        return [false, 'تعذّر حفظ الملف على الخادم.'];
    }
    @chmod($dest, 0644);

    return [true, 'uploads/' . $safeDir . '/' . $name];
}

const IMAGE_MIMES = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
    'image/gif'       => 'gif',
    'image/svg+xml'   => 'svg',
];

const FILE_MIMES_REPORTS = [
    'application/pdf' => 'pdf',
];

/* ================================================================== */
/* أدوات ترتيب (sort_order + أعلى/أسفل)                              */
/* ================================================================== */

/** جدول أبيض — يمنع SQL Injection عبر أسماء الجداول */
const SORTABLE_TABLES = [
    'pages'           => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'page_sections'   => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'products'        => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'projects'        => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'board_members'   => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'executives'      => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'reports'         => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'faqs'            => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'important_links' => ['col_sort' => 'sort_order', 'col_id' => 'id'],
    'slider'          => ['col_sort' => 'sort_order', 'col_id' => 'id'],
];

function move_item(string $table, int $id, string $direction): bool
{
    if (!isset(SORTABLE_TABLES[$table])) return false;
    $col = SORTABLE_TABLES[$table]['col_sort'];

    $cur = fetchOne("SELECT id, `$col` AS so FROM `$table` WHERE id = ?", [$id]);
    if (!$cur) return false;

    $cmp  = ($direction === 'up') ? '<' : '>';
    $ord  = ($direction === 'up') ? 'DESC' : 'ASC';
    $neighbor = fetchOne(
        "SELECT id, `$col` AS so FROM `$table` WHERE `$col` $cmp ? OR (`$col` = ? AND id $cmp ?)
         ORDER BY `$col` $ord, id $ord LIMIT 1",
        [$cur['so'], $cur['so'], $id]
    );
    if (!$neighbor) return false;

    // تبديل قيم sort_order (أو إزاحة بسيطة إذا تساوت)
    if ((int)$cur['so'] === (int)$neighbor['so']) {
        $newSo = ($direction === 'up') ? ((int)$cur['so'] - 1) : ((int)$cur['so'] + 1);
        q("UPDATE `$table` SET `$col` = ? WHERE id = ?", [$newSo, $id]);
    } else {
        q("UPDATE `$table` SET `$col` = ? WHERE id = ?", [$neighbor['so'], $id]);
        q("UPDATE `$table` SET `$col` = ? WHERE id = ?", [$cur['so'], $neighbor['id']]);
    }
    return true;
}

function next_sort(string $table): int
{
    if (!isset(SORTABLE_TABLES[$table])) return 0;
    $col = SORTABLE_TABLES[$table]['col_sort'];
    return (int)fetchValue("SELECT COALESCE(MAX(`$col`),0)+1 FROM `$table`") ;
}

/** حذف صورة قديمة من uploads إن كانت مسجلة هناك */
function delete_upload(?string $relPath): void
{
    if (!$relPath) return;
    if (!str_starts_with($relPath, 'uploads/')) return;
    $full = realpath(ROOT_PATH . '/' . $relPath);
    if ($full && str_starts_with($full, realpath(UPLOAD_PATH) ?: '')) {
        @unlink($full);
    }
}

/* ================================================================== */
/* رؤوس الأمان                                                        */
/* ================================================================== */

function emit_security_headers(): void
{
    if (headers_sent()) return;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header(
        "Content-Security-Policy: default-src 'self'; " .
        "img-src 'self' data: https:; style-src 'self' 'unsafe-inline'; " .
        "script-src 'self' 'unsafe-inline'; font-src 'self' data:; " .
        "connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
    );
}
