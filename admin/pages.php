<?php
/**
 * إدارة الصفحات العامة + أقسامها (page_sections).
 * قائمة الصفحات، ثم ?page=ID لإدارة فقرات صفحة معينة بمحرر TinyMCE.
 */
declare(strict_types=1);

require_once __DIR__ . '/_crud.php';
require_admin();

$action = $_POST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    if ($action === 'add_page') {
        $title = trim((string)($_POST['title'] ?? ''));
        $slug  = trim((string)($_POST['slug'] ?? ''));
        if ($title === '') {
            flash_set('error', 'عنوان الصفحة مطلوب.');
        } else {
            $slug = $slug !== '' ? make_slug($slug) : make_slug($title);
            if (fetchValue('SELECT id FROM pages WHERE slug = ?', [$slug])) {
                flash_set('error', 'يوجد صفحة بنفس الـ slug.');
            } else {
                q('INSERT INTO pages (slug, title, sort_order) VALUES (?, ?, ?)',
                  [$slug, $title, next_sort('pages')]);
                flash_set('success', 'تمت إضافة الصفحة.');
            }
        }
    } elseif ($action === 'del_page') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && fetchOne('SELECT id FROM pages WHERE id = ?', [$id])) {
            // حذف الصور المرفوعة للأقسام أولًا
            foreach (fetchAll('SELECT image FROM page_sections WHERE page_id = ?', [$id]) as $s) {
                delete_upload($s['image']);
            }
            q('DELETE FROM pages WHERE id = ?', [$id]); // الأقسام تحذف تلقائيًا (FK CASCADE)
            flash_set('success', 'تم حذف الصفحة وأقسامها.');
        }
    } elseif ($action === 'save_section') {
        $pageId = (int)($_POST['page_id'] ?? 0);
        $secId  = (int)($_POST['section_id'] ?? 0);
        $title  = trim((string)($_POST['title'] ?? ''));
        $content = clean_html_store(trim((string)($_POST['content_html'] ?? '')));
        if (!$pageId || !fetchOne('SELECT id FROM pages WHERE id = ?', [$pageId])) {
            flash_set('error', 'الصفحة غير موجودة.');
        } elseif ($title === '') {
            flash_set('error', 'عنوان الفقرة مطلوب.');
        } else {
            $img = null;
            $keep = trim((string)($_POST['keep_image'] ?? ''));
            if (!empty($_FILES['upload_image']['name'])) {
                [$ok, $res] = upload_file('upload_image', 'products', IMAGE_MIMES);
                if (!$ok) { flash_set('error', 'صورة الفقرة: ' . $res); header('Location: ' . url('admin/pages.php?page=' . $pageId)); exit; }
                if (strpos($keep, 'uploads/') === 0) delete_upload($keep);
                $img = $res;
            } else {
                $img = $keep !== '' ? $keep : null;
            }
            if ($secId > 0 && fetchOne('SELECT id FROM page_sections WHERE id = ? AND page_id = ?', [$secId, $pageId])) {
                q('UPDATE page_sections SET title = ?, image = ?, content_html = ? WHERE id = ?',
                  [$title, $img, $content !== '' ? $content : null, $secId]);
                flash_set('success', 'تم تحديث الفقرة.');
            } else {
                q('INSERT INTO page_sections (page_id, title, image, content_html, sort_order) VALUES (?, ?, ?, ?, ?)',
                  [$pageId, $title, $img, $content !== '' ? $content : null, next_sort('page_sections')]);
                flash_set('success', 'تمت إضافة الفقرة.');
            }
        }
        header('Location: ' . url('admin/pages.php?page=' . $pageId));
        exit;
    } elseif ($action === 'del_section') {
        $secId  = (int)($_POST['section_id'] ?? 0);
        $pageId = (int)($_POST['page_id'] ?? 0);
        $sec = fetchOne('SELECT * FROM page_sections WHERE id = ? AND page_id = ?', [$secId, $pageId]);
        if ($sec) {
            delete_upload($sec['image']);
            q('DELETE FROM page_sections WHERE id = ?', [$secId]);
            flash_set('success', 'تم حذف الفقرة.');
        }
        header('Location: ' . url('admin/pages.php?page=' . $pageId));
        exit;
    } elseif ($action === 'move_section') {
        $secId = (int)($_POST['section_id'] ?? 0);
        $dir   = ($_POST['dir'] ?? '') === 'up' ? 'up' : 'down';
        move_item('page_sections', $secId, $dir);
        header('Location: ' . url('admin/pages.php?page=' . (int)($_POST['page_id'] ?? 0)));
        exit;
    } elseif ($action === 'move_page') {
        move_item('pages', (int)($_POST['id'] ?? 0), ($_POST['dir'] ?? '') === 'up' ? 'up' : 'down');
    }
    header('Location: ' . url('admin/pages.php' . (isset($_POST['page_id']) ? '?page=' . (int)$_POST['page_id'] : '')));
    exit;
}

$pageId = (int)($_GET['page'] ?? 0);
$curPage = null;
$sections = [];
if ($pageId > 0) {
    $curPage = fetchOne('SELECT * FROM pages WHERE id = ?', [$pageId]); // prepared — منع IDOR
    if (!$curPage) {
        flash_set('error', 'الصفحة غير موجودة.');
        header('Location: ' . url('admin/pages.php'));
        exit;
    }
    $sections = fetchAll('SELECT * FROM page_sections WHERE page_id = ? ORDER BY sort_order ASC, id ASC', [$pageId]);
}
$allPages = fetchAll('SELECT p.*, (SELECT COUNT(*) FROM page_sections ps WHERE ps.page_id = p.id) AS sections_count FROM pages p ORDER BY p.sort_order ASC, p.id ASC');

$EDIT_SECTION = null;
if (isset($_GET['section'])) {
    $sid = (int)$_GET['section'];
    $EDIT_SECTION = fetchOne('SELECT * FROM page_sections WHERE id = ? AND page_id = ?', [$sid, $pageId]);
    if (!$EDIT_SECTION) { $EDIT_SECTION = null; }
}

$ADMIN_TITLE  = $curPage ? ('أقسام صفحة: ' . $curPage['title']) : 'إدارة الصفحات';
$ADMIN_ACTIVE = 'pages';
require __DIR__ . '/_top.php';
?>

<?php if (!$curPage): ?>
<div class="panel">
  <h2 style="margin-top:0">الصفحات العامة</h2>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>الترتيب</th><th>العنوان</th><th>slug</th><th>عدد الفقرات</th><th>إجراءات</th></tr></thead>
      <tbody>
      <?php foreach ($allPages as $p): ?>
        <tr>
          <td style="white-space:nowrap">
            <form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move_page"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="dir" value="up"><button class="btn-icon" type="submit" aria-label="لأعلى">▲</button></form>
            <form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move_page"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="dir" value="down"><button class="btn-icon" type="submit" aria-label="لأسفل">▼</button></form>
          </td>
          <td><a href="<?= e(url('admin/pages.php?page=' . (int)$p['id'])) ?>"><strong><?= e($p['title']) ?></strong></a></td>
          <td dir="ltr"><?= e($p['slug']) ?></td>
          <td><?= (int)$p['sections_count'] ?></td>
          <td>
            <a class="btn btn-outline" style="padding:.3rem .8rem" href="<?= e(url('admin/pages.php?page=' . (int)$p['id'])) ?>">إدارة الفقرات</a>
            <form class="inline-form" method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="del_page"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn-icon" type="submit" data-confirm="سيتم حذف الصفحة وكل فقراتها. هل أنت متأكد؟" style="color:var(--c-danger)" aria-label="حذف الصفحة">🗑</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="panel">
  <h2 style="margin-top:0">إضافة صفحة جديدة</h2>
  <form method="post" class="form-grid" style="display:grid;gap:.8rem;max-width:560px">
    <?= csrf_field() ?><input type="hidden" name="action" value="add_page">
    <div><label class="req" for="ptitle">عنوان الصفحة</label><input type="text" id="ptitle" name="title" required maxlength="200"></div>
    <div><label for="pslug">slug (اختياري — يُولَّد من العنوان)</label><input type="text" id="pslug" name="slug" dir="ltr" maxlength="120"></div>
    <div><button type="submit" class="btn btn-primary">إضافة</button></div>
  </form>
</div>
<?php else: ?>
<p><a href="<?= e(url('admin/pages.php')) ?>">← كل الصفحات</a></p>

<div class="panel">
  <h2 style="margin-top:0"><?= $EDIT_SECTION ? 'تعديل فقرة' : 'إضافة فقرة جديدة' ?> — <?= e($curPage['title']) ?></h2>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_section">
    <input type="hidden" name="page_id" value="<?= (int)$pageId ?>">
    <?php if ($EDIT_SECTION): ?><input type="hidden" name="section_id" value="<?= (int)$EDIT_SECTION['id'] ?>"><?php endif; ?>
    <div class="form-grid">
      <div>
        <label class="req" for="stitle">عنوان الفقرة</label>
        <input type="text" id="stitle" name="title" required maxlength="200" value="<?= e($EDIT_SECTION['title'] ?? '') ?>">
      </div>
      <div>
        <label for="simg">صورة الفقرة (اختيارية)</label>
        <?php if (!empty($EDIT_SECTION['image'])): ?>
          <div style="display:flex;gap:.6rem;align-items:center;margin-bottom:.4rem">
            <img src="<?= e(upload_url($EDIT_SECTION['image'])) ?>" alt="" style="width:74px;height:52px;object-fit:cover;border-radius:6px;border:1px solid var(--c-line)">
            <label style="margin:0;font-weight:500;display:inline-flex;gap:.3rem;align-items:center">
              <input type="checkbox" name="keep_image" value="<?= e($EDIT_SECTION['image']) ?>" checked> إبقاء الحالية
            </label>
          </div>
        <?php else: ?>
          <input type="hidden" name="keep_image" value="">
        <?php endif; ?>
        <input type="file" id="simg" name="upload_image" accept=".jpg,.jpeg,.png,.webp,.gif,.svg">
      </div>
      <div style="grid-column:1/-1">
        <label for="scontent">المحتوى المنسّق</label>
        <textarea class="html-editor" id="scontent" name="content_html" rows="10"><?= e($EDIT_SECTION['content_html'] ?? '') ?></textarea>
      </div>
      <div style="display:flex;gap:.7rem">
        <button type="submit" class="btn btn-primary"><?= $EDIT_SECTION ? 'حفظ التعديلات' : 'إضافة الفقرة' ?></button>
        <?php if ($EDIT_SECTION): ?><a class="btn btn-outline" href="<?= e(url('admin/pages.php?page=' . $pageId)) ?>">إلغاء</a><?php endif; ?>
      </div>
    </div>
  </form>
</div>

<div class="table-wrap">
  <table class="data">
    <thead><tr><th>الترتيب</th><th>الصورة</th><th>العنوان</th><th>المحتوى</th><th>إجراءات</th></tr></thead>
    <tbody>
    <?php foreach ($sections as $s): ?>
      <tr>
        <td style="white-space:nowrap">
          <form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move_section"><input type="hidden" name="page_id" value="<?= (int)$pageId ?>"><input type="hidden" name="section_id" value="<?= (int)$s['id'] ?>"><input type="hidden" name="dir" value="up"><button class="btn-icon" type="submit" aria-label="لأعلى">▲</button></form>
          <form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move_section"><input type="hidden" name="page_id" value="<?= (int)$pageId ?>"><input type="hidden" name="section_id" value="<?= (int)$s['id'] ?>"><input type="hidden" name="dir" value="down"><button class="btn-icon" type="submit" aria-label="لأسفل">▼</button></form>
        </td>
        <td><?= $s['image'] ? '<img class="row-thumb" src="' . e(upload_url($s['image'])) . '" alt="">' : '—' ?></td>
        <td><?= e($s['title']) ?></td>
        <td><?= mb_substr(strip_tags((string)$s['content_html']), 0, 50) !== '' ? e(mb_substr(strip_tags((string)$s['content_html']), 0, 50)) . '…' : '<span style="color:var(--c-muted)">فارغة</span>' ?></td>
        <td style="white-space:nowrap">
          <a class="btn-icon" href="<?= e(url('admin/pages.php?page=' . $pageId . '&section=' . (int)$s['id'])) ?>" title="تعديل" aria-label="تعديل الفقرة">✎</a>
          <form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="del_section"><input type="hidden" name="page_id" value="<?= (int)$pageId ?>"><input type="hidden" name="section_id" value="<?= (int)$s['id'] ?>">
            <button class="btn-icon" type="submit" data-confirm="حذف هذه الفقرة نهائيًا؟" style="color:var(--c-danger)" aria-label="حذف الفقرة">🗑</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$sections): ?><tr><td colspan="5" style="text-align:center;color:var(--c-muted);padding:1.2rem">لا توجد فقرات بعد.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php admin_footer(); ?>
