<?php
declare(strict_types=1);
$ADMIN_TITLE   = 'إدارة التقارير السنوية';
$ADMIN_ACTIVE  = 'reports';
$TABLE         = 'reports';
$REDIRECT      = 'reports.php';
$TITLE_SINGULAR = 'تقرير';
$REPORT_MAX = 10 * 1024 * 1024; // 10MB للتقارير PDF
$FIELDS = [
    ['name' => 'title',        'label' => 'عنوان التقرير', 'type' => 'text'],
    ['name' => 'file_path',    'label' => 'ملف التقرير (PDF)', 'type' => 'file', 'dir' => 'reports'],
    ['name' => 'publish_date', 'label' => 'تاريخ الإصدار', 'type' => 'date'],
    ['name' => 'is_active',    'label' => 'الحالة', 'type' => 'checkbox', 'default' => 1],
];
$LIST_COLS = function (string $mode, ?array $r = null) {
    if ($mode === 'head') return ['التقرير', 'الملف', 'التاريخ'];
    return [
        e((string)($r['title'] ?? '')),
        !empty($r['file_path']) ? '<a href="' . e(url((string)$r['file_path'])) . '" target="_blank" rel="noopener">PDF ↗</a>' : '—',
        e(ar_date((string)($r['publish_date'] ?? ''))),
    ];
};
require __DIR__ . '/_list_page.php';
