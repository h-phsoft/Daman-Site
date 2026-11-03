<?php
/**
 * الصفحة الرئيسية: سلايدر + منتجات + آخر الأخبار.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$slides = fetchAll('SELECT * FROM slider WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$products = fetchAll('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$latestNews = fetchAll('SELECT * FROM news WHERE is_active = 1 ORDER BY publish_date DESC, id DESC LIMIT 6');

$PAGE_TITLE = null; // اسم الموقع مباشرة
$ACTIVE_NAV = 'home';
$PAGE_DESC = setting('meta_description', '');
require ROOT_PATH . '/includes/header.php';
?>

<!-- ===================== السلايدر الرئيسي ===================== -->
<?php if ($slides): ?>
<section class="hero-slider" data-slider data-interval="5500" aria-roledescription="carousel" aria-label="أبرز خدمات الصندوق" tabindex="0">
  <div class="slides-track">
    <?php foreach ($slides as $i => $s): ?>
      <article class="slide<?= $i === 0 ? ' is-active' : '' ?>" role="group" aria-roledescription="slide"
               aria-label="شريحة <?= $i + 1 ?> من <?= count($slides) ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
        <img class="cover" src="<?= e(upload_url($s['image'], 'img/slide1.svg')) ?>" alt="<?= e($s['title'] ?: 'صورة ترويجية') ?>">
        <div class="slide-overlay">
          <!-- Overlay بخلفية دخانية شفافة مع Blur -->
          <div class="overlay-glass">
            <?php if ($s['title']): ?><h2><?= e($s['title']) ?></h2><?php endif; ?>
            <?php if ($s['subtitle']): ?><p><?= e($s['subtitle']) ?></p><?php endif; ?>
            <?php if ($s['cta_text'] && $s['cta_link']):
                $href = preg_match('#^https?://#i', $s['cta_link']) ? $s['cta_link'] : url($s['cta_link']); ?>
              <a class="btn" href="<?= e($href) ?>"><?= e($s['cta_text']) ?></a>
            <?php endif; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php if (count($slides) > 1): ?>
    <button type="button" class="slider-btn prev" aria-label="الشريحة السابقة">&#8250;</button>
    <button type="button" class="slider-btn next" aria-label="الشريحة التالية">&#8249;</button>
    <div class="slider-dots" role="tablist" aria-label="التنقل بين الشرائح"></div>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- ===================== فقرة المنتجات ===================== -->
<?php if ($products): ?>
<section class="section" aria-labelledby="productsHeading">
  <div class="container">
    <div class="section-head">
      <h2 id="productsHeading">منتجاتنا التمويلية والسكنية</h2>
      <p>حلول متكاملة مصمّمة لاحتياجات الأسرة — اختر منتجًا لعرض تفاصيله الكاملة.</p>
    </div>
    <div class="grid grid-3">
      <?php foreach ($products as $p): ?>
        <article class="card">
          <div class="thumb">
            <img src="<?= e(upload_url($p['image'], 'img/placeholder-product1.svg')) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
          </div>
          <div class="card-body">
            <h3><?= e($p['title']) ?></h3>
            <p class="desc"><?= e($p['short_desc']) ?></p>
            <a class="more" href="<?= url('product.php?id=' . (int)$p['id']) ?>" aria-label="التفاصيل الكاملة عن <?= e($p['title']) ?>">التفاصيل</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-top:1.6rem">
      <a class="btn btn-outline" href="<?= url('pages/products/index.php') ?>">استعراض كل المنتجات</a>
    </p>
  </div>
</section>
<?php endif; ?>

<!-- ===================== آخر الأخبار (سلايدر أفقي) ===================== -->
<?php if ($latestNews): ?>
<section class="section" style="background:var(--c-surface)" aria-labelledby="newsHeading">
  <div class="container">
    <div class="section-head">
      <h2 id="newsHeading">آخر الأخبار</h2>
      <p>تابع أحدث المستجدات والقرارات من الصندوق.</p>
    </div>
    <div class="news-scroller" role="region" aria-label="قائمة آخر الأخبار - تمرير أفقي" tabindex="0">
      <?php foreach ($latestNews as $n): ?>
        <article class="news-item">
          <div class="thumb">
            <img src="<?= e(upload_url($n['image'], 'img/placeholder-news1.svg')) ?>" alt="<?= e($n['title']) ?>" loading="lazy">
          </div>
          <div class="card-body">
            <span class="meta-date"><time datetime="<?= e($n['publish_date']) ?>"><?= e(ar_date($n['publish_date'])) ?></time></span>
            <h3><?= e($n['title']) ?></h3>
            <div class="desc"><?= clean_html($n['excerpt_html']) ?></div>
            <a class="more" href="<?= url('pages/news/article.php?slug=' . urlencode($n['slug'])) ?>">المزيد</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;margin-top:1rem">
      <a class="btn btn-outline" href="<?= url('pages/news/index.php') ?>">كل الأخبار</a>
    </p>
  </div>
</section>
<?php endif; ?>

<?php require ROOT_PATH . '/includes/footer.php'; ?>
