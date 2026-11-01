<?php
/**
 * الرسائل الواردة (تواصل / مقترحات / شكاوى) — عرض وحذف فقط.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete' && $id > 0 && fetchOne('SELECT id FROM contact_messages WHERE id = ?', [$id])) {
        q('DELETE FROM contact_messages WHERE id = ?', [$id]);
        flash_set('success', 'تم حذف الرسالة.');
    }
    header('Location: ' . url('admin/messages.php'));
    exit;
}

$typeFilter = (string)($_GET['type'] ?? '');
$allowedTypes = ['contact', 'suggestion', 'complaint'];
$params = [];
$where  = '';
if (in_array($typeFilter, $allowedTypes, true)) {
    $where = 'WHERE type = ?';
    $params[] = $typeFilter;
}
$rows = fetchAll("SELECT * FROM contact_messages $where ORDER BY created_at DESC, id DESC LIMIT 200", $params);

$typeLabels = ['contact' => 'تواصل', 'suggestion' => 'اقتراح', 'complaint' => 'شكوى'];

$ADMIN_TITLE  = 'الرسائل الواردة';
$ADMIN_ACTIVE = 'messages';
require __DIR__ . '/_top.php';
?>
<div class="toolbar-forms">
  <a class="btn <?= $typeFilter === '' ? 'btn-primary' : 'btn-outline' ?>" href="<?= e(url('admin/messages.php')) ?>">الكل</a>
  <?php foreach ($typeLabels as $k => $lbl): ?>
    <a class="btn <?= $typeFilter === $k ? 'btn-primary' : 'btn-outline' ?>" href="<?= e(url('admin/messages.php?type=' . $k)) ?>"><?= e($lbl) ?></a>
  <?php endforeach; ?>
</div>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th>#</th><th>النوع</th><th>الاسم</th><th>البريد</th><th>الجوال</th><th>الرسالة</th><th>التاريخ</th><th>إجراء</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><span class="badge <?= $r['type'] === 'complaint' ? 'badge-off' : 'badge-on' ?>"><?= e($typeLabels[$r['type']] ?? $r['type']) ?></span></td>
        <td><?= e($r['name']) ?></td>
        <td dir="ltr"><?= $r['email'] ? '<a href="mailto:' . e($r['email']) . '">' . e($r['email']) . '</a>' : '—' ?></td>
        <td dir="ltr"><?= e($r['phone'] ?: '—') ?></td>
        <td style="max-width:340px"><?= nl2br(e($r['message'])) ?></td>
        <td style="white-space:nowrap"><?= e(str_replace('T', ' ', substr((string)$r['created_at'], 0, 16))) ?></td>
        <td>
          <form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn-icon" type="submit" data-confirm="حذف هذه الرسالة نهائيًا؟" style="color:var(--c-danger)" aria-label="حذف الرسالة">🗑</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="8" style="text-align:center;color:var(--c-muted);padding:1.4rem">لا توجد رسائل.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php admin_footer(); ?>
