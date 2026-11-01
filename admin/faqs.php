<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة الأسئلة المتكررة';
$ADMIN_ACTIVE  = 'faqs';
$TABLE         = 'faqs';
$REDIRECT      = 'faqs.php';
$TITLE_SINGULAR = 'سؤال';
$FIELDS = [
    ['name' => 'question', 'label' => 'السؤال', 'type' => 'text', 'max' => 300],
    ['name' => 'is_active','label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['السؤال', 'الجواب'];
    return [
        e((string)($r['question'] ?? '')),
        !empty($r['answer_html']) ? '<a href="' . e(url('admin/editor.php?table=faqs&col=answer_html&id=' . (int)$r['id'])) . '">تحرير الجواب ↗</a>' : '<span style="color:var(--c-danger)">بدون جواب</span>',
    ];
};
require __DIR__ . '/_list_page.php';
