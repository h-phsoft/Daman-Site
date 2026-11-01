<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة المشاريع المستهدفة';
$ADMIN_ACTIVE  = 'projects';
$TABLE         = 'projects';
$REDIRECT      = 'projects.php';
$TITLE_SINGULAR = 'مشروع';
$FIELDS = [
    ['name' => 'title', 'label' => 'اسم المشروع', 'type' => 'text'],
    ['name' => 'image', 'label' => 'صورة المشروع', 'type' => 'image', 'dir' => 'products', 'nullable' => true],
    ['name' => 'is_active', 'label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['المشروع', 'الصورة', 'المحتوى'];
    return [
        e((string)($r['title'] ?? '')),
        !empty($r['image']) ? '<img class="row-thumb" src="' . e(upload_url($r['image'])) . '" alt="">' : '—',
        !empty($r['content_html']) ? '<a href="' . e(url('admin/editor.php?table=projects&id=' . (int)$r['id'])) . '">تحرير النص ↗</a>' : '<span style="color:var(--c-danger)">بدون نص</span>',
    ];
};
require __DIR__ . '/_list_page.php';
