<?php
/**
 * صفحة CRUD جاهزة — تُستدعى عبر admin/<name>.php بعد تعريف المتغيرات.
 * المتغيرات المطلوبة:
 *   $TABLE, $FIELDS, $REDIRECT, $TITLE_SINGULAR, $ADMIN_TITLE, $ADMIN_ACTIVE
 * اختيارية:
 *   $LIST_COLS = callable(head|row) لعرض مخصص، أو ['title','name','question'] لأسماء أعمدة نصية تلقائية.
 */

declare(strict_types=1);

require_once __DIR__ . '/_crud.php';

if (!isset($TABLE, $FIELDS, $REDIRECT, $TITLE_SINGULAR)) {
    http_response_code(500);
    exit('إعدادات صفحة الإدارة غير مكتملة.');
}

$editId   = (int)($_GET['edit'] ?? 0);
$rowToEdit = null;
if ($editId > 0) {
    $rowToEdit = fetchOne("SELECT * FROM `$TABLE` WHERE id = ?", [$editId]); // prepared — منع IDOR
    if (!$rowToEdit) {
        flash_set('error', 'العنصر المطلوب تعديله غير موجود.');
        header('Location: ' . url('admin/' . $REDIRECT));
        exit;
    }
}

// معالج عمليات POST (حفظ/حذف/ترتيب)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_handle_crud($TABLE, $FIELDS, $REDIRECT);
}

// جدول الأخبار بلا sort_order: يُرتَّب بالتاريخ، وبقية الجداول بالترتيب اليدوي
$orderBy = $TABLE === 'news' ? 'publish_date DESC, id DESC' : 'sort_order ASC, id ASC';
$rows = fetchAll("SELECT * FROM `$TABLE` ORDER BY $orderBy");

/* أعمدة القائمة الافتراضية: نصوص آمنة عبر e() + صور مصغرة */
if (!isset($LIST_COLS)) {
    $textCols = array_values(array_filter($FIELDS, fn($f) => in_array($f['type'], ['text', 'textarea', 'date', 'url'], true)));
    $imgCol   = null;
    foreach ($FIELDS as $f) {
        if ($f['type'] === 'image') { $imgCol = $f; break; }
    }
    $LIST_COLS = function (string $mode, ?array $r = null) use ($textCols, $imgCol) {
        if ($mode === 'head') {
            $h = [];
            if ($imgCol) $h[] = 'الصورة';
            foreach ($textCols as $f) $h[] = $f['label'];
            return $h;
        }
        $out = [];
        if ($imgCol) {
            $out[] = admin_thumb($r[$imgCol['name']] ?? null, $imgCol['upload']);
        }
        foreach ($textCols as $f) {
            $v = (string)($r[$f['name']] ?? '');
            if ($f['type'] === 'date') $v = ar_date($v);
            $out[] = e(mb_strlen($v) > 60 ? mb_substr($v, 0, 60) . '…' : $v);
        }
        return $out;
    };
}

require __DIR__ . '/_top.php';
admin_render_form($TABLE, $FIELDS, $rowToEdit, $REDIRECT, $TITLE_SINGULAR);
admin_render_list($TABLE, $rows, $LIST_COLS, $REDIRECT);
admin_footer();
