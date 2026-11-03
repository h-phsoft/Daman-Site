<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة السلايدر الرئيسي';
$ADMIN_ACTIVE  = 'slider';
$TABLE         = 'slider';
$REDIRECT      = 'slider.php';
$TITLE_SINGULAR = 'شريحة سلايدر';
$FIELDS = [
    ['name' => 'image',    'label' => 'صورة الشريحة', 'type' => 'image', 'upload' => 'slider'],
    ['name' => 'title',    'label' => 'العنوان',      'type' => 'text', 'nullable' => true],
    ['name' => 'subtitle', 'label' => 'الوصف المختصر', 'type' => 'textarea', 'nullable' => true, 'max' => 300],
    ['name' => 'cta_text', 'label' => 'نص الزر',      'type' => 'text', 'nullable' => true],
    ['name' => 'cta_link', 'label' => 'رابط الزر',    'type' => 'text', 'nullable' => true],
    ['name' => 'is_active','label' => 'الحالة',       'type' => 'checkbox', 'default' => 1],
];
require __DIR__ . '/_list_page.php';
