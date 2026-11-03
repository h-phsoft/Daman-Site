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
    ['name' => 'excerpt_html', 'label' => 'مقتطف الخبر (يظهر في القوائم)', 'type' => 'html', 'nullable' => true],
    ['name' => 'content_html', 'label' => 'نص الخبر الكامل', 'type' => 'html', 'nullable' => true],
    ['name' => 'is_active',    'label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['العنوان', 'الصورة', 'التاريخ', 'المقتطف', 'النص الكامل'];
    return [
        e((string)($r['title'] ?? '')),
        admin_thumb($r['image'] ?? null, 'news'),
        e(ar_date((string)($r['publish_date'] ?? ''))),
        admin_text_status($r['excerpt_html'] ?? null),
        admin_text_status($r['content_html'] ?? null),
    ];
};
require __DIR__ . '/_list_page.php';
