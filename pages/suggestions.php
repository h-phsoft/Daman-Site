<?php
/**
 * مقترحات وشكاوى — نموذج يُخزّن في contact_messages
 * CSRF + Validation + رسائل نجاح/خطأ.
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$old    = ['name' => '', 'email' => '', 'phone' => '', 'message' => '', 'type' => 'suggestion'];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require(); // يوقف الطلب عند فشل التوكن

    $old['name']    = trim((string)($_POST['name'] ?? ''));
    $old['email']   = trim((string)($_POST['email'] ?? ''));
    $old['phone']   = trim((string)($_POST['phone'] ?? ''));
    $old['message'] = trim((string)($_POST['message'] ?? ''));
    $old['type']    = in_array($_POST['type'] ?? '', ['suggestion', 'complaint'], true) ? $_POST['type'] : 'suggestion';

    if ($old['name'] === '' || mb_strlen($old['name']) < 3 || mb_strlen($old['name']) > 150) {
        $errors['name'] = 'الاسم مطلوب (3 إلى 150 حرفًا).';
    }
    if (!valid_email($old['email'])) {
        $errors['email'] = 'يرجى إدخال بريد إلكتروني صحيح.';
    }
    if (!valid_phone($old['phone'])) {
        $errors['phone'] = 'يرجى إدخال رقم جوال صحيح (مثال: 05xxxxxxxx).';
    }
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 3000) {
        $errors['message'] = 'نص الرسالة مطلوب (10 إلى 3000 حرف).';
    }
    // حماية من الإرسال المتكرر: نفس البريد خلال 60 ثانية
    if (!$errors) {
        $recent = fetchValue(
            'SELECT id FROM contact_messages WHERE email = ? AND created_at > (NOW() - INTERVAL 60 SECOND) LIMIT 1',
            [$old['email']]
        );
        if ($recent) {
            $errors['form'] = 'تم إرسال رسالتك مؤخرًا، يرجى الانتظار دقيقة قبل المحاولة مجددًا.';
        }
    }

    if (!$errors) {
        q(
            'INSERT INTO contact_messages (type, name, email, phone, message) VALUES (?, ?, ?, ?, ?)',
            [$old['type'], $old['name'], $old['email'], $old['phone'], $old['message']]
        );
        $success = true;
        $old = ['name' => '', 'email' => '', 'phone' => '', 'message' => '', 'type' => 'suggestion'];
    } else {
        flash_set('error', 'يرجى تصحيح الحقول المميزة بالأحمر.');
    }
}

$PAGE_TITLE = 'مقترحات وشكاوى';
$ACTIVE_NAV = '';
$PAGE_DESC = 'أرسل مقترحًا أو شكوى إلى الصندوق وسنقوم بالمتابعة.';
require ROOT_PATH . '/includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="مسار التنقل">
      <a href="<?= url('') ?>">الرئيسية</a> / <span aria-current="page">مقترحات وشكاوى</span>
    </nav>
    <h1>مقترحات وشكاوى</h1>
    <p>رأيك يهمنا — نلتزم بالرد والمتابعة خلال 5 أيام عمل.</p>
  </div>
</div>

<div class="container section" style="padding-top:0">
  <?php foreach (flash_get() as $fl): ?>
    <div class="alert <?= $fl['type'] === 'error' ? 'alert-error' : 'alert-success' ?>" role="alert"><?= e($fl['msg']) ?></div>
  <?php endforeach; ?>

  <?php if ($success): ?>
    <div class="alert alert-success" role="status">
      ✅ تم استلام رسالتك بنجاح، شكرًا لتواصلك مع <?= e(setting('site_name', 'الصندوق')) ?>.
    </div>
  <?php endif; ?>

  <form class="form-card" method="post" action="<?= url('pages/suggestions.php') ?>" novalidate aria-describedby="formHint">
    <?= csrf_field() ?>
    <p id="formHint" style="color:var(--c-muted);margin-top:0">جميع الحقول المؤشرة بـ (*) إلزامية.</p>

    <?php if (!empty($errors['form'])): ?>
      <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
    <?php endif; ?>

    <div class="form-grid">
      <fieldset style="border:1px solid var(--c-line);border-radius:9px;padding:1rem">
        <legend style="font-weight:700;color:var(--c-primary-dark)">نوع الرسالة</legend>
        <label style="display:inline-flex;gap:.4rem;align-items:center;margin-inline-end:1.5rem;font-weight:500">
          <input type="radio" name="type" value="suggestion" <?= $old['type'] === 'suggestion' ? 'checked' : '' ?>> مقترح
        </label>
        <label style="display:inline-flex;gap:.4rem;align-items:center;font-weight:500">
          <input type="radio" name="type" value="complaint" <?= $old['type'] === 'complaint' ? 'checked' : '' ?>> شكوى
        </label>
      </fieldset>

      <div class="form-row">
        <div>
          <label class="req" for="f-name">الاسم الكامل</label>
          <input type="text" id="f-name" name="name" maxlength="150" required value="<?= e($old['name']) ?>"
                 aria-invalid="<?= isset($errors['name']) ? 'true' : 'false' ?>">
          <?php if (isset($errors['name'])): ?><p class="field-error" role="alert"><?= e($errors['name']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="req" for="f-email">البريد الإلكتروني</label>
          <input type="email" id="f-email" name="email" dir="ltr" required value="<?= e($old['email']) ?>"
                 aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>">
          <?php if (isset($errors['email'])): ?><p class="field-error" role="alert"><?= e($errors['email']) ?></p><?php endif; ?>
        </div>
      </div>

      <div>
        <label class="req" for="f-phone">رقم الجوال</label>
        <input type="tel" id="f-phone" name="phone" dir="ltr" required value="<?= e($old['phone']) ?>"
               aria-invalid="<?= isset($errors['phone']) ? 'true' : 'false' ?>">
        <?php if (isset($errors['phone'])): ?><p class="field-error" role="alert"><?= e($errors['phone']) ?></p><?php endif; ?>
      </div>

      <div>
        <label class="req" for="f-message">نص المقترح / الشكوى</label>
        <textarea id="f-message" name="message" maxlength="3000" required
                  aria-invalid="<?= isset($errors['message']) ? 'true' : 'false' ?>"><?= e($old['message']) ?></textarea>
        <?php if (isset($errors['message'])): ?><p class="field-error" role="alert"><?= e($errors['message']) ?></p><?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary">إرسال</button>
    </div>
  </form>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
