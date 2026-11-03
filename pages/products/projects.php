<?php
/** المشاريع المستهدفة — نفس بنية المنتجات من جدول projects */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$projects = fetchAll('SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');

$PAGE_TITLE = 'المشاريع المستهدفة';
$ACTIVE_NAV = 'products';
$PAGE_DESC = 'المشاريع التطويرية والعمرانية المستهدفة من الصندوق.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <a href="<?= url('pages/products/index.php') ?>">منتجاتنا</a> / <span aria-current="page">المشاريع المستهدفة</span>
    </nav>
    <h1>المشاريع المستهدفة</h1>
    <p>مشاريع تطويرية وعمرانية نوعية يشارك فيها الصندوق.</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <?php if (!$projects): ?>
    <p>لا توجد مشاريع منشورة حاليًا.</p>
  <?php else: ?>
    <div class="rich-content">
      <?php foreach ($projects as $pr): ?>
        <article class="content-block" aria-labelledby="proj-<?= (int)$pr['id'] ?>">
          <div style="display:flex;gap:1.4rem;flex-wrap:wrap;align-items:flex-start">
            <img src="<?= e(upload_url($pr['image'], 'projects', 'placeholder-project1.svg')) ?>"
                 alt="<?= e($pr['title']) ?>" loading="lazy"
                 style="width:230px;height:150px;object-fit:cover;border-radius:12px;box-shadow:var(--shadow)">
            <div style="flex:1;min-width:260px">
              <h2 id="proj-<?= (int)$pr['id'] ?>" style="margin-top:0"><?= e($pr['title']) ?></h2>
              <?= clean_html($pr['content_html']) ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
