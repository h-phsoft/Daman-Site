<?php
/**
 * إعدادات الموقع: الهوية (الشعاران، الأيقونتان، النمط الافتراضي)، الاسم، العنوان،
 * الهواتف، البريد، السوشيال، SEO + تغيير كلمة المرور.
 * كل ما يغيّره المستخدم يُحفظ في جدول settings؛ الملفات تُخزَّن بأسمائها فقط.
 */
declare(strict_types=1);

require_once __DIR__ . '/_crud.php';
require_admin();

// روابط حسابات التواصل الاجتماعي (وحدها تُضاف لها https:// عند الحاجة)
const SOCIAL_KEYS = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'];

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
        $hadError = false;
        foreach ($TEXT_KEYS as $key => $label) {
            if (!array_key_exists($key, $_POST)) continue;
            $v = trim((string)$_POST[$key]);
            $isSocial = in_array($key, SOCIAL_KEYS, true);
            if ($isSocial && $v !== '' && !valid_url($v)) {
                flash_set('error', "$label: رابط غير صالح.");
                $hadError = true;
                continue;
            }
            if ($key === 'email' && $v !== '' && !valid_email($v)) {
                flash_set('error', 'البريد الإلكتروني غير صالح.');
                $hadError = true;
                continue;
            }
            if ($key === 'site_name' && $v === '') {
                flash_set('error', 'اسم الموقع مطلوب.');
                $hadError = true;
                continue;
            }
            // تطبيع الروابط فقط؛ الاسم والعنوان والهاتف والبريد تُحفظ كما هي
            set_setting($key, $isSocial ? normalize_url_input($v) : $v);
        }
        // النمط الافتراضي للزائر الجديد
        $theme = (string)($_POST['default_theme'] ?? '');
        if ($theme === 'light' || $theme === 'dark') {
            set_setting('default_theme', $theme);
        }
        // ملفات الهوية: الشعاران والأيقونتان (اسم الملف فقط في settings)
        foreach (BRAND_FILE_SETTINGS as $key => $label) {
            if (empty($_FILES['upload_' . $key]['name'])) continue;
            [$ok, $res] = upload_file('upload_' . $key, 'brand', IMAGE_MIMES);
            if (!$ok) {
                flash_set('error', $label . ': ' . $res);
                $hadError = true;
                continue;
            }
            $old = setting($key);
            set_setting($key, $res);
            // حذف الملف القديم ما لم يكن مستخدمًا في إعداد هوية آخر
            $stillUsed = false;
            foreach (array_keys(BRAND_FILE_SETTINGS) as $other) {
                if ($other !== $key && setting($other) === $old) $stillUsed = true;
            }
            if ($old !== '' && !$stillUsed) delete_upload($old, 'brand');
        }
        if (!$hadError) flash_set('success', 'تم حفظ الإعدادات.');
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
  <h2 style="margin-top:0">إعدادات الموقع العامة</h2>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="action" value="save_settings">
    <div class="form-grid">
      <h3 style="margin:0">الهوية والمظهر</h3>
      <?php
      $BRAND_HINTS = [
          'logo_light'       => 'يظهر في النمط الفاتح — شعار ملوّن بخلفية شفافة.',
          'logo_dark'        => 'يظهر في النمط الداكن — شعار فاتح بخلفية شفافة.',
          'favicon'          => 'أيقونة تبويب المتصفح — صورة مربعة (64×64 أو أكبر).',
          'apple_touch_icon' => 'أيقونة الشاشة الرئيسية في iPhone و iPad — مربعة 180×180.',
      ];
      foreach (BRAND_FILE_SETTINGS as $key => $label):
          $file = setting($key);
          $has  = upload_exists($file, 'brand');
      ?>
        <div>
          <label for="s-<?= e($key) ?>"><?= e($label) ?></label>
          <div style="display:flex;gap:.8rem;align-items:center;flex-wrap:wrap">
            <span class="logo-preview<?= $key === 'logo_dark' ? ' on-dark' : '' ?><?= str_starts_with($key, 'logo') ? '' : ' is-icon' ?>">
              <?php if ($has): ?>
                <img src="<?= e(upload_url($file, 'brand')) ?>" alt="<?= e($label) ?>">
              <?php else: ?>
                <span style="color:var(--c-muted);font-size:.85rem">لا يوجد ملف</span>
              <?php endif; ?>
            </span>
            <input type="file" id="s-<?= e($key) ?>" name="upload_<?= e($key) ?>" accept=".png,.jpg,.jpeg,.webp,.gif,.svg">
          </div>
          <small style="color:var(--c-muted)"><?= e($BRAND_HINTS[$key]) ?> PNG / SVG / JPG / WebP حتى 5MB.</small>
        </div>
      <?php endforeach; ?>
      <div>
        <label for="s-default_theme">النمط الافتراضي للزائر الجديد</label>
        <select id="s-default_theme" name="default_theme">
          <option value="light"<?= default_theme() === 'light' ? ' selected' : '' ?>>فاتح</option>
          <option value="dark"<?= default_theme() === 'dark' ? ' selected' : '' ?>>داكن</option>
        </select>
        <small style="color:var(--c-muted)">يُطبَّق على من لم يختر نمطًا بنفسه؛ اختيار الزائر بزر التبديل يبقى محفوظًا له.</small>
      </div>

      <h3 style="margin:.6rem 0 0">بيانات الموقع</h3>
      <?php foreach ($TEXT_KEYS as $key => $label): ?>
        <div>
          <label<?= $key === 'site_name' ? ' class="req"' : '' ?> for="s-<?= e($key) ?>"><?= e($label) ?></label>
          <input type="text" id="s-<?= e($key) ?>" name="<?= e($key) ?>" dir="ltr"
                 value="<?= e(setting($key)) ?>"
                 <?= in_array($key, SOCIAL_KEYS, true) ? 'placeholder="https://"' : '' ?>>
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
