<?php
/**
 * الصفحة الرئيسية: سلايدر + منتجات + آخر الأخبار.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$slides = fetchAll('SELECT * FROM slider WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$products = fetchAll('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$latestNews = fetchAll('SELECT * FROM news WHERE is_active = 1 ORDER BY publish_date DESC, id DESC LIMIT 3');

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

<!-- ===================== آخر الأخبار (آخر 3 أخبار بنفس عرض صفحة الأخبار) ===================== -->
<?php if ($latestNews): ?>
<section class="section" style="background:var(--c-surface)" aria-labelledby="newsHeading">
  <div class="container">
    <div class="section-head">
      <h2 id="newsHeading">آخر الأخبار</h2>
      <p>تابع أحدث المستجدات والقرارات من الصندوق.</p>
    </div>
    <?php foreach ($latestNews as $n):
        $newsUrl = url('pages/news/article.php?slug=' . urlencode($n['slug']));
        $excerpt = trim(html_entity_decode(strip_tags((string)$n['excerpt_html']), ENT_QUOTES, 'UTF-8'));
        $short   = mb_substr($excerpt, 0, 180);
    ?>
      <article class="news-row">
        <a class="thumb" href="<?= $newsUrl ?>" aria-hidden="true" tabindex="-1">
          <img src="<?= e(upload_url($n['image'], 'img/placeholder-news1.svg')) ?>" alt="" loading="lazy">
        </a>
        <div style="flex:1">
          <span class="meta-date" style="color:var(--c-accent-dark);font-size:.82rem;font-weight:600">
            <time datetime="<?= e($n['publish_date']) ?>"><?= e(ar_date($n['publish_date'])) ?></time>
          </span>
          <h3 style="margin:.2rem 0 .4rem;font-size:1.15rem">
            <a href="<?= $newsUrl ?>"><?= e($n['title']) ?></a>
          </h3>
          <p style="color:var(--c-muted);margin:0 0 .6rem"><?= e($short) ?><?= mb_strlen($excerpt) > 180 ? '…' : '' ?></p>
          <a class="more" href="<?= $newsUrl ?>">اقرأ الخبر كاملًا</a>
        </div>
      </article>
    <?php endforeach; ?>
    <p style="text-align:center;margin-top:1rem">
      <a class="btn btn-outline" href="<?= url('pages/news/index.php') ?>">كل الأخبار</a>
    </p>
  </div>
</section>
<?php endif; ?>

<?php require ROOT_PATH . '/includes/footer.php'; ?>
