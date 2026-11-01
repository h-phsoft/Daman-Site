<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة المواقع الهامة';
$ADMIN_ACTIVE  = 'links';
$TABLE         = 'important_links';
$REDIRECT      = 'links.php';
$TITLE_SINGULAR = 'موقع هام';
$FIELDS = [
    ['name' => 'title', 'label' => 'اسم الموقع', 'type' => 'text'],
    ['name' => 'image', 'label' => 'الشعار / الصورة', 'type' => 'image', 'dir' => 'links', 'nullable' => true],
    ['name' => 'url',   'label' => 'الرابط', 'type' => 'url'],
    ['name' => 'is_active', 'label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
require __DIR__ . '/_list_page.php';
