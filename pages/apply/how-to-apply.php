<?php
/** أسلوب التقدم للخدمة — Accordion من page_sections (slug='how-to-apply') */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__) . '/_sections_helper.php';

$data = load_page_sections('how-to-apply');
$PAGE_TITLE = $data['page'] ? $data['page']['title'] : 'أسلوب التقدم للخدمة';
$ACTIVE_NAV = 'apply';
$PAGE_DESC = 'خطوات وشروط التقديم على خدمات الصندوق.';
require ROOT_PATH . '/includes/header.php';

render_sections_intro($data, 'كل ما تحتاج معرفته قبل تقديم طلبك — افتح العنصر لعرض التفاصيل.');
?>
<div class="container section" style="padding-top:0">
  <?php if (!$data['sections']): ?>
    <div class="alert alert-error">لا يوجد محتوى منشور حاليًا لهذه الصفحة.</div>
  <?php else: ?>
    <?php render_accordion($data['sections'], 'hta'); ?>
  <?php endif; ?>
</div>
<?php require ROOT_PATH . '/includes/footer.php'; ?>
