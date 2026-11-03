<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة المنتجات';
$ADMIN_ACTIVE  = 'products';
$TABLE         = 'products';
$REDIRECT      = 'products.php';
$TITLE_SINGULAR = 'منتج';
$FIELDS = [
    ['name' => 'title',      'label' => 'اسم المنتج', 'type' => 'text'],
    ['name' => 'image',      'label' => 'صورة المنتج', 'type' => 'image', 'upload' => 'products', 'nullable' => true],
    ['name' => 'short_desc', 'label' => 'وصف مختصر',  'type' => 'textarea', 'nullable' => true, 'max' => 500],
    ['name' => 'content_html','label' => 'المحتوى الكامل للمنتج', 'type' => 'html', 'nullable' => true],
    ['name' => 'is_active',  'label' => 'الحالة',     'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['المنتج', 'الصورة', 'وصف مختصر', 'المحتوى الكامل'];
    $out = [e((string)($r['title'] ?? ''))];
    $out[] = admin_thumb($r['image'] ?? null, 'products');
    $d = mb_substr(strip_tags((string)($r['short_desc'] ?? '')), 0, 60);
    $out[] = e($d);
    $out[] = admin_text_status($r['content_html'] ?? null);
    return $out;
};
require __DIR__ . '/_list_page.php';
