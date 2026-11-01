<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة أعضاء مجلس الإدارة';
$ADMIN_ACTIVE  = 'board';
$TABLE         = 'board_members';
$REDIRECT      = 'board.php';
$TITLE_SINGULAR = 'عضو مجلس إدارة';
$FIELDS = [
    ['name' => 'name', 'label' => 'الاسم', 'type' => 'text'],
    ['name' => 'position', 'label' => 'الصفة / المنصب', 'type' => 'text', 'nullable' => true],
    ['name' => 'image', 'label' => 'الصورة الشخصية', 'type' => 'image', 'dir' => 'team', 'nullable' => true],
    ['name' => 'is_active', 'label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['الاسم', 'الصفة', 'الصورة', 'النبذة'];
    return [
        e((string)($r['name'] ?? '')),
        e((string)($r['position'] ?? '')),
        !empty($r['image']) ? '<img class="row-thumb" src="' . e(upload_url($r['image'])) . '" alt="">' : '—',
        !empty($r['bio_html']) ? '<a href="' . e(url('admin/editor.php?table=board_members&col=bio_html&id=' . (int)$r['id'])) . '">تحرير النبذة ↗</a>' : '<span style="color:var(--c-muted)">—</span>',
    ];
};
require __DIR__ . '/_list_page.php';
