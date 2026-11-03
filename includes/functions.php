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
    return url(ASSETS_URL . '/' . ltrim($path, '/'));
}

/* ------------------------------------------------------------------ */
/* الملفات المرفوعة: قاعدة البيانات تخزّن اسم الملف فقط، والمجلد يأتي من */
/* UPLOAD_DIRS في config.php حسب نوع المحتوى ($type).                 */
/* ------------------------------------------------------------------ */

/** اسم ملف نظيف من القيمة المخزّنة (يقبل القيم القديمة التي تحوي مسارًا). */
function upload_name(?string $stored): string
{
    $stored = trim((string)$stored);
    return $stored === '' ? '' : basename(str_replace('\\', '/', $stored));
}

/** المجلد النسبي لنوع المحتوى داخل مجلد الرفع. */
function upload_dir(string $type): string
{
    if (!isset(UPLOAD_DIRS[$type])) {
        throw new InvalidArgumentException('نوع رفع غير معرّف في config.php: ' . $type);
    }
    return UPLOAD_DIRS[$type];
}

/** المسار الكامل على القرص لملف مرفوع، أو '' إن لم يوجد اسم. */
function upload_path(?string $stored, string $type): string
{
    $name = upload_name($stored);
    return $name === '' ? '' : UPLOAD_PATH . '/' . upload_dir($type) . '/' . $name;
}

/** هل الملف المرفوع موجود فعليًا على القرص؟ */
function upload_exists(?string $stored, string $type): bool
{
    $p = upload_path($stored, $type);
    return $p !== '' && is_file($p);
}

/**
 * رابط ملف مرفوع حسب نوعه، أو صورة افتراضية من DEFAULT_IMG_DIR إن لم يوجد.
 * $fallback اسم ملف داخل مجلد الصور الافتراضية (مثل placeholder-news1.svg).
 */
function upload_url(?string $stored, string $type, string $fallback = 'no-image.svg'): string
{
    if ($stored !== null && preg_match('#^https?://#i', trim($stored))) {
        return trim($stored); // رابط خارجي كامل
    }
    if (upload_exists($stored, $type)) {
        return url(UPLOAD_URL . '/' . upload_dir($type) . '/' . rawurlencode(upload_name($stored)));
    }
    return asset(DEFAULT_IMG_DIR . '/' . $fallback);
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
 * @param string $type    نوع المحتوى — مفتاح في UPLOAD_DIRS (config.php) يحدد المجلد
 * @param array  $allowedMimes map: mime => extension allowed
 * @return array [ok(bool), fileNameOrError(string)] — اسم الملف فقط بدون مسار
 */
function upload_file(string $field, string $type, array $allowedMimes, int $maxSize = UPLOAD_MAX_SIZE): array
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

    $dir = UPLOAD_PATH . '/' . upload_dir($type);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        return [false, 'تعذّر حفظ الملف على الخادم.'];
    }
    @chmod($dest, 0644);

    return [true, $name]; // اسم الملف فقط — المجلد من UPLOAD_DIRS
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

/** حذف ملف مرفوع قديم حسب نوعه (داخل مجلد الرفع فقط). */
function delete_upload(?string $stored, string $type): void
{
    $p = upload_path($stored, $type);
    if ($p === '') return;
    $full = realpath($p);
    $root = realpath(UPLOAD_PATH);
    if ($full && $root && str_starts_with($full, $root . DIRECTORY_SEPARATOR)) {
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

/* ==========================================================================
   النمط الفاتح / الداكن — بنفس أسلوب بوابة LRGC
   الافتراضي داكن، ويُحفظ اختيار الزائر في المتصفح (localStorage + Cookie).
   ========================================================================== */

/** سكربت يُوضع داخل <head> قبل ملفات CSS ليُطبَّق النمط قبل الرسم (بدون وميض). */
function theme_head_script(): string
{
    return <<<'HTML'
<script>
(function () {
  var t = null;
  try { t = localStorage.getItem('site_theme'); } catch (e) {}
  if (!t) { var m = document.cookie.match(/(?:^|; )site_theme=([^;]+)/); t = m ? m[1] : null; }
  if (t === 'light') document.documentElement.classList.add('light');
  window.toggleTheme = function () {
    var isLight = document.documentElement.classList.toggle('light');
    var v = isLight ? 'light' : 'dark';
    try { localStorage.setItem('site_theme', v); } catch (e) {}
    document.cookie = 'site_theme=' + v + ';path=/;max-age=31536000;SameSite=Lax';
    document.querySelectorAll('.theme-toggle').forEach(function (b) {
      b.setAttribute('aria-pressed', isLight ? 'false' : 'true');
    });
  };
  document.addEventListener('DOMContentLoaded', function () {
    var dark = !document.documentElement.classList.contains('light');
    document.querySelectorAll('.theme-toggle').forEach(function (b) {
      b.setAttribute('aria-pressed', dark ? 'true' : 'false');
      b.addEventListener('click', window.toggleTheme);
    });
  });
})();
</script>
HTML;
}

/** زر التبديل: قمر في النمط الداكن، شمس في النمط الفاتح. */
function theme_toggle_button(): string
{
    return <<<'HTML'
<button type="button" class="theme-toggle" aria-pressed="true" aria-label="النمط الداكن" title="تبديل النمط فاتح / داكن">
  <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9z"/></svg>
  <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
</button>
HTML;
}

/**
 * الشعار حسب النمط: logo.png للفاتح و logow.png للداكن (كما في البوابة).
 * إن رفع المدير شعارًا مخصصًا من الإعدادات يُعرض هو في النمطين.
 */
function brand_logo(string $alt, string $class = ''): string
{
    $custom = (string) setting('logo', '');
    $cls = trim('brand-logo ' . $class);
    if (upload_exists($custom, 'logo')) {
        return '<img class="' . e($cls) . '" src="' . e(upload_url($custom, 'logo')) . '" alt="' . e($alt) . '">';
    }
    return '<img class="' . e($cls . ' logo-light') . '" src="' . e(asset(DEFAULT_IMG_DIR . '/logo.png')) . '" alt="' . e($alt) . '" width="481" height="200">'
         . '<img class="' . e($cls . ' logo-dark') . '" src="' . e(asset(DEFAULT_IMG_DIR . '/logow.png')) . '" alt="' . e($alt) . '" width="492" height="200">';
}

/** أيقونة الموقع (favicon) وأيقونة أجهزة Apple — نفس أيقونات البوابة. */
function favicon_links(): string
{
    $fav = e(asset(DEFAULT_IMG_DIR . '/favicon.png'));
    return '<link rel="icon" type="image/png" sizes="64x64" href="' . $fav . '">' . "\n"
         . '<link rel="shortcut icon" type="image/png" href="' . $fav . '">' . "\n"
         . '<link rel="apple-touch-icon" sizes="180x180" href="' . e(asset(DEFAULT_IMG_DIR . '/apple-touch-icon.png')) . '">';
}
