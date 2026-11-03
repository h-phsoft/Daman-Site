<?php
/**
 * الترويسة المشتركة — ثابتة في كل الصفحات العامة.
 * تتطلب المتغيرات: $PAGE_TITLE, $PAGE_DESC (اختياري), $ACTIVE_NAV (اختياري)
 */

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
emit_security_headers();

$siteName  = setting('site_name', 'الصندوق');
$tagline   = setting('site_tagline', '');
$pageDesc  = $PAGE_DESC ?? setting('meta_description', '');
$activeNav = $ACTIVE_NAV ?? '';

/** بنية القائمة الرئيسية حرفيًا كما هو مطلوب */
$menu = [
    ['key' => 'home',      'title' => 'الرئيسية',        'href' => url(''), 'sub' => []],
    ['key' => 'about',     'title' => 'من نحن',          'href' => '#', 'sub' => [
        ['title' => 'الإحداث والأهداف', 'href' => url('pages/about/objectives.php')],
        ['title' => 'كلمة المدير العام', 'href' => url('pages/about/director.php')],
        ['title' => 'مجلس الإدارة',     'href' => url('pages/about/board.php')],
        ['title' => 'الإدارة التنفيذية', 'href' => url('pages/about/executives.php')],
    ]],
    ['key' => 'products',  'title' => 'منتجاتنا',        'href' => '#', 'sub' => [
        ['title' => 'منتجاتنا',          'href' => url('pages/products/index.php')],
        ['title' => 'المشاريع المستهدفة', 'href' => url('pages/products/projects.php')],
    ]],
    ['key' => 'apply',     'title' => 'التقدم للخدمة',   'href' => '#', 'sub' => [
        ['title' => 'أسلوب التقدم للخدمة', 'href' => url('pages/apply/how-to-apply.php')],
        ['title' => 'مسار لملف',           'href' => url('pages/apply/file-path.php')],
    ]],
    ['key' => 'news',      'title' => 'الأخبار',          'href' => url('pages/news/index.php'), 'sub' => []],
    ['key' => 'reports',   'title' => 'التقارير السنوية', 'href' => url('pages/reports.php'), 'sub' => []],
    ['key' => 'contact',   'title' => 'تواصل معنا',       'href' => url('pages/contact.php'), 'sub' => []],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(page_title($PAGE_TITLE ?? null)) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>">
<meta property="og:title" content="<?= e($PAGE_TITLE ? ($PAGE_TITLE.' | '.$siteName) : $siteName) ?>">
<meta property="og:description" content="<?= e($pageDesc) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_SA">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<?= favicon_links() ?>
<?= theme_head_script() ?>
<link rel="stylesheet" href="<?= asset('vendor/fonts/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<a class="skip-link" href="#main-content">تخطّئ إلى المحتوى الرئيسي</a>

<header class="site-header" id="siteHeader">
  <div class="container header-inner">
    <a class="brand" href="<?= url('') ?>" aria-label="<?= e($siteName) ?>">
      <?= brand_logo('شعار ' . $siteName) ?>
      <span class="brand-text">
        <strong><?= e($siteName) ?></strong>
        <?php if ($tagline): ?><small><?= e($tagline) ?></small><?php endif; ?>
      </span>
    </a>

    <button class="nav-toggle" id="navToggle" type="button"
            aria-controls="primaryNav" aria-expanded="false" aria-label="فتح القائمة">
      <span class="bar"></span><span class="bar"></span><span class="bar"></span>
    </button>

    <nav id="primaryNav" class="primary-nav" aria-label="القائمة الرئيسية">
      <ul class="nav-list" role="menubar" aria-label="القائمة الرئيسية">
        <?php foreach ($menu as $item):
            $hasSub = !empty($item['sub']);
            $isActive = ($activeNav === $item['key']) ||
                        ($hasSub && in_array($activeNav, array_column($item['sub'], 'key') ?: [], true));
        ?>
        <li class="nav-item<?= $hasSub ? ' has-sub' : '' ?><?= $isActive ? ' active' : '' ?>" role="none">
          <?php if ($hasSub): ?>
            <a class="nav-link" href="<?= e($item['href']) ?>" role="menuitem"
               aria-haspopup="true" aria-expanded="false"
               <?= $isActive ? 'aria-current="true"' : '' ?>>
              <?= e($item['title']) ?>
              <svg class="chev" width="10" height="10" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="3"/></svg>
            </a>
            <ul class="sub-menu" role="menu" aria-label="<?= e($item['title']) ?>">
              <?php foreach ($item['sub'] as $sub): ?>
                <li role="none">
                  <a role="menuitem" href="<?= e($sub['href']) ?>"><?= e($sub['title']) ?></a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <a class="nav-link" href="<?= e($item['href']) ?>" role="menuitem"
               <?= $isActive ? 'aria-current="page"' : '' ?>><?= e($item['title']) ?></a>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <?= theme_toggle_button() ?>
  </div>
</header>

<main id="main-content">
