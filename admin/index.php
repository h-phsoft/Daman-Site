<?php
/**
 * Dashboard — إحصائيات سريعة وروابط مختصرة.
 */
declare(strict_types=1);

require_once __DIR__ . '/_crud.php';
require_admin();

$stats = [
    ['المنتجات',        (int)fetchValue('SELECT COUNT(*) FROM products'),               'box',   'products.php'],
    ['المشاريع المستهدفة', (int)fetchValue('SELECT COUNT(*) FROM projects'),            'target','projects.php'],
    ['الأخبار',         (int)fetchValue('SELECT COUNT(*) FROM news'),                   'news',  'news.php'],
    ['أعضاء مجلس الإدارة', (int)fetchValue('SELECT COUNT(*) FROM board_members'),       'users', 'board.php'],
    ['الإدارة التنفيذية', (int)fetchValue('SELECT COUNT(*) FROM executives'),           'user',  'executives.php'],
    ['شرائح السلايدر',  (int)fetchValue('SELECT COUNT(*) FROM slider'),                 'image', 'slider.php'],
    ['التقارير السنوية', (int)fetchValue('SELECT COUNT(*) FROM reports'),               'doc',   'reports.php'],
    ['الأسئلة المتكررة', (int)fetchValue('SELECT COUNT(*) FROM faqs'),                  'help',  'faqs.php'],
    ['المواقع الهامة',  (int)fetchValue('SELECT COUNT(*) FROM important_links'),        'link',  'links.php'],
    ['الرسائل الواردة', (int)fetchValue('SELECT COUNT(*) FROM contact_messages'),       'mail',  'messages.php'],
];
$latestNews  = fetchAll('SELECT id, title, publish_date, is_active FROM news ORDER BY publish_date DESC, id DESC LIMIT 5');
$latestMsgs  = fetchAll("SELECT id, type, name, created_at FROM contact_messages ORDER BY created_at DESC, id DESC LIMIT 5");
$typeLabels  = ['contact' => 'تواصل', 'suggestion' => 'اقتراح', 'complaint' => 'شكوى'];
$missingFiles = find_missing_uploads();

$ADMIN_TITLE  = 'لوحة التحكم — الرئيسية';
$ADMIN_ACTIVE = 'index';
require __DIR__ . '/_top.php';
?>
<?php if ($missingFiles): ?>
<div class="panel missing-panel" role="alert">
  <h2 style="margin-top:0">⚠ ملفات مفقودة على الخادم (<?= count($missingFiles) ?>)</h2>
  <p style="margin-top:0;color:var(--c-muted)">
    هذه الملفات مسجّلة في قاعدة البيانات لكنها غير موجودة في مجلدها داخل <code dir="ltr"><?= e(UPLOAD_URL) ?></code>،
    لذلك يعرض الموقع مكانها صورة بديلة من <code dir="ltr"><?= e(ASSETS_URL . '/' . DEFAULT_IMG_DIR) ?></code>.
    ارفع الملف من جديد من صفحة العنصر، أو انسخه إلى المسار الظاهر.
  </p>
  <div class="table-wrap" style="box-shadow:none">
    <table class="data" style="min-width:auto">
      <thead><tr><th>القسم</th><th>العنصر</th><th>المسار المتوقع</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($missingFiles as $m): ?>
        <tr>
          <td><?= e($m['section']) ?></td>
          <td><?= e($m['item']) ?></td>
          <td dir="ltr" style="text-align:left"><code><?= e($m['path']) ?></code></td>
          <td><a href="<?= e($m['edit']) ?>">إصلاح ←</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="stat-cards">
  <?php foreach ($stats as [$lbl, $num, $icon, $link]): ?>
    <a class="stat-card" href="<?= e(url('admin/' . $link)) ?>" style="text-decoration:none;color:inherit">
      <svg width="26" height="26" aria-hidden="true" style="color:var(--c-primary)"><use href="<?= asset('vendor/icons/ui.svg#' . $icon) ?>"></use></svg>
      <span><span class="num"><?= $num ?></span><br><span class="lbl"><?= e($lbl) ?></span></span>
    </a>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.2rem">
  <div class="panel" style="margin-bottom:0">
    <h2 style="margin-top:0">أحدث الأخبار</h2>
    <table class="data" style="min-width:auto">
      <tbody>
      <?php foreach ($latestNews as $n): ?>
        <tr>
          <td><?= e($n['title']) ?></td>
          <td style="white-space:nowrap"><?= e(ar_date($n['publish_date'])) ?></td>
          <td><span class="badge <?= (int)$n['is_active'] ? 'badge-on' : 'badge-off' ?>"><?= (int)$n['is_active'] ? 'منشور' : 'متوقف' ?></span></td>
          <td><a href="<?= e(url('admin/news.php?edit=' . (int)$n['id'])) ?>" aria-label="تعديل">✎</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$latestNews): ?><tr><td style="color:var(--c-muted)">لا توجد أخبار بعد.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="panel" style="margin-bottom:0">
    <h2 style="margin-top:0">آخر الرسائل</h2>
    <table class="data" style="min-width:auto">
      <tbody>
      <?php foreach ($latestMsgs as $m): ?>
        <tr>
          <td><span class="badge <?= $m['type'] === 'complaint' ? 'badge-off' : 'badge-on' ?>"><?= e($typeLabels[$m['type']] ?? $m['type']) ?></span></td>
          <td><?= e($m['name']) ?></td>
          <td style="white-space:nowrap"><?= e(substr((string)$m['created_at'], 0, 16)) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$latestMsgs): ?><tr><td style="color:var(--c-muted)">لا توجد رسائل.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <p style="margin-bottom:0"><a href="<?= e(url('admin/messages.php')) ?>">عرض كل الرسائل →</a></p>
  </div>
</div>
<?php admin_footer(); ?>
