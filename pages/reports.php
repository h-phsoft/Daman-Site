<?php
/** التقارير السنوية — عنوان + رابط تحميل الملف */
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$reports = fetchAll('SELECT * FROM reports WHERE is_active = 1 ORDER BY sort_order ASC, publish_date DESC');

$PAGE_TITLE = 'التقارير السنوية';
$ACTIVE_NAV = 'reports';
$PAGE_DESC = 'التقارير السنوية الصادرة عن الصندوق بصيغة PDF.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <span aria-current="page">التقارير السنوية</span>
    </nav>
    <h1>التقارير السنوية</h1>
    <p>أداء الصندوق وإنجازاته عامًا بعد عام.</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <?php if (!$reports): ?>
    <p>لا توجد تقارير منشورة حاليًا.</p>
  <?php else: ?>
    <ul class="grid grid-3" role="list" style="list-style:none;padding:0;margin:0" aria-label="قائمة التقارير السنوية">
      <?php foreach ($reports as $r):
          // التحقق من وجود الملف فعليًا قبل عرض الرابط (منع كسر الروابط)
          $fileExists = is_file(ROOT_PATH . '/' . $r['file_path']);
      ?>
        <li class="card">
          <div class="card-body" style="gap:.7rem">
            <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="var(--c-danger)" stroke-width="1.6" aria-hidden="true" style="margin-top:.6rem">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>
              <text x="8" y="18" font-size="6" fill="var(--c-danger)" stroke="none" font-family="Arial">PDF</text>
            </svg>
            <h3><?= e($r['title']) ?></h3>
            <p class="desc"><time datetime="<?= e($r['publish_date']) ?>"><?= e(ar_date($r['publish_date'])) ?></time></p>
            <?php if ($fileExists): ?>
              <a class="btn btn-primary btn-sm" href="<?= e(url($r['file_path'])) ?>" download target="_blank" rel="noopener">تحميل التقرير</a>
            <?php else: ?>
              <span class="badge badge-off">الملف غير متوفر حاليًا</span>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
