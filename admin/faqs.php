<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة الأسئلة المتكررة';
$ADMIN_ACTIVE  = 'faqs';
$TABLE         = 'faqs';
$REDIRECT      = 'faqs.php';
$TITLE_SINGULAR = 'سؤال';
$FIELDS = [
    ['name' => 'question', 'label' => 'السؤال', 'type' => 'text', 'max' => 300],
    ['name' => 'answer_html', 'label' => 'الجواب', 'type' => 'html', 'nullable' => true],
    ['name' => 'is_active','label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['السؤال', 'الجواب'];
    return [
        e((string)($r['question'] ?? '')),
        admin_text_status($r['answer_html'] ?? null),
    ];
};
require __DIR__ . '/_list_page.php';
