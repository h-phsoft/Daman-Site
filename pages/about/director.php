<?php
/** كلمة المدير العام — slug='director' مع صورة بارزة */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__) . '/_sections_helper.php';

$data = load_page_sections('director');
$PAGE_TITLE = $data['page'] ? $data['page']['title'] : 'كلمة المدير العام';
$ACTIVE_NAV = 'about';
$PAGE_DESC = 'كلمة ترحيبية من المدير العام للصندوق.';
require ROOT_PATH . '/includes/header.php';

render_sections_intro($data, 'رسالة المدير العام إلى زوار الموقع والمستفيدين.');
?>
<div class="container section" style="padding-top:0">
  <?php if (!$data['page']): ?>
    <div class="alert alert-error">الصفحة غير متاحة حاليًا.</div>
  <?php else: ?>
    <div class="rich-content">
      <?php foreach ($data['sections'] as $sec): ?>
        <section class="content-block" aria-labelledby="dir-<?= (int)$sec['id'] ?>">
          <div style="display:flex;gap:1.5rem;flex-wrap:wrap;align-items:flex-start">
            <?php if (!empty($sec['image'])): ?>
              <img src="<?= e(upload_url($sec['image'], 'sections', 'placeholder-director.svg')) ?>"
                   alt="صورة <?= e($sec['title']) ?>"
                   style="width:200px;height:200px;object-fit:cover;border-radius:50%;border:5px solid var(--c-primary-light);box-shadow:var(--shadow)"
                   loading="lazy">
            <?php endif; ?>
            <div style="flex:1;min-width:260px">
              <h2 id="dir-<?= (int)$sec['id'] ?>" style="margin-top:0"><?= e($sec['title']) ?></h2>
              <?= clean_html($sec['content_html']) ?>
            </div>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
