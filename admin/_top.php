<?php
/**
 * لوحة التحكم — الترويسة المشتركة الداخلية.
 * تتطلب: $ADMIN_TITLE, $ADMIN_ACTIVE (اسم الملف بدون امتداد)
 */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$u = current_user();
$adminTitle  = $ADMIN_TITLE ?? 'لوحة التحكم';
$adminActive = $ADMIN_ACTIVE ?? '';
$logoPath    = setting('logo', 'assets/img/logo.svg');

$navItems = [
    'index'      => ['الرئيسية (Dashboard)', 'grid'],
    'slider'     => ['السلايدر', 'image'],
    'pages'      => ['الصفحات', 'file'],
    'products'   => ['المنتجات', 'box'],
    'projects'   => ['المشاريع المستهدفة', 'target'],
    'board'      => ['مجلس الإدارة', 'users'],
    'executives' => ['الإدارة التنفيذية', 'user'],
    'news'       => ['الأخبار', 'news'],
    'reports'    => ['التقارير السنوية', 'doc'],
    'faqs'       => ['الأسئلة المتكررة', 'help'],
    'links'      => ['المواقع الهامة', 'link'],
    'messages'   => ['الرسائل الواردة', 'mail'],
    'settings'   => ['إعدادات الموقع', 'gear'],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($adminTitle) ?> | إدارة <?= e(setting('site_name', 'الصندوق')) ?></title>
<link rel="icon" type="image/svg+xml" href="<?= e(upload_url($logoPath ?: null, 'img/logo.svg')) ?>">
<link rel="stylesheet" href="<?= asset('vendor/fonts/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<script src="<?= asset('vendor/tinymce/tinymce.min.js') ?>" defer></script>
<script src="<?= asset('js/admin.js') ?>" defer></script>
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-side">
    <a class="brand-mini" href="<?= url('admin/') ?>">
      <img src="<?= e(upload_url($logoPath ?: null, 'img/logo.svg')) ?>" alt="الشعار">
      <strong>إدارة<br><?= e(setting('site_name', 'الصندوق')) ?></strong>
    </a>
    <nav aria-label="قائمة لوحة التحكم">
      <ul role="menubar">
        <?php foreach ($navItems as $key => [$label, $icon]): ?>
          <li role="none">
            <a role="menuitem" href="<?= url('admin/' . $key . '.php') ?>"
               class="<?= $adminActive === $key ? 'current' : '' ?>"
               <?= $adminActive === $key ? 'aria-current="page"' : '' ?>>
              <svg width="16" height="16" aria-hidden="true"><use href="<?= asset('vendor/icons/ui.svg#' . $icon) ?>"></use></svg>
              <?= e($label) ?>
            </a>
          </li>
        <?php endforeach; ?>
        <li role="none"><a href="<?= url('') ?>" target="_blank" rel="noopener">
          <svg width="16" height="16" aria-hidden="true"><use href="<?= asset('vendor/icons/ui.svg#external') ?>"></use></svg>
          عرض الموقع</a></li>
        <li role="none"><a href="<?= url('admin/logout.php') ?>" style="color:#f2b632">
          <svg width="16" height="16" aria-hidden="true"><use href="<?= asset('vendor/icons/ui.svg#logout') ?>"></use></svg>
          تسجيل الخروج (<?= e($u['username'] ?? '') ?>)</a></li>
      </ul>
    </nav>
  </aside>
  <main class="admin-main" id="main-content">
    <div class="admin-topbar">
      <h1><?= e($adminTitle) ?></h1>
      <span style="color:var(--c-muted);font-size:.85rem">مرحبًا، <?= e($u['username'] ?? '') ?> — <?= date('Y-m-d') ?></span>
    </div>
    <?php foreach (flash_get() as $fl): ?>
      <div class="alert <?= $fl['type'] === 'error' ? 'alert-error' : 'alert-success' ?>" role="alert"><?= e($fl['msg']) ?></div>
    <?php endforeach; ?>
