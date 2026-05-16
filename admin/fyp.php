<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'group') {
        $id = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'pending');
        $supervisor = trim((string) ($_POST['supervisor'] ?? ''));
        $feedback = trim((string) ($_POST['admin_feedback'] ?? ''));
        if ($id > 0 && in_array($status, ['pending','approved','rejected','in_progress','completed'], true)) {
            $stmt = $pdo->prepare('SELECT leader_id, status FROM fyp_groups WHERE id = ?');
            $stmt->execute([$id]);
            $groupState = $stmt->fetch();
            $stmt = $pdo->prepare('UPDATE fyp_groups SET status = ?, supervisor = ?, admin_remarks = ? WHERE id = ?');
            $stmt->execute([$status, $supervisor ?: null, $feedback ?: null, $id]);
            if ($groupState && $groupState['status'] !== $status) {
                add_notification((int) $groupState['leader_id'], 'Your FYP group status has been updated to ' . str_replace('_', ' ', $status) . '.');
            }
            flash('success', 'FYP group updated.');
        }
    } elseif ($action === 'milestone') {
        $id = (int) ($_POST['milestone_id'] ?? 0);
        $status = (string) ($_POST['milestone_status'] ?? 'pending');
        if ($id > 0 && in_array($status, ['pending','in_progress','completed'], true)) {
            $stmt = $pdo->prepare('UPDATE fyp_milestones SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);
            flash('success', 'Milestone updated.');
        }
    }
    redirect_to('admin/fyp.php');
}

$groups = $pdo->query('SELECT fg.*, u.name AS leader_name, u.student_id AS leader_reg_no FROM fyp_groups fg JOIN users u ON u.id = fg.leader_id ORDER BY FIELD(fg.status,"pending","approved","in_progress","completed","rejected"), fg.created_at DESC')->fetchAll();
$milestonesByGroup = [];
$allMilestones = $pdo->query('SELECT * FROM fyp_milestones ORDER BY deadline')->fetchAll();
foreach ($allMilestones as $m) {
    $milestonesByGroup[(int) $m['group_id']][] = $m;
}
render_header('FYP Management', 'fyp');
?>
<section><h1 class="font-headline-lg text-headline-lg text-on-surface mb-xs">FYP Milestone Tracker</h1><p class="text-on-surface-variant">Approve projects, assign supervisors, and update milestone status.</p></section>
<section class="grid gap-lg">
  <?php foreach ($groups as $group): ?>
    <article class="glass-card rounded-xl p-lg">
      <div class="grid lg:grid-cols-3 gap-lg">
        <div class="lg:col-span-2">
          <div class="flex justify-between gap-md flex-wrap"><div><h2 class="font-headline-md text-headline-md"><?= h($group['project_title']) ?></h2><p class="text-on-surface-variant"><?= h($group['group_name']) ?> led by <?= h($group['leader_name']) ?></p></div><span class="h-fit rounded-full px-md py-sm bg-secondary-container/20 text-secondary"><?= h(strtoupper($group['status'])) ?></span></div>
          <p class="text-on-surface-variant mt-md"><?= h($group['project_description']) ?></p>
          <div class="grid md:grid-cols-3 gap-sm mt-md">
            <?php foreach ($milestonesByGroup[(int) $group['id']] ?? [] as $m): ?>
              <form class="rounded-lg bg-surface-container-low border border-white/5 p-md space-y-sm" method="post">
                <?= csrf_field() ?><input type="hidden" name="action" value="milestone"><input type="hidden" name="milestone_id" value="<?= (int) $m['id'] ?>">
                <p class="font-bold"><?= h($m['milestone_title']) ?></p><p class="text-xs text-on-surface-variant">Due <?= h(date('M d, Y', strtotime((string) $m['deadline']))) ?></p>
                <select class="form-input p-sm" name="milestone_status"><?php foreach (['pending','in_progress','completed'] as $s): ?><option value="<?= h($s) ?>" <?= $m['status'] === $s ? 'selected' : '' ?>><?= h(ucwords(str_replace('_', ' ', $s))) ?></option><?php endforeach; ?></select>
                <button class="w-full py-xs rounded-lg bg-tertiary/20 text-tertiary">Save</button>
              </form>
            <?php endforeach; ?>
          </div>
        </div>
        <form class="space-y-sm" method="post">
          <?= csrf_field() ?><input type="hidden" name="action" value="group"><input type="hidden" name="id" value="<?= (int) $group['id'] ?>">
          <select class="form-input p-md" name="status"><?php foreach (['pending','approved','rejected','in_progress','completed'] as $s): ?><option value="<?= h($s) ?>" <?= $group['status'] === $s ? 'selected' : '' ?>><?= h(ucwords(str_replace('_', ' ', $s))) ?></option><?php endforeach; ?></select>
          <input class="form-input p-md" name="supervisor" placeholder="Supervisor name" value="<?= h($group['supervisor'] ?? '') ?>">
          <textarea class="form-input p-md" name="admin_feedback" placeholder="Admin remarks"><?= h($group['admin_remarks'] ?? '') ?></textarea>
          <button class="w-full py-md rounded-lg bg-gradient-to-r from-secondary-container to-tertiary-container text-white font-bold">Update Group</button>
        </form>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$groups): ?><div class="glass-card rounded-xl p-lg text-on-surface-variant">No FYP groups submitted yet.</div><?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
