<?php
/** تواصل معنا — العنوان، الهواتف tel:، البريد mailto: */
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$email   = setting('email', '');
$phone1  = setting('phone1', '');
$phone2  = setting('phone2', '');
$address = setting('address', '');

$PAGE_TITLE = 'تواصل معنا';
$ACTIVE_NAV = 'contact';
$PAGE_DESC = 'بيانات التواصل مع الصندوق: العنوان، الهواتف، البريد الإلكتروني.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <span aria-current="page">تواصل معنا</span>
    </nav>
    <h1>تواصل معنا</h1>
    <p>نسعد بخدمتك على مدار ساعات العمل الرسمية.</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <div class="grid grid-3" role="list" aria-label="قنوات التواصل">
    <?php if ($address): ?>
    <div class="card content-block" role="listitem" style="text-align:center;padding:1.6rem">
      <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--c-primary)" stroke-width="1.8" aria-hidden="true" style="margin-inline:auto">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
      </svg>
      <h2 style="font-size:1.05rem">العنوان</h2>
      <p style="color:var(--c-muted);margin:0"><?= e($address) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($phone1 || $phone2): ?>
    <div class="card content-block" role="listitem" style="text-align:center;padding:1.6rem">
      <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--c-primary)" stroke-width="1.8" aria-hidden="true" style="margin-inline:auto">
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
      </svg>
      <h2 style="font-size:1.05rem">الهاتف</h2>
      <p style="margin:0;display:grid;gap:.3rem">
        <?php if ($phone1): ?><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone1)) ?>" dir="ltr" style="font-weight:700"><?= e($phone1) ?></a><?php endif; ?>
        <?php if ($phone2): ?><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone2)) ?>" dir="ltr" style="font-weight:700"><?= e($phone2) ?></a><?php endif; ?>
      </p>
    </div>
    <?php endif; ?>

    <?php if ($email): ?>
    <div class="card content-block" role="listitem" style="text-align:center;padding:1.6rem">
      <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--c-primary)" stroke-width="1.8" aria-hidden="true" style="margin-inline:auto">
        <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
      </svg>
      <h2 style="font-size:1.05rem">البريد الإلكتروني</h2>
      <p style="margin:0"><a href="mailto:<?= e($email) ?>" dir="ltr" style="font-weight:700"><?= e($email) ?></a></p>
      <small style="color:var(--c-muted)">يفتح برنامج البريد لديك مباشرة</small>
    </div>
    <?php endif; ?>
  </div>

  <div style="text-align:center;margin-top:2rem;display:flex;gap:.8rem;justify-content:center;flex-wrap:wrap">
    <a class="btn btn-primary" href="<?= url('pages/suggestions.php') ?>">إرسال مقترح أو شكوى</a>
    <a class="btn btn-outline" href="<?= url('pages/faq.php') ?>">الأسئلة المتكررة</a>
  </div>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
