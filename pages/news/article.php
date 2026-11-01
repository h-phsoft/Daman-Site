<?php
/** صفحة الخبر — article.php?slug=... — حماية IDOR عبر slug + is_active */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$article = $slug !== ''
    ? fetchOne('SELECT * FROM news WHERE slug = ? AND is_active = 1', [$slug])
    : null;

if (!$article) {
    http_response_code(404);
    $PAGE_TITLE = 'الخبر غير موجود';
    require ROOT_PATH . '/includes/header.php';
    echo '<div class="container section"><div class="alert alert-error">عذرًا، الخبر المطلوب غير موجود أو تم إيقاف نشره.</div>'
       . '<p><a class="btn btn-primary" href="' . e(url('pages/news/index.php')) . '">العودة إلى الأخبار</a></p></div>';
    require ROOT_PATH . '/includes/footer.php';
    exit;
}

$recent = fetchAll('SELECT title, slug, publish_date FROM news WHERE is_active = 1 AND slug <> ? ORDER BY publish_date DESC LIMIT 5', [$slug]);

$PAGE_TITLE = $article['title'];
$ACTIVE_NAV = 'news';
$PAGE_DESC = mb_substr(strip_tags((string)$article['excerpt_html']), 0, 155);
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <a href="<?= url('pages/news/index.php') ?>">الأخبار</a> / <span aria-current="page"><?= e($article['title']) ?></span>
    </nav>
    <h1><?= e($article['title']) ?></h1>
    <p><time datetime="<?= e($article['publish_date']) ?>"><?= e(ar_date($article['publish_date'])) ?></time></p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <div style="display:grid;grid-template-columns:minmax(0,2.2fr) minmax(0,1fr);gap:2rem;align-items:start" class="article-layout">
    <article class="rich-content" style="max-width:none">
      <img src="<?= e(upload_url($article['image'], 'img/placeholder-news1.svg')) ?>"
           alt="<?= e($article['title']) ?>" style="width:100%;border-radius:12px;box-shadow:var(--shadow)">
      <div class="content-block" style="margin-top:1.4rem">
        <?= clean_html($article['content_html']) ?>
      </div>
      <p><a class="btn btn-outline btn-sm" href="<?= url('pages/news/index.php') ?>">← كل الأخبار</a></p>
    </article>

    <?php if ($recent): ?>
    <aside aria-labelledby="recentNews">
      <div class="content-block">
        <h2 id="recentNews" style="font-size:1.05rem;margin-top:0">أخبار أخرى</h2>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.8rem">
          <?php foreach ($recent as $r): ?>
            <li>
              <a href="<?= url('pages/news/article.php?slug=' . urlencode($r['slug'])) ?>" style="font-weight:600"><?= e($r['title']) ?></a>
              <small style="display:block;color:var(--c-muted)"><?= e(ar_date($r['publish_date'])) ?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </aside>
    <?php endif; ?>
  </div>
</div>
<style>@media(max-width:900px){.article-layout{grid-template-columns:1fr !important}}</style>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
