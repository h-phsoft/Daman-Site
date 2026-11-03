<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة الأخبار';
$ADMIN_ACTIVE  = 'news';
$TABLE         = 'news';
$REDIRECT      = 'news.php';
$TITLE_SINGULAR = 'خبر';
$FIELDS = [
    ['name' => 'title',        'label' => 'عنوان الخبر', 'type' => 'text', 'max' => 200],
    ['name' => 'image',        'label' => 'صورة الخبر', 'type' => 'image', 'upload' => 'news', 'nullable' => true],
    ['name' => 'publish_date', 'label' => 'تاريخ النشر', 'type' => 'date'],
    ['name' => 'is_active',    'label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['العنوان', 'الصورة', 'التاريخ', 'المقتطف والمحتوى'];
    return [
        e((string)($r['title'] ?? '')),
        !empty($r['image']) ? '<img class="row-thumb" src="' . e(upload_url($r['image'], 'news')) . '" alt="">' : '—',
        e(ar_date((string)($r['publish_date'] ?? ''))),
        '<a href="' . e(url('admin/editor.php?table=news&col=excerpt_html&id=' . (int)$r['id'])) . '">المقتطف ↗</a> · ' .
        '<a href="' . e(url('admin/editor.php?table=news&col=content_html&id=' . (int)$r['id'])) . '">المحتوى ↗</a>',
    ];
};
require __DIR__ . '/_list_page.php';
