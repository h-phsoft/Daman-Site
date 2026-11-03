<?php
/** الإحداث والأهداف — فقرات من page_sections (slug='objectives') */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__) . '/_sections_helper.php';

$data = load_page_sections('objectives');
$PAGE_TITLE = $data['page'] ? $data['page']['title'] : 'الإحداث والأهداف';
$ACTIVE_NAV = 'about';
$PAGE_DESC = 'نشأة الصندوق وأهدافه الاستراتيجية.';
require ROOT_PATH . '/includes/header.php';

render_sections_intro($data, 'تعرف على نشأة الصندوق ورؤيته وأهدافه الاستراتيجية.');
?>
<div class="container section" style="padding-top:0">
  <?php if (!$data['page']): ?>
    <div class="alert alert-error">الصفحة غير متاحة حاليًا.</div>
  <?php elseif (!$data['sections']): ?>
    <p class="rich-content">لا يوجد محتوى منشور بعد لهذه الصفحة.</p>
  <?php else: ?>
    <div class="rich-content">
      <?php foreach ($data['sections'] as $sec): ?>
        <section class="content-block" aria-labelledby="obj-<?= (int)$sec['id'] ?>">
          <h2 id="obj-<?= (int)$sec['id'] ?>"><?= e($sec['title']) ?></h2>
          <?php if (!empty($sec['image'])): ?>
            <img src="<?= e(upload_url($sec['image'], 'sections', 'no-image.svg')) ?>" alt="<?= e($sec['title']) ?>" loading="lazy">
          <?php endif; ?>
          <?= clean_html($sec['content_html']) ?>
        </section>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
