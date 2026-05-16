<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? 'submitted');
    $response = trim((string) ($_POST['admin_response'] ?? ''));
    if ($id > 0 && in_array($status, ['submitted','under_review','in_progress','resolved','rejected'], true)) {
        $stmt = $pdo->prepare('SELECT student_id, status FROM complaints WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        $stmt = $pdo->prepare('UPDATE complaints SET status = ?, admin_response = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $response ?: null, $id]);
        if ($existing && $existing['status'] !== $status) {
            add_notification((int) $existing['student_id'], 'Your complaint status has been updated to ' . str_replace('_', ' ', $status) . '.');
        }
        flash('success', 'Complaint updated.');
    }
    redirect_to('admin/complaints.php');
}

$status = (string) ($_GET['status'] ?? '');
$priority = (string) ($_GET['priority'] ?? '');
$where = [];
$params = [];
if (in_array($status, ['submitted','under_review','in_progress','resolved','rejected'], true)) {
    $where[] = 'c.status = ?';
    $params[] = $status;
}
if (in_array($priority, ['low','medium','high','urgent'], true)) {
    $where[] = 'c.priority = ?';
    $params[] = $priority;
}
$sql = 'SELECT c.*, u.name, u.email, u.student_id AS reg_no FROM complaints c JOIN users u ON u.id = c.student_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY FIELD(c.priority,'urgent','high','medium','low'), c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();
$stats = [
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM complaints')->fetchColumn(),
    'pending' => (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('submitted','under_review')")->fetchColumn(),
    'resolved' => (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'")->fetchColumn(),
    'urgent' => (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE priority = 'urgent' AND status <> 'resolved'")->fetchColumn(),
];
render_header('Complaint Management', 'complaints');
?>
<section class="flex justify-between items-end gap-md flex-wrap">
  <div><h1 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Complaint Management</h1><p class="text-on-surface-variant">Campus-wide issue tracking with admin responses.</p></div>
  <form class="flex gap-sm" method="get">
    <select class="form-input p-sm" name="status"><option value="">All statuses</option><?php foreach (['submitted','under_review','in_progress','resolved','rejected'] as $s): ?><option value="<?= h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= h(ucwords(str_replace('_', ' ', $s))) ?></option><?php endforeach; ?></select>
    <select class="form-input p-sm" name="priority"><option value="">All priorities</option><?php foreach (['low','medium','high','urgent'] as $p): ?><option value="<?= h($p) ?>" <?= $priority === $p ? 'selected' : '' ?>><?= h(ucfirst($p)) ?></option><?php endforeach; ?></select>
    <button class="glass-card px-md py-sm rounded-lg">Filter</button>
  </form>
</section>
<section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-lg">
  <?php foreach ([['Total Submitted',$stats['total'],'assignment','border-secondary-container'],['Pending',$stats['pending'],'pending','border-tertiary'],['Resolved',$stats['resolved'],'check_circle','border-primary'],['Critical Alerts',$stats['urgent'],'warning','border-error']] as [$label,$value,$icon,$border]): ?>
    <div class="glass-card rounded-xl p-md border-l-4 <?= h($border) ?>"><div class="flex justify-between"><span class="text-on-surface-variant"><?= h($label) ?></span><span class="material-symbols-outlined text-secondary"><?= h($icon) ?></span></div><div class="text-headline-lg font-headline-lg mt-md <?= $border === 'border-error' ? 'text-error' : '' ?>"><?= h($value) ?></div></div>
  <?php endforeach; ?>
</section>
<section class="glass-card rounded-xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead class="bg-surface-container-high text-on-surface-variant"><tr><th class="p-md">Student</th><th class="p-md">Complaint</th><th class="p-md">Status</th><th class="p-md">Response</th><th class="p-md">Action</th></tr></thead>
      <tbody>
      <?php foreach ($complaints as $row): ?>
        <tr class="border-t border-white/5 <?= $row['priority'] === 'urgent' ? 'bg-error/5' : '' ?>">
          <td class="p-md"><p class="font-bold"><?= h($row['name']) ?></p><p class="text-sm text-on-surface-variant"><?= h($row['reg_no']) ?> · <?= h($row['email']) ?></p></td>
          <td class="p-md"><p class="font-bold"><?= h($row['title']) ?> <span class="text-xs rounded-full px-sm py-xs <?= in_array($row['priority'], ['urgent','high'], true) ? 'bg-error/20 text-error' : 'bg-secondary-container/20 text-secondary' ?>"><?= h(strtoupper($row['priority'])) ?></span></p><p class="text-sm text-on-surface-variant"><?= h($row['category']) ?> · <?= h($row['description']) ?></p></td>
          <td class="p-md"><?= h(ucwords(str_replace('_', ' ', $row['status']))) ?></td>
          <td class="p-md text-sm text-on-surface-variant"><?= h($row['admin_response'] ?: 'No response yet') ?></td>
          <td class="p-md">
            <form class="space-y-sm min-w-64" method="post">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
              <select class="form-input p-sm" name="status"><?php foreach (['submitted','under_review','in_progress','resolved','rejected'] as $s): ?><option value="<?= h($s) ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= h(ucwords(str_replace('_', ' ', $s))) ?></option><?php endforeach; ?></select>
              <textarea class="form-input p-sm" name="admin_response" placeholder="Admin response"><?= h($row['admin_response'] ?? '') ?></textarea>
              <button class="w-full py-sm rounded-lg bg-secondary-container text-white font-bold">Update</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$complaints): ?><tr><td class="p-lg text-on-surface-variant" colspan="5">No complaints match the current filters.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
