<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة المشاريع المستهدفة';
$ADMIN_ACTIVE  = 'projects';
$TABLE         = 'projects';
$REDIRECT      = 'projects.php';
$TITLE_SINGULAR = 'مشروع';
$FIELDS = [
    ['name' => 'title', 'label' => 'اسم المشروع', 'type' => 'text'],
    ['name' => 'image', 'label' => 'صورة المشروع', 'type' => 'image', 'upload' => 'projects', 'nullable' => true],
    ['name' => 'content_html', 'label' => 'وصف المشروع', 'type' => 'html', 'nullable' => true],
    ['name' => 'is_active', 'label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['المشروع', 'الصورة', 'المحتوى'];
    return [
        e((string)($r['title'] ?? '')),
        admin_thumb($r['image'] ?? null, 'projects'),
        admin_text_status($r['content_html'] ?? null),
    ];
};
require __DIR__ . '/_list_page.php';
