<?php
/**
 * محرر TinyMCE المستقل لحقول HTML:
 * editor.php?table=products&col=content_html&id=3
 */
declare(strict_types=1);

require_once __DIR__ . '/_crud.php';
require_admin();

$ALLOWED = [
    'products'        => ['content_html' => 'المحتوى الكامل للمنتج', 'back' => 'products.php'],
    'projects'        => ['content_html' => 'وصف المشروع',          'back' => 'projects.php'],
    'news'            => ['excerpt_html' => 'مقتطف الخبر', 'content_html' => 'نص الخبر الكامل', 'back' => 'news.php'],
    'board_members'   => ['bio_html' => 'نبذة العضو',               'back' => 'board.php'],
    'executives'      => ['bio_html' => 'نبذة التنفيذي',            'back' => 'executives.php'],
    'faqs'            => ['answer_html' => 'جواب السؤال',           'back' => 'faqs.php'],
];

$table  = (string)($_GET['table'] ?? $_POST['table'] ?? '');
$col    = (string)($_GET['col']   ?? $_POST['col']   ?? '');
$id     = (int)($_GET['id']       ?? $_POST['id']    ?? 0);

// قائمة بيضاء صارمة — تمنع الوصول لغير المعرفات الصحيحة (IDOR)
if (!isset($ALLOWED[$table]) || !isset($ALLOWED[$table][$col]) || $id <= 0) {
    http_response_code(404);
    exit('الرابط غير صالح.');
}
$backUrl = url('admin/' . $ALLOWED[$table]['back']);

$row = fetchOne("SELECT * FROM `$table` WHERE id = ?", [$id]);
if (!$row) {
    flash_set('error', 'العنصر غير موجود.');
    header('Location: ' . $backUrl);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $html = clean_html_store(trim((string)($_POST['content'] ?? '')));
    q("UPDATE `$table` SET `$col` = ? WHERE id = ?", [$html !== '' ? $html : null, $id]);
    flash_set('success', 'تم حفظ المحتوى بنجاح.');
    header('Location: ' . $backUrl);
    exit;
}

$ADMIN_TITLE  = 'تحرير: ' . $ALLOWED[$table][$col] . ' — ' . e($row['title'] ?? $row['name'] ?? $row['question'] ?? ('#' . $id));
$ADMIN_ACTIVE = ['products' => 'products', 'projects' => 'projects', 'news' => 'news',
                 'board_members' => 'board', 'executives' => 'executives', 'faqs' => 'faqs'][$table];
require __DIR__ . '/_top.php';
?>
<div class="panel">
  <p><a href="<?= e($backUrl) ?>">← العودة إلى القائمة</a></p>
  <h2 style="margin-top:0"><?= e($ALLOWED[$table][$col]) ?>: <?= e($row['title'] ?? $row['name'] ?? $row['question'] ?? '') ?></h2>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="table" value="<?= e($table) ?>">
    <input type="hidden" name="col" value="<?= e($col) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">
    <label class="req" for="editorArea">المحتوى المنسّق</label>
    <textarea class="html-editor" id="editorArea" name="content" rows="16"><?= e((string)($row[$col] ?? '')) ?></textarea>
    <div style="margin-top:1rem;display:flex;gap:.7rem">
      <button type="submit" class="btn btn-primary">حفظ المحتوى</button>
      <a class="btn btn-outline" href="<?= e($backUrl) ?>">إلغاء</a>
    </div>
  </form>
</div>
<?php admin_footer(); ?>
