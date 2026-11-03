<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة الإدارة التنفيذية';
$ADMIN_ACTIVE  = 'executives';
$TABLE         = 'executives';
$REDIRECT      = 'executives.php';
$TITLE_SINGULAR = 'تنفيذي';
$FIELDS = [
    ['name' => 'name', 'label' => 'الاسم', 'type' => 'text'],
    ['name' => 'position', 'label' => 'الصفة / المنصب', 'type' => 'text', 'nullable' => true],
    ['name' => 'image', 'label' => 'الصورة الشخصية', 'type' => 'image', 'upload' => 'executives', 'nullable' => true],
    ['name' => 'bio_html', 'label' => 'النبذة', 'type' => 'html', 'nullable' => true],
    ['name' => 'is_active', 'label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['الاسم', 'الصفة', 'الصورة', 'النبذة'];
    return [
        e((string)($r['name'] ?? '')),
        e((string)($r['position'] ?? '')),
        admin_thumb($r['image'] ?? null, 'executives'),
        admin_text_status($r['bio_html'] ?? null),
    ];
};
require __DIR__ . '/_list_page.php';
