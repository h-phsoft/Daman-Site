<?php
/** الأسئلة المتكررة — Accordion من faqs مع جواب HTML */
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$faqs = fetchAll('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');

$PAGE_TITLE = 'الأسئلة المتكررة';
$ACTIVE_NAV = '';
$PAGE_DESC = 'إجابات عن أكثر الأسئلة شيوعًا حول خدمات الصندوق.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <span aria-current="page">الأسئلة المتكررة</span>
    </nav>
    <h1>الأسئلة المتكررة</h1>
    <p>لم تجد إجابتك؟ تواصل معنا مباشرة.</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <?php if (!$faqs): ?>
    <p>لا توجد أسئلة منشورة حاليًا.</p>
  <?php else: ?>
    <div class="accordion" data-accordion role="list">
      <?php foreach ($faqs as $i => $f): ?>
        <div class="acc-item" role="listitem">
          <h2 style="margin:0;font-size:1rem">
            <button type="button" class="acc-trigger" id="faq-t<?= $i ?>"
                    aria-expanded="false" aria-controls="faq-p<?= $i ?>">
              <span><?= e($f['question']) ?></span>
              <svg class="acc-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.5"/></svg>
            </button>
          </h2>
          <div class="acc-panel" id="faq-p<?= $i ?>" role="region" aria-labelledby="faq-t<?= $i ?>" data-open="false">
            <?= clean_html($f['answer_html']) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <p style="text-align:center;margin-top:2rem">
    <a class="btn btn-primary" href="<?= url('pages/contact.php') ?>">تواصل معنا</a>
  </p>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
