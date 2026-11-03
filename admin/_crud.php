<?php
/**
 * CRUD عام للجداول القابلة للترتيب — نمط واحد:
 * list (إضافة/تعديل/حذف/ترتيب) + editor.php?id=... للنصوص الغنية عبر TinyMCE.
 * المتغيرات المطلوبة من الملف الداعي: $table, $fields, $editorCols, $titleSingular, $listCols
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

/**
 * @param string $table         اسم الجدول (ضمن SORTABLE_TABLES)
 * @param array  $fields        قائمة الأعمدة القابلة للتحرير مع الأنواع
 *   type: text|textarea|html|date|url|image|file|checkbox|number
 * @param array  $editorCols    أعمدة تُحرَّر في صفحة editor.php المستقلة
 * @param string $redirect      اسم ملف القائمة
 */
function admin_handle_crud(string $table, array $fields, string $redirect): void
{
    csrf_require();

    if (!crud_table_allowed($table)) {
        flash_set('error', 'جدول غير مسموح.');
        header('Location: ' . url('admin/' . $redirect));
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        // حذف الصورة المرفوعة المرتبطة إن وجدت
        $row = fetchOne("SELECT * FROM `$table` WHERE id = ?", [$id]);
        if ($row) {
            foreach ($fields as $f) {
                if (in_array($f['type'], ['image', 'file'], true) && !empty($row[$f['name']])) {
                    delete_upload((string)$row[$f['name']], $f['upload']);
                }
            }
            q("DELETE FROM `$table` WHERE id = ?", [$id]);
            flash_set('success', 'تم الحذف بنجاح.');
        } else {
            flash_set('error', 'العنصر غير موجود.');
        }
    } elseif ($action === 'move' && $id > 0) {
        $dir = ($_POST['dir'] ?? '') === 'up' ? 'up' : 'down';
        move_item($table, $id, $dir);
    } elseif ($action === 'save') {
        $data     = [];
        $errs     = [];
        foreach ($fields as $f) {
            $name = $f['name'];
            if ($f['type'] === 'image' || $f['type'] === 'file') {
                $keep = upload_name($_POST['keep_' . $name] ?? ''); // اسم الملف فقط
                $fieldKey = 'upload_' . $name;
                if (!empty($_FILES[$fieldKey]['name'])) {
                    $mimes = $f['type'] === 'file' ? FILE_MIMES_REPORTS : IMAGE_MIMES;
                    [$ok, $res] = upload_file($fieldKey, $f['upload'], $mimes);
                    if (!$ok) {
                        $errs[$name] = $res;
                    } else {
                        if ($keep !== '') delete_upload($keep, $f['upload']);
                        $data[$name] = $res;
                    }
                } else {
                    $data[$name] = $keep !== '' ? $keep : null;
                }
                continue;
            }
            $raw = $_POST[$name] ?? '';
            switch ($f['type']) {
                case 'checkbox':
                    $data[$name] = $raw ? 1 : 0; break;
                case 'number':
                    $data[$name] = (int)$raw; break;
                case 'html':
                    $v = trim((string)$raw);
                    $data[$name] = $v !== '' ? clean_html_store($v) : null; break;
                case 'url':
                    $v = trim((string)$raw);
                    if ($v !== '' && !valid_url($v)) $errs[$name] = 'رابط غير صالح: ' . $f['label'];
                    $data[$name] = $v !== '' ? normalize_url_input($v) : ($f['nullable'] ? null : $v);
                    break;
                case 'date':
                    $v = trim((string)$raw);
                    $d = \DateTime::createFromFormat('Y-m-d', $v);
                    if ($v === '') {
                        if (!($f['nullable'] ?? false)) $errs[$name] = $f['label'] . ' مطلوب.';
                        $data[$name] = null;
                    } elseif ($d && $d->format('Y-m-d') === $v) {
                        $data[$name] = $v;
                    } else {
                        $errs[$name] = 'تاريخ غير صالح (' . $f['label'] . ').';
                    }
                    break;
                default: // text / textarea
                    $v = trim((string)$raw);
                    if (!($f['nullable'] ?? false) && $v === '') $errs[$name] = $f['label'] . ' مطلوب.';
                    $maxLen = $f['max'] ?? 255;
                    if (mb_strlen($v) > $maxLen) $errs[$name] = $f['label'] . ' طويل جدًا.';
                    $data[$name] = $v;
            }
        }

        if ($errs) {
            flash_set('error', 'أخطاء: ' . implode(' | ', $errs));
            header('Location: ' . url('admin/' . $redirect . ($id ? '?edit=' . $id : '')));
            exit;
        }

        if ($id > 0) {
            $sets = [];
            $params = [];
            foreach ($data as $k => $v) { $sets[] = "`$k` = ?"; $params[] = $v; }
            $params[] = $id;
            q("UPDATE `$table` SET " . implode(', ', $sets) . " WHERE id = ?", $params);
            flash_set('success', 'تم تحديث العنصر.');
        } else {
            if (isset(SORTABLE_TABLES[$table])) {
                $data['sort_order'] = next_sort($table);
            }
            if ($table === 'news') {
                // رابط الخبر يُولَّد من العنوان مرة واحدة ويبقى ثابتًا عند التعديل
                $data['slug'] = unique_news_slug((string)($data['title'] ?? ''));
            }
            $cols = array_keys($data);
            $sql = "INSERT INTO `$table` (`" . implode('`, `', $cols) . "`) VALUES (" . rtrim(str_repeat('?, ', count($cols)), ', ') . ')';
            q($sql, array_values($data));
            flash_set('success', $table === 'news'
                ? 'تمت إضافة الخبر. أضف المقتطف ونص الخبر من روابط «المقتطف» و«المحتوى» في القائمة.'
                : 'تمت الإضافة بنجاح.');
        }
    }

    header('Location: ' . url('admin/' . $redirect));
    exit;
}

/** تعقيم أولي لمحتوى المحرر قبل التخزين (دفاع عميق) */
function clean_html_store(string $html): string
{
    // إزالة الوسوم الخطرة فقط مع الإبقاء على وسوم المحرر الكاملة
    $html = preg_replace('#<(script|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $html) ?? '';
    $html = preg_replace('#<(script|iframe|object|embed|form|link|meta)[^>]*/?>#is', '', $html) ?? '';
    $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? '';
    return $html;
}

/** عرض نموذج CRUD */
function admin_render_form(string $table, array $fields, ?array $row, string $redirect, string $titleSingular): void
{
    $isEdit = $row !== null;
    ?>
<div class="panel">
  <h2 style="margin-top:0"><?= $isEdit ? 'تعديل: ' . e($titleSingular) : 'إضافة ' . e($titleSingular) ?></h2>
  <form method="post" enctype="multipart/form-data" action="<?= url('admin/' . $redirect) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><?php endif; ?>
    <div class="form-grid">
      <?php foreach ($fields as $f):
          $name = $f['name'];
          $val  = $isEdit ? ($row[$name] ?? '') : ($f['default'] ?? '');
      ?>
        <div>
          <label<?= ($f['nullable'] ?? false) ? '' : ' class="req"' ?> for="fld-<?= e($name) ?>"><?= e($f['label']) ?></label>
          <?php if ($f['type'] === 'html'): ?>
            <textarea class="html-editor" id="fld-<?= e($name) ?>" name="<?= e($name) ?>" rows="8"><?= e((string)$val) ?></textarea>
          <?php elseif ($f['type'] === 'textarea'): ?>
            <textarea id="fld-<?= e($name) ?>" name="<?= e($name) ?>" rows="4"><?= e((string)$val) ?></textarea>
          <?php elseif ($f['type'] === 'checkbox'): ?>
            <label style="display:inline-flex;gap:.5rem;align-items:center;font-weight:500">
              <input type="checkbox" id="fld-<?= e($name) ?>" name="<?= e($name) ?>" value="1" <?= (int)$val === 1 ? 'checked' : '' ?>> منشور ومفعّل
            </label>
          <?php elseif ($f['type'] === 'image' || $f['type'] === 'file'): ?>
            <?php if ($isEdit && $val): ?>
              <div style="display:flex;gap:.6rem;align-items:center;margin-bottom:.4rem">
                <?php if (!upload_exists((string)$val, $f['upload'])): ?>
                  <?= admin_missing_badge((string)$val, $f['upload']) ?>
                <?php elseif ($f['type'] === 'image'): ?>
                  <img src="<?= e(upload_url((string)$val, $f['upload'])) ?>" alt="" style="width:74px;height:52px;object-fit:cover;border-radius:6px;border:1px solid var(--c-line)">
                <?php else: ?>
                  <a href="<?= e(upload_url((string)$val, $f['upload'])) ?>" target="_blank" rel="noopener">الملف الحالي ↗</a>
                <?php endif; ?>
                <label style="margin:0;font-weight:500;display:inline-flex;gap:.3rem;align-items:center">
                  <input type="checkbox" name="keep_<?= e($name) ?>" value="<?= e((string)$val) ?>" checked> إبقاء الحالي
                </label>
              </div>
            <?php else: ?>
              <input type="hidden" name="keep_<?= e($name) ?>" value="">
            <?php endif; ?>
            <input type="file" id="fld-<?= e($name) ?>" name="upload_<?= e($name) ?>"
                   accept="<?= $f['type'] === 'file' ? '.pdf' : '.jpg,.jpeg,.png,.webp,.gif,.svg' ?>">
            <small style="color:var(--c-muted)"><?= $f['type'] === 'file' ? 'PDF فقط حتى 10MB' : 'صور JPG/PNG/WebP/GIF/SVG حتى 5MB' ?></small>
          <?php elseif ($f['type'] === 'date'): ?>
            <input type="date" id="fld-<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e((string)$val) ?>">
          <?php elseif ($f['type'] === 'url'): ?>
            <input type="url" dir="ltr" id="fld-<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e((string)$val) ?>" placeholder="https://">
          <?php elseif ($f['type'] === 'number'): ?>
            <input type="number" id="fld-<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e((string)$val) ?>">
          <?php else: ?>
            <input type="text" id="fld-<?= e($name) ?>" name="<?= e($name) ?>" maxlength="<?= (int)($f['max'] ?? 255) ?>" value="<?= e((string)$val) ?>">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <div style="display:flex;gap:.7rem">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'حفظ التعديلات' : 'إضافة' ?></button>
        <?php if ($isEdit): ?><a class="btn btn-outline" href="<?= url('admin/' . $redirect) ?>">إلغاء</a><?php endif; ?>
      </div>
    </div>
  </form>
</div>
    <?php
}

/** عرض جدول القائمة مع أزرار الترتيب والتعديل والحذف */
function admin_render_list(string $table, array $rows, callable $colsFn, string $redirect): void
{
    $sortable = isset(SORTABLE_TABLES[$table]);
    ?>
<div class="table-wrap">
  <table class="data">
    <thead><tr>
      <?php if ($sortable): ?><th>الترتيب</th><?php endif; ?>
      <?php foreach ($colsFn('head') as $h): ?><th><?= e($h) ?></th><?php endforeach; ?>
      <th>الحالة</th>
      <th aria-label="إجراءات">إجراءات</th>
    </tr></thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="20" style="text-align:center;color:var(--c-muted);padding:1.4rem">لا توجد عناصر.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <?php if ($sortable): ?>
        <td style="white-space:nowrap">
          <form class="inline-form" method="post" action="<?= url('admin/' . $redirect) ?>"><?= csrf_field() ?>
            <input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="dir" value="up">
            <button class="btn-icon" type="submit" title="لأعلى" aria-label="نقل لأعلى">▲</button>
          </form>
          <form class="inline-form" method="post" action="<?= url('admin/' . $redirect) ?>"><?= csrf_field() ?>
            <input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="dir" value="down">
            <button class="btn-icon" type="submit" title="لأسفل" aria-label="نقل لأسفل">▼</button>
          </form>
        </td>
        <?php endif; ?>
        <?php foreach ($colsFn('row', $r) as $c): ?>
          <td><?= $c /* مُهيّأ مسبقًا عبر e() في المستدعي */ ?></td>
        <?php endforeach; ?>
        <td><?php if (array_key_exists('is_active', $r)): ?>
              <span class="badge <?= (int)$r['is_active'] === 1 ? 'badge-on' : 'badge-off' ?>"><?= (int)$r['is_active'] === 1 ? 'مفعّل' : 'متوقف' ?></span>
            <?php else: ?>—<?php endif; ?></td>
        <td style="white-space:nowrap">
          <a class="btn-icon" href="<?= url('admin/' . $redirect . '?edit=' . (int)$r['id']) ?>" title="تعديل" aria-label="تعديل العنصر <?= (int)$r['id'] ?>">✎</a>
          <form class="inline-form" method="post" action="<?= url('admin/' . $redirect) ?>" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn-icon" type="submit" title="حذف" aria-label="حذف العنصر <?= (int)$r['id'] ?>"
                    data-confirm="هل أنت متأكد من حذف هذا العنصر نهائيًا؟" style="color:var(--c-danger)">🗑</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
    <?php
}

/* ================================================================== */
/* تنبيه الملفات المفقودة: ملف مسجّل في قاعدة البيانات لكنه غير موجود */
/* في مجلده داخل uploads (فيعرض الموقع صورة بديلة من assets).        */
/* ================================================================== */

/** المسار النسبي المتوقع للملف (للعرض في التنبيه) مثل uploads/news/x.jpg */
function upload_expected_path(?string $stored, string $type): string
{
    return UPLOAD_URL . '/' . upload_dir($type) . '/' . upload_name($stored);
}

/** شارة «الملف مفقود» مع المسار المتوقع. */
function admin_missing_badge(?string $stored, string $type): string
{
    $path = upload_expected_path($stored, $type);
    return '<span class="missing-file" title="' . e('غير موجود على الخادم: ' . $path) . '">'
         . '<span class="badge badge-off">⚠ الملف مفقود</span>'
         . '<small dir="ltr">' . e($path) . '</small></span>';
}

/** حالة حقل نص منسّق في القوائم: «✓ موجود» أو «فارغ». يُحرَّر من نموذج التعديل نفسه. */
function admin_text_status(?string $html): string
{
    $hasText = trim(html_entity_decode(strip_tags((string)$html), ENT_QUOTES, 'UTF-8')) !== ''
            || preg_match('#<(img|table|iframe)#i', (string)$html);
    return $hasText ? '<span class="badge badge-on">✓ موجود</span>' : '<span class="badge badge-off">فارغ</span>';
}

/** صورة مصغرة لقوائم لوحة التحكم: «—» إن لم تُسجَّل صورة، وتنبيه إن كان ملفها مفقودًا. */
function admin_thumb(?string $stored, string $type): string
{
    if (upload_name($stored) === '') return '—';
    if (!upload_exists($stored, $type)) return admin_missing_badge($stored, $type);
    return '<img class="row-thumb" src="' . e(upload_url($stored, $type)) . '" alt="">';
}

/** رابط ملف (مثل PDF) في القوائم، مع تنبيه إن كان مفقودًا. */
function admin_file_link(?string $stored, string $type, string $label = 'PDF ↗'): string
{
    if (upload_name($stored) === '') return '—';
    if (!upload_exists($stored, $type)) return admin_missing_badge($stored, $type);
    return '<a href="' . e(upload_url($stored, $type)) . '" target="_blank" rel="noopener">' . e($label) . '</a>';
}

/**
 * كل الملفات المسجلة في قاعدة البيانات وغير الموجودة على الخادم.
 * @return array<int, array{section:string, item:string, path:string, edit:string}>
 */
function find_missing_uploads(): array
{
    // الجدول => [عمود الملف، نوع الرفع، عمود العنوان، اسم القسم، صفحة الإدارة]
    $sources = [
        'slider'          => ['image',     'slider',     'title',    'السلايدر',          'slider.php'],
        'news'            => ['image',     'news',       'title',    'الأخبار',           'news.php'],
        'products'        => ['image',     'products',   'title',    'المنتجات',          'products.php'],
        'projects'        => ['image',     'projects',   'title',    'المشاريع المستهدفة', 'projects.php'],
        'board_members'   => ['image',     'board',      'name',     'مجلس الإدارة',      'board.php'],
        'executives'      => ['image',     'executives', 'name',     'الإدارة التنفيذية', 'executives.php'],
        'important_links' => ['image',     'links',      'title',    'المواقع الهامة',    'links.php'],
        'reports'         => ['file_path', 'reports',    'title',    'التقارير السنوية',  'reports.php'],
    ];
    $missing = [];
    foreach ($sources as $table => [$col, $type, $titleCol, $section, $page]) {
        $rows = fetchAll("SELECT id, `$titleCol` AS t, `$col` AS f FROM `$table` WHERE `$col` IS NOT NULL AND `$col` <> ''");
        foreach ($rows as $r) {
            if (!upload_exists($r['f'], $type)) {
                $missing[] = ['section' => $section, 'item' => (string)($r['t'] ?: '#' . $r['id']),
                              'path' => upload_expected_path($r['f'], $type),
                              'edit' => url('admin/' . $page . '?edit=' . (int)$r['id'])];
            }
        }
    }
    foreach (fetchAll("SELECT ps.id, ps.page_id, ps.title, ps.image FROM page_sections ps WHERE ps.image IS NOT NULL AND ps.image <> ''") as $r) {
        if (!upload_exists($r['image'], 'sections')) {
            $missing[] = ['section' => 'فقرات الصفحات', 'item' => (string)$r['title'],
                          'path' => upload_expected_path($r['image'], 'sections'),
                          'edit' => url('admin/pages.php?page=' . (int)$r['page_id'] . '&section=' . (int)$r['id'])];
        }
    }
    foreach (BRAND_FILE_SETTINGS as $key => $label) {
        $f = setting($key);
        if ($f !== '' && !upload_exists($f, 'brand')) {
            $missing[] = ['section' => 'إعدادات الموقع', 'item' => $label,
                          'path' => upload_expected_path($f, 'brand'), 'edit' => url('admin/settings.php')];
        }
    }
    return $missing;
}
