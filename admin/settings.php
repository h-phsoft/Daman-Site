<?php
/**
 * إعدادات الموقع: الشعار، الاسم، العنوان، الهواتف، البريد، السوشيال، SEO + تغيير كلمة المرور.
 */
declare(strict_types=1);

require_once __DIR__ . '/_crud.php';
require_admin();

$TEXT_KEYS = [
    'site_name'        => 'اسم الموقع',
    'site_tagline'     => 'الوصف المختصر (Tagline)',
    'address'          => 'العنوان',
    'phone1'           => 'الهاتف الرئيسي',
    'phone2'           => 'الهاتف الثانوي / الجوال',
    'email'            => 'البريد الإلكتروني',
    'facebook'         => 'Facebook',
    'twitter'          => 'X (تويتر)',
    'instagram'        => 'Instagram',
    'linkedin'         => 'LinkedIn',
    'youtube'          => 'YouTube',
    'meta_description' => 'وصف SEO (meta description)',
    'footer_about'     => 'نبذة الفوتر',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? 'save_settings';

    if ($action === 'save_settings') {
        foreach ($TEXT_KEYS as $key => $label) {
            if (!array_key_exists($key, $_POST)) continue;
            $v = trim((string)$_POST[$key]);
            if (in_array($key, ['facebook','twitter','instagram','linkedin','youtube'], true) && $v !== '' && !valid_url($v)) {
                flash_set('error', "$label: رابط غير صالح.");
                continue;
            }
            if ($key === 'email' && $v !== '' && !valid_email($v)) {
                flash_set('error', 'البريد الإلكتروني غير صالح.');
                continue;
            }
            if ($key === 'site_name' && $v === '') {
                flash_set('error', 'اسم الموقع مطلوب.');
                continue;
            }
            set_setting($key, normalize_url_input($v));
        }
        // رفع الشعار
        if (!empty($_FILES['upload_logo']['name'])) {
            [$ok, $res] = upload_file('upload_logo', 'img', IMAGE_MIMES);
            if ($ok) {
                $old = setting('logo');
                set_setting('logo', $res);
                if (str_starts_with($old, 'uploads/')) delete_upload($old);
                flash_set('success', 'تم تحديث الشعار.');
            } else {
                flash_set('error', 'الشعار: ' . $res);
            }
        }
        flash_set('success', 'تم حفظ الإعدادات.');
    } elseif ($action === 'change_password') {
        $cur  = (string)($_POST['current_password'] ?? '');
        $new  = (string)($_POST['new_password'] ?? '');
        $conf = (string)($_POST['confirm_password'] ?? '');
        $u = current_user();
        $row = fetchOne('SELECT password_hash FROM users WHERE id = ?', [(int)$u['id']]);
        if (!$row || !password_verify($cur, $row['password_hash'])) {
            flash_set('error', 'كلمة المرور الحالية غير صحيحة.');
        } elseif (mb_strlen($new) < 8) {
            flash_set('error', 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.');
        } elseif ($new !== $conf) {
            flash_set('error', 'تأكيد كلمة المرور غير مطابق.');
        } else {
            change_own_password((int)$u['id'], $new);
            flash_set('success', 'تم تغيير كلمة المرور بنجاح.');
        }
    }
    header('Location: ' . url('admin/settings.php'));
    exit;
}

$ADMIN_TITLE  = 'إعدادات الموقع';
$ADMIN_ACTIVE = 'settings';
require __DIR__ . '/_top.php';
?>
<div class="panel">
  <h2 style="margin-top:0">بيانات الموقع</h2>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="action" value="save_settings">
    <div class="form-grid">
      <div>
        <label>الشعار الحالي</label>
        <div style="display:flex;gap:.8rem;align-items:center">
          <span class="logo-preview"><?= brand_logo('الشعار') ?></span>
          <input type="file" name="upload_logo" accept=".jpg,.jpeg,.png,.webp,.svg">
        </div>
        <small style="color:var(--c-muted)">PNG / SVG / JPG حتى 5MB — يُنصح بشكل مربع شفاف الخلفية.</small>
      </div>
      <?php foreach ($TEXT_KEYS as $key => $label): ?>
        <div>
          <label<?= $key === 'site_name' ? ' class="req"' : '' ?> for="s-<?= e($key) ?>"><?= e($label) ?></label>
          <input type="text" id="s-<?= e($key) ?>" name="<?= e($key) ?>" dir="ltr"
                 value="<?= e(setting($key)) ?>"
                 <?= in_array($key, ['facebook','twitter','instagram','linkedin','youtube'], true) ? 'placeholder="https://"' : '' ?>>
        </div>
      <?php endforeach; ?>
      <div><button type="submit" class="btn btn-primary">حفظ الإعدادات</button></div>
    </div>
  </form>
</div>

<div class="panel">
  <h2 style="margin-top:0">تغيير كلمة مرور المدير</h2>
  <form method="post" class="form-grid" style="display:grid;gap:.8rem;max-width:560px">
    <?= csrf_field() ?><input type="hidden" name="action" value="change_password">
    <div><label class="req" for="cp1">كلمة المرور الحالية</label><input type="password" id="cp1" name="current_password" required autocomplete="current-password" dir="ltr"></div>
    <div><label class="req" for="cp2">كلمة المرور الجديدة (8+ أحرف)</label><input type="password" id="cp2" name="new_password" required minlength="8" autocomplete="new-password" dir="ltr"></div>
    <div><label class="req" for="cp3">تأكيد كلمة المرور</label><input type="password" id="cp3" name="confirm_password" required minlength="8" autocomplete="new-password" dir="ltr"></div>
    <div><button type="submit" class="btn btn-primary">تغيير كلمة المرور</button></div>
  </form>
</div>
<?php admin_footer(); ?>
