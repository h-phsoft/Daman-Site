<?php
/** المنتجات — كل المنتجات النشطة: عنوان + صورة + نص منسّق كامل */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$products = fetchAll('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');

$PAGE_TITLE = 'منتجاتنا';
$ACTIVE_NAV = 'products';
$PAGE_DESC = 'المنتجات التمويلية والسكنية التي يقدمها الصندوق.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <span aria-current="page">منتجاتنا</span>
    </nav>
    <h1>منتجاتنا</h1>
    <p>حلول سكنية وتمويلية متكاملة مصمّمة لاحتياجاتك.</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <?php if (!$products): ?>
    <p>لا توجد منتجات منشورة حاليًا.</p>
  <?php else: ?>
    <div class="rich-content">
      <?php foreach ($products as $p): ?>
        <article class="content-block" aria-labelledby="prod-<?= (int)$p['id'] ?>">
          <div style="display:flex;gap:1.4rem;flex-wrap:wrap;align-items:flex-start">
            <img src="<?= e(upload_url($p['image'], 'img/placeholder-product1.svg')) ?>"
                 alt="<?= e($p['title']) ?>" loading="lazy"
                 style="width:230px;height:150px;object-fit:cover;border-radius:12px;box-shadow:var(--shadow)">
            <div style="flex:1;min-width:260px">
              <h2 id="prod-<?= (int)$p['id'] ?>" style="margin-top:0"><?= e($p['title']) ?></h2>
              <p style="color:var(--c-muted)"><?= e($p['short_desc']) ?></p>
              <?= clean_html($p['content_html']) ?>
              <a class="btn btn-primary btn-sm" href="<?= url('product.php?id=' . (int)$p['id']) ?>">التفاصيل الكاملة</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
