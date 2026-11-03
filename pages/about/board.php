<?php
/** مجلس الإدارة — فقرة تمهيدية + شبكة بطاقات board_members */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$members = fetchAll('SELECT * FROM board_members WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$intro = setting('board_intro', 'تولّى القيادة العليا للصندوق نخبة من أصحاب المعالي والسعادة ذوي الخبرة في قطاعات الإسكان والتمويل والتنظيم، بما يضمن حوكمة رشيدة واتخاذ قرارات استراتيجية تخدم المستفيدين.');

$PAGE_TITLE = 'مجلس الإدارة';
$ACTIVE_NAV = 'about';
$PAGE_DESC = 'أعضاء مجلس إدارة الصندوق ونبذاتهم الرسمية.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <a href="<?= url('pages/about/objectives.php') ?>">من نحن</a> / <span aria-current="page">مجلس الإدارة</span>
    </nav>
    <h1>مجلس الإدارة</h1>
    <p>القيادة الاستراتيجية للصندوق</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <div class="rich-content content-block">
    <h2 class="sr-only" style="position:absolute;inset-inline-start:-9999px">نبذة المجلس</h2>
    <p><?= e($intro) ?></p>
  </div>

  <?php if (!$members): ?>
    <p>لا يوجد أعضاء منشورون حاليًا.</p>
  <?php else: ?>
    <ul class="grid grid-3" role="list" style="list-style:none;padding:0;margin:0" aria-label="أعضاء مجلس الإدارة">
      <?php foreach ($members as $m): ?>
        <li class="card person-card">
          <div class="avatar">
            <img src="<?= e(upload_url($m['image'], 'board', 'placeholder-member1.svg')) ?>" alt="صورة <?= e($m['name']) ?>" loading="lazy">
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
