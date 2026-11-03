<?php
/**
 * صفحة المحتوى القائمة على page_sections حسب slug الصفحة.
 * تُستخدم لصفحات: objectives / director / how-to-apply / file-path عبر المتغير $SECTION_SLUG.
 */

declare(strict_types=1);

function load_page_sections(string $slug): array
{
    require_once ROOT_PATH . '/includes/functions.php';
    $page = fetchOne('SELECT * FROM pages WHERE slug = ?', [$slug]); // prepared — منع IDOR
    if (!$page) {
        return ['page' => null, 'sections' => []];
    }
    $sections = fetchAll(
        'SELECT ps.* FROM page_sections ps WHERE ps.page_id = ? ORDER BY ps.sort_order ASC, ps.id ASC',
        [(int)$page['id']]
    );
    return ['page' => $page, 'sections' => $sections];
}

function render_sections_intro(array $data, string $heroText = ''): void
{
    ?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <span aria-current="page"><?= e($data['page'] ? $data['page']['title'] : 'صفحة') ?></span>
    </nav>
    <h1><?= e($data['page'] ? $data['page']['title'] : 'صفحة غير موجودة') ?></h1>
    <?php if ($heroText): ?><p><?= e($heroText) ?></p><?php endif; ?>
  </div>
</div>
    <?php
}

function render_accordion(array $items, string $prefix): void
{
    // Accordion: يظهر قسم واحد ويُخفى الباقي + ARIA كامل
    ?>
<div class="accordion" data-accordion role="list">
  <?php foreach ($items as $i => $it):
      $tid = $prefix . '-t' . $i;
      $pid = $prefix . '-p' . $i; ?>
    <div class="acc-item" role="listitem">
      <h3 style="margin:0">
        <button type="button" class="acc-trigger" id="<?= e($tid) ?>"
                aria-expanded="false" aria-controls="<?= e($pid) ?>">
          <span><?= e($it['title']) ?></span>
          <svg class="acc-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.5"/></svg>
        </button>
      </h3>
      <div class="acc-panel" id="<?= e($pid) ?>" role="region" aria-labelledby="<?= e($tid) ?>" data-open="false">
        <?php if (!empty($it['image'])): ?>
          <img src="<?= e(upload_url($it['image'], 'sections', 'no-image.svg')) ?>" alt="<?= e($it['title']) ?>" loading="lazy">
        <?php endif; ?>
        <?= clean_html($it['content_html']) ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
    <?php
}
