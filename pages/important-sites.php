<?php
/** المواقع الهامة — بطاقات (صورة + عنوان) تفتح الرابط في تبويب جديد بأمان */
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$links = fetchAll('SELECT * FROM important_links WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');

$PAGE_TITLE = 'مواقع هامة';
$ACTIVE_NAV = '';
$PAGE_DESC = 'روابط المواقع الحكومية والشركاء المرتبطة بخدمات الصندوق.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <span aria-current="page">مواقع هامة</span>
    </nav>
    <h1>مواقع هامة</h1>
    <p>روابط مختصرة لجهات وشركاء ذوو صلة — تُفتح في تبويب جديد.</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <?php if (!$links): ?>
    <p>لا توجد روابط منشورة حاليًا.</p>
  <?php else: ?>
    <ul class="grid grid-4" role="list" style="list-style:none;padding:0;margin:0" aria-label="قائمة المواقع الهامة">
      <?php foreach ($links as $l): ?>
        <li>
          <a class="card" href="<?= e($l['url']) ?>" target="_blank" rel="noopener noreferrer"
             aria-label="<?= e($l['title']) ?> — يفتح في تبويب جديد">
            <div class="thumb" style="aspect-ratio:4/3;display:grid;place-items:center;background:#eef2f0">
              <img src="<?= e(upload_url($l['image'], 'img/placeholder-site1.svg')) ?>" alt="<?= e($l['title']) ?>" loading="lazy"
                   style="max-height:110px;width:auto">
            </div>
            <div class="card-body">
              <h3 style="font-size:.98rem"><?= e($l['title']) ?></h3>
              <span class="desc" dir="ltr" style="font-size:.78rem"><?= e(parse_url($l['url'], PHP_URL_HOST) ?: $l['url']) ?> ↗</span>
            </div>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
