<?php
/**
 * ترحيل ملفات: نقل كل ملف مرفوع إلى مجلد نوعه كما في UPLOAD_DIRS (config.php).
 *
 * سابقًا كانت صور المشاريع وفقرات الصفحات (ومنها كلمة المدير العام) تُرفع إلى
 * uploads/products. صار لكل منهما مجلده: uploads/projects و uploads/sections.
 * يبحث السكربت عن كل ملف مسجّل في قاعدة البيانات وغير موجود في مجلده الصحيح
 * داخل بقية مجلدات uploads، وينقله إليه (أو ينسخه إن كان عنصر آخر يستخدمه في
 * مكانه القديم).
 *
 * الاستخدام من مجلد الموقع على الخادم:
 *   php database/migrations/2026-11-04-move-uploads-to-own-folders.php           ← عرض فقط (بدون تغيير)
 *   php database/migrations/2026-11-04-move-uploads-to-own-folders.php --apply   ← تنفيذ النقل
 * آمن للتشغيل أكثر من مرة.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/functions.php';

$apply = in_array('--apply', $argv, true);

// كل الملفات المسجلة: [نوع الرفع، اسم الملف]
$refs = [];
$sources = [
    ['slider', 'image', 'slider'], ['news', 'image', 'news'], ['products', 'image', 'products'],
    ['projects', 'image', 'projects'], ['page_sections', 'image', 'sections'],
    ['board_members', 'image', 'board'], ['executives', 'image', 'executives'],
    ['important_links', 'image', 'links'], ['reports', 'file_path', 'reports'],
];
foreach ($sources as [$table, $col, $type]) {
    foreach (fetchAll("SELECT `$col` AS f FROM `$table` WHERE `$col` IS NOT NULL AND `$col` <> ''") as $r) {
        $refs[] = [$type, upload_name($r['f'])];
    }
}
foreach (array_keys(BRAND_FILE_SETTINGS) as $key) {
    if (setting($key) !== '') $refs[] = ['brand', upload_name(setting($key))];
}

// هل يحتاج ملف في مجلد ما للبقاء فيه (لأن نوعًا آخر يشير إليه من هناك)؟
$neededIn = [];
foreach ($refs as [$type, $name]) {
    $neededIn[upload_dir($type) . '/' . $name] = true;
}

$folders = array_values(array_unique(array_values(UPLOAD_DIRS)));
$moved = $copied = $notFound = $ok = 0;

foreach ($refs as [$type, $name]) {
    if ($name === '' || preg_match('#^https?:#i', $name)) continue;
    $target = upload_path($name, $type);
    if (is_file($target)) { $ok++; continue; }

    // ابحث عنه في بقية مجلدات الرفع
    $found = null;
    foreach ($folders as $dir) {
        $candidate = UPLOAD_PATH . '/' . $dir . '/' . $name;
        if ($dir !== upload_dir($type) && is_file($candidate)) { $found = $dir; break; }
    }
    $rel = UPLOAD_URL . '/' . upload_dir($type) . '/' . $name;
    if ($found === null) {
        echo "مفقود  : $rel (غير موجود في أي مجلد داخل " . UPLOAD_URL . ")\n";
        $notFound++;
        continue;
    }
    $from = UPLOAD_PATH . '/' . $found . '/' . $name;
    $keepOld = isset($neededIn[$found . '/' . $name]); // عنصر آخر ما زال يستخدمه هناك
    echo ($keepOld ? 'نسخ: من ' : 'نقل: من ') . UPLOAD_URL . "/$found/$name  إلى  $rel\n";
    if (!$apply) {
        $keepOld ? $copied++ : $moved++; // معاينة: عدّ ما سيُنفَّذ
    } else {
        $dir = dirname($target);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if ($keepOld ? copy($from, $target) : rename($from, $target)) {
            $keepOld ? $copied++ : $moved++;
        } else {
            echo "  فشل: تحقّق من صلاحيات المجلد $dir\n";
        }
    }
}

echo "\nفي مكانه الصحيح: $ok | " . ($apply ? 'نُقل' : 'سيُنقل') . ": $moved | " . ($apply ? 'نُسخ' : 'سيُنسخ') . ": $copied | مفقود تمامًا: $notFound\n";
if (!$apply) echo "لم يُغيَّر شيء. أعد التشغيل مع --apply للتنفيذ.\n";
