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
    ['name' => 'content_html','label' => 'المحتوى الكامل (يُحرَّر من زر "محتوى" بالجدول)', 'type' => 'textarea', 'nullable' => true, 'hidden_in_form' => true],
    ['name' => 'is_active',  'label' => 'الحالة',     'type' => 'checkbox', 'default' => 1],
];
unset($FIELDS[3]); // المحتوى عبر editor.php
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['المنتج', 'الصورة', 'وصف مختصر'];
    $out = [e((string)($r['title'] ?? ''))];
    $out[] = !empty($r['image']) ? '<img class="row-thumb" src="' . e(upload_url($r['image'], 'products')) . '" alt="">' : '—';
    $d = mb_substr(strip_tags((string)($r['short_desc'] ?? '')), 0, 60);
    $out[] = e($d);
    return $out;
};
require __DIR__ . '/_list_page.php';
