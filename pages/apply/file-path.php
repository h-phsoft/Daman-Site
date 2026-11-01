<?php
/** مسار لملف — Accordion من page_sections (slug='file-path') */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__) . '/_sections_helper.php';

$data = load_page_sections('file-path');
$PAGE_TITLE = $data['page'] ? $data['page']['title'] : 'مسار الملف';
$ACTIVE_NAV = 'apply';
$PAGE_DESC = 'مراحل معالجة ملف الطلب داخل الصندوق.';
require ROOT_PATH . '/includes/header.php';

render_sections_intro($data, 'تعرّف على رحلة ملف طلبك منذ الاستلام وحتى إصدار القرار.');
?>
<div class="container section" style="padding-top:0">
  <?php if (!$data['sections']): ?>
    <div class="alert alert-error">لا يوجد محتوى منشور حاليًا لهذه الصفحة.</div>
  <?php else: ?>
    <?php render_accordion($data['sections'], 'fp'); ?>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
