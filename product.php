<?php
/**
 * صفحة المنتج التفصيلية — product.php?id=...
 * حماية IDOR: معرف صحيح + منتج نشط فقط، وإلا 404.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(404);
    $PAGE_TITLE = 'المنتج غير موجود';
    require ROOT_PATH . '/includes/header.php';
    echo '<div class="container section"><div class="alert alert-error">المنتج المطلوب غير موجود أو الرابط غير صالح.</div>'
       . '<p><a class="btn btn-primary" href="' . e(url('pages/products/index.php')) . '">العودة إلى المنتجات</a></p></div>';
    require ROOT_PATH . '/includes/footer.php';
    exit;
}

// Prepared statement + شرط is_active = حماية IDOR
$product = fetchOne('SELECT * FROM products WHERE id = ? AND is_active = 1', [$id]);
if (!$product) {
    http_response_code(404);
    $PAGE_TITLE = 'المنتج غير موجود';
    require ROOT_PATH . '/includes/header.php';
    echo '<div class="container section"><div class="alert alert-error">عذرًا، هذا المنتج غير متاح حاليًا.</div>'
       . '<p><a class="btn btn-primary" href="' . e(url('pages/products/index.php')) . '">العودة إلى المنتجات</a></p></div>';
    require ROOT_PATH . '/includes/footer.php';
    exit;
}

$others = fetchAll('SELECT id, title FROM products WHERE id <> ? AND is_active = 1 ORDER BY sort_order ASC LIMIT 6', [$id]);

$PAGE_TITLE = $product['title'];
$ACTIVE_NAV = 'products';
$PAGE_DESC = mb_substr(strip_tags((string)$product['short_desc']), 0, 155);
require ROOT_PATH . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <a href="<?= url('pages/products/index.php') ?>">منتجاتنا</a> / <span aria-current="page"><?= e($product['title']) ?></span>
    </nav>
    <h1><?= e($product['title']) ?></h1>
    <p><?= e($product['short_desc']) ?></p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <article class="rich-content">
    <img src="<?= e(upload_url($product['image'], 'img/placeholder-product1.svg')) ?>"
         alt="<?= e($product['title']) ?>" style="max-height:420px;margin-inline:auto;width:auto">
    <div class="content-block" style="margin-top:1.6rem">
      <?= clean_html($product['content_html']) ?>
    </div>
  </article>

  <?php if ($others): ?>
  <aside aria-labelledby="relatedProducts" style="margin-top:2rem">
    <h2 id="relatedProducts" class="section-head" style="text-align:start;font-size:1.15rem">منتجات أخرى</h2>
    <div class="grid grid-3">
      <?php foreach ($others as $o): ?>
        <a class="card" href="<?= url('product.php?id=' . (int)$o['id']) ?>" style="text-align:center;padding:1rem;font-weight:700"><?= e($o['title']) ?></a>
      <?php endforeach; ?>
    </div>
  </aside>
  <?php endif; ?>
</div>

<?php require ROOT_PATH . '/includes/footer.php'; ?>
