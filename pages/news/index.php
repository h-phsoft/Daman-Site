<?php
/** الأخبار — قائمة تنازلية بالتاريخ: صورة + عنوان + تاريخ + مقتطف + رابط */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$total   = (int)fetchValue('SELECT COUNT(*) FROM news WHERE is_active = 1');
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min($page, $pages);
$offset  = ($page - 1) * $perPage;

// LIMIT/OFFSET عبر قيم صحيحة مثبّتة (bound parameters) لدعم Shared Hosting
$stmt = db()->prepare('SELECT * FROM news WHERE is_active = 1 ORDER BY publish_date DESC, id DESC LIMIT :lim OFFSET :off');
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

$PAGE_TITLE = 'الأخبار';
$ACTIVE_NAV = 'news';
$PAGE_DESC = 'أحدث أخبار وقرارات الصندوق.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <span aria-current="page">الأخبار</span>
    </nav>
    <h1>الأخبار</h1>
    <p>آخر مستجدات الصندوق وإعلاناته.</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <?php if (!$items): ?>
    <p>لا توجد أخبار منشورة حاليًا.</p>
  <?php else: ?>
    <?php foreach ($items as $n):
        $excerpt = trim(html_entity_decode(strip_tags((string)$n['excerpt_html']), ENT_QUOTES, 'UTF-8'));
    ?>
      <article class="news-row">
        <a class="thumb" href="<?= url('pages/news/article.php?slug=' . urlencode($n['slug'])) ?>" aria-hidden="true" tabindex="-1">
          <img src="<?= e(upload_url($n['image'], 'news', 'placeholder-news1.svg')) ?>" alt="" loading="lazy">
        </a>
        <div style="flex:1">
          <span class="meta-date" style="color:var(--c-accent-dark);font-size:.82rem;font-weight:600">
            <time datetime="<?= e($n['publish_date']) ?>"><?= e(ar_date($n['publish_date'])) ?></time>
          </span>
          <h2 style="margin:.2rem 0 .4rem;font-size:1.15rem">
            <a href="<?= url('pages/news/article.php?slug=' . urlencode($n['slug'])) ?>"><?= e($n['title']) ?></a>
          </h2>
          <p style="color:var(--c-muted);margin:0 0 .6rem"><?= e(mb_substr($excerpt, 0, 180)) ?><?= mb_strlen($excerpt) > 180 ? '…' : '' ?></p>
          <a class="more" href="<?= url('pages/news/article.php?slug=' . urlencode($n['slug'])) ?>">اقرأ الخبر كاملًا</a>
        </div>
      </article>
    <?php endforeach; ?>

    <?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="التنقل بين الصفحات" style="display:flex;gap:.5rem;justify-content:center;margin-top:1.6rem;flex-wrap:wrap">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if ($i === $page): ?>
          <span class="badge badge-on" style="padding:.5rem .9rem;font-size:.95rem" aria-current="page"><?= $i ?></span>
        <?php else: ?>
          <a class="btn btn-outline btn-sm" href="<?= url('pages/news/index.php?page=' . $i) ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
