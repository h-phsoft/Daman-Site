<?php
/**
 * تسجيل الدخول إلى لوحة التحكم.
 */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . url('admin/index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($username === '' || $password === '') {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور.';
    } elseif (attempt_login($username, $password)) {
        // منع إعادة الإرسال بنفس next غير آمن — نستخدم قائمة بيضاء
        $next = basename((string)($_GET['next'] ?? ''));
        $allowedNext = ['index.php', 'news.php', 'products.php', 'pages.php', 'settings.php'];
        $target = in_array($next, $allowedNext, true) ? url('admin/' . $next) : url('admin/index.php');
        header('Location: ' . $target);
        exit;
    } else {
        $error = 'بيانات الدخول غير صحيحة أو تجاوزت عدد المحاولات المسموح. حاول بعد قليل.';
    }
}
if (!empty($_GET['expired'])) {
    $error = 'انتهت صلاحية جلستك لأسباب أمنية، يرجى تسجيل الدخول مجددًا.';
}
$siteName = setting('site_name', 'الصندوق');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl"<?= theme_html_class() ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>تسجيل الدخول | إدارة <?= e($siteName) ?></title>
<?= favicon_links() ?>
<?= theme_head_script() ?>
<link rel="stylesheet" href="<?= asset('vendor/fonts/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="login-wrap">
  <?= theme_toggle_button() ?>
  <main class="login-card" id="main-content" aria-labelledby="loginTitle">
    <div class="logo-center"><?= brand_logo('شعار ' . $siteName) ?></div>
    <h1 id="loginTitle" style="text-align:center;font-size:1.25rem">دخول لوحة التحكم</h1>
    <p style="text-align:center;color:var(--c-muted);font-size:.88rem;margin-top:0"><?= e($siteName) ?></p>

    <?php if ($error): ?>
      <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= url('admin/login.php' . (isset($_GET['next']) ? '?next=' . urlencode(basename((string)$_GET['next'])) : '')) ?>">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div>
          <label class="req" for="username">اسم المستخدم</label>
          <input type="text" id="username" name="username" autocomplete="username" required autofocus dir="ltr">
        </div>
        <div>
          <label class="req" for="password">كلمة المرور</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required dir="ltr">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">دخول</button>
      </div>
    </form>
    <p style="text-align:center;margin-top:1.2rem"><a href="<?= url('') ?>">← العودة إلى الموقع</a></p>
  </main>
</body>
</html>
