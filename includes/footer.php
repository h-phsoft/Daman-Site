<?php
/**
 * التذييل المشترك — موحّد لكل الصفحات.
 */
$siteName = $siteName ?? setting('site_name', 'الصندوق');
$email    = setting('email', '');
$socials  = [
    'linkedin'  => ['label' => 'LinkedIn',  'icon' => 'linkedin'],
    'facebook'  => ['label' => 'Facebook',  'icon' => 'facebook'],
    'twitter'   => ['label' => 'X',         'icon' => 'x'],
    'instagram' => ['label' => 'Instagram', 'icon' => 'instagram'],
    'youtube'   => ['label' => 'YouTube',   'icon' => 'youtube'],
];
?>
</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a class="brand" href="<?= url('') ?>">
        <img src="<?= e(upload_url(setting('logo') ?: null, 'img/logo.svg')) ?>"
             alt="شعار <?= e($siteName) ?>" width="52" height="52">
        <strong><?= e($siteName) ?></strong>
      </a>
      <p class="footer-about"><?= e(setting('footer_about', '')) ?></p>
    </div>

    <nav class="footer-links" aria-label="روابط التذييل">
      <h2 class="footer-title">روابط سريعة</h2>
      <ul>
        <li><a href="<?= url('pages/important-sites.php') ?>">مواقع هامة</a></li>
        <li><a href="<?= url('pages/suggestions.php') ?>">مقترحات وشكاوى</a></li>
        <li><a href="<?= url('pages/faq.php') ?>">أسئلة متكررة</a></li>
        <li><a href="<?= url('pages/contact.php') ?>">تواصل معنا</a></li>
      </ul>
    </nav>

    <div class="footer-contact">
      <h2 class="footer-title">تواصل</h2>
      <?php if ($email): ?>
        <p><a href="mailto:<?= e($email) ?>" dir="ltr"><?= e($email) ?></a></p>
      <?php endif; ?>
      <ul class="social-icons" aria-label="حسابات التواصل الاجتماعي">
        <?php foreach ($socials as $key => $s): $link = setting($key); if (!$link) continue; ?>
          <li>
            <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer"
               aria-label="<?= e($s['label']) ?>" title="<?= e($s['label']) ?>">
              <svg width="20" height="20" aria-hidden="true"><use href="<?= asset('vendor/icons/social.svg#' . $s['icon']) ?>"></use></svg>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      <p>© <?= date('Y') ?> <?= e($siteName) ?> — جميع الحقوق محفوظة.</p>
    </div>
  </div>
</footer>

<script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
