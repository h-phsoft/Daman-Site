<?php
/** الإدارة التنفيذية — نفس بنية مجلس الإدارة مع جدول executives وترتيب sort_order */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$execs = fetchAll('SELECT * FROM executives WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$intro = setting('executives_intro', 'يقود العمل التنفيذي في الصندوق فريق إداري متخصص يشرف على تنفيذ الاستراتيجيات وتشغيل البرامج والعمليات اليومية بكفاءة وجودة عالية.');

$PAGE_TITLE = 'الإدارة التنفيذية';
$ACTIVE_NAV = 'about';
$PAGE_DESC = 'الإدارة التنفيذية للصندوق ومناصبهم ونبذاتهم.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <a href="<?= url('pages/about/objectives.php') ?>">من نحن</a> / <span aria-current="page">الإدارة التنفيذية</span>
    </nav>
    <h1>الإدارة التنفيذية</h1>
    <p>الفريق المسؤول عن تنفيذ استراتيجيات الصندوق</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <div class="rich-content content-block">
    <p><?= e($intro) ?></p>
  </div>

  <?php if (!$execs): ?>
    <p>لا يوجد مسؤولون منشورون حاليًا.</p>
  <?php else: ?>
    <ul class="grid grid-3" role="list" style="list-style:none;padding:0;margin:0" aria-label="قادة الإدارة التنفيذية">
      <?php foreach ($execs as $m): ?>
        <li class="card person-card">
          <div class="avatar">
            <img src="<?= e(upload_url($m['image'], 'img/placeholder-exec1.svg')) ?>" alt="صورة <?= e($m['name']) ?>" loading="lazy">
          </div>
          <div class="card-body">
            <h3><?= e($m['name']) ?></h3>
            <p class="position"><?= e($m['position']) ?></p>
            <div class="bio"><?= clean_html($m['bio_html']) ?></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
