<?php
require_once __DIR__ . '/../includes/header.php';
require_role('student');

$pdo = db();
$uid = user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'create') {
        $groupName = trim((string) ($_POST['group_name'] ?? ''));
        $title = trim((string) ($_POST['project_title'] ?? ''));
        $description = trim((string) ($_POST['project_description'] ?? ''));
        if ($groupName === '' || $title === '' || $description === '') {
            flash('error', 'Please complete the FYP group form.');
        } else {
            $stmt = $pdo->prepare('SELECT student_id FROM users WHERE id = ?');
            $stmt->execute([$uid]);
            $studentRegNo = (string) $stmt->fetchColumn();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM fyp_groups fg LEFT JOIN fyp_members fm ON fm.group_id = fg.id WHERE fg.leader_id = ? OR fm.student_reg_no = ?');
            $stmt->execute([$uid, $studentRegNo]);
            if ((int) $stmt->fetchColumn() > 0) {
                flash('error', 'You already belong to an FYP group.');
            } else {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('INSERT INTO fyp_groups (group_name, project_title, project_description, leader_id) VALUES (?, ?, ?, ?)');
                $stmt->execute([$groupName, $title, $description, $uid]);
                $groupId = (int) $pdo->lastInsertId();
                $stmt = $pdo->prepare('INSERT INTO fyp_members (group_id, student_name, student_reg_no, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$groupId, user_name(), $studentRegNo, 'Leader']);
                foreach (['Proposal Defense' => '+14 days', 'Prototype Demo' => '+45 days', 'Final Documentation' => '+90 days'] as $milestone => $offset) {
                    $stmt = $pdo->prepare('INSERT INTO fyp_milestones (group_id, milestone_title, deadline) VALUES (?, ?, ?)');
                    $stmt->execute([$groupId, $milestone, date('Y-m-d', strtotime($offset))]);
                }
                $pdo->commit();
                flash('success', 'FYP group created.');
            }
        }
    } elseif ($action === 'member') {
        $groupId = (int) ($_POST['group_id'] ?? 0);
        $name = trim((string) ($_POST['member_name'] ?? ''));
        $regNo = trim((string) ($_POST['student_reg_no'] ?? ''));
        $role = trim((string) ($_POST['member_role'] ?? 'Member'));
        if ($groupId > 0 && $name !== '' && $regNo !== '') {
            $stmt = $pdo->prepare('INSERT INTO fyp_members (group_id, student_name, student_reg_no, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$groupId, $name, $regNo, $role ?: 'Member']);
            flash('success', 'Member added to group.');
        }
    }
    redirect_to('student/fyp.php');
}

$stmt = $pdo->prepare('SELECT student_id FROM users WHERE id = ?');
$stmt->execute([$uid]);
$studentRegNo = (string) $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT fg.* FROM fyp_groups fg LEFT JOIN fyp_members fm ON fm.group_id = fg.id WHERE fg.leader_id = ? OR fm.student_reg_no = ? ORDER BY fg.id DESC LIMIT 1');
$stmt->execute([$uid, $studentRegNo]);
$group = $stmt->fetch();
$members = $milestones = [];
if ($group) {
    $stmt = $pdo->prepare('SELECT * FROM fyp_members WHERE group_id = ? ORDER BY id');
    $stmt->execute([$group['id']]);
    $members = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT * FROM fyp_milestones WHERE group_id = ? ORDER BY deadline');
    $stmt->execute([$group['id']]);
    $milestones = $stmt->fetchAll();
}
render_header('FYP Hub', 'fyp');
?>
<?php if (!$group): ?>
<section class="glass-card rounded-xl p-lg max-w-3xl">
  <h1 class="font-headline-lg text-headline-lg mb-xs">Create FYP Group</h1>
  <p class="text-on-surface-variant mb-lg">Submit your project title and description for admin review.</p>
  <form class="space-y-md" method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="create">
    <input class="form-input p-md" name="group_name" placeholder="Group name" required>
    <input class="form-input p-md" name="project_title" placeholder="Project title" required>
    <textarea class="form-input p-md min-h-36" name="project_description" placeholder="Project description" required></textarea>
    <button class="px-lg py-md rounded-lg bg-gradient-to-r from-secondary-container to-tertiary-container text-white font-bold">Submit FYP Proposal</button>
  </form>
</section>
<?php else: ?>
<section class="grid lg:grid-cols-3 gap-lg">
  <div class="lg:col-span-2 glass-card rounded-xl p-lg">
    <div class="flex justify-between gap-md flex-wrap"><div><h1 class="font-headline-lg text-headline-lg"><?= h($group['project_title']) ?></h1><p class="text-on-surface-variant"><?= h($group['group_name']) ?></p></div><span class="h-fit rounded-full px-md py-sm bg-secondary-container/20 text-secondary"><?= h(strtoupper($group['status'])) ?></span></div>
    <p class="text-on-surface-variant mt-md"><?= h($group['project_description']) ?></p>
    <div class="grid sm:grid-cols-2 gap-md mt-lg">
      <div class="rounded-lg bg-surface-container-low p-md border border-white/5"><p class="text-sm text-on-surface-variant">Supervisor</p><p class="font-bold"><?= h($group['supervisor'] ?: 'Awaiting assignment') ?></p></div>
      <div class="rounded-lg bg-surface-container-low p-md border border-white/5"><p class="text-sm text-on-surface-variant">Admin Remarks</p><p class="font-bold"><?= h($group['admin_remarks'] ?: 'No remarks yet') ?></p></div>
    </div>
  </div>
  <form class="glass-card rounded-xl p-lg space-y-md" method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="member"><input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
    <h2 class="font-headline-md text-headline-md">Add Member</h2>
    <input class="form-input p-md" name="member_name" placeholder="Member name" required>
    <input class="form-input p-md" name="student_reg_no" placeholder="Registration number" required>
    <input class="form-input p-md" name="member_role" placeholder="Member role" value="Member">
    <button class="w-full py-sm rounded-lg bg-secondary-container text-white font-bold">Add Member</button>
  </form>
</section>
<section class="grid lg:grid-cols-2 gap-lg">
  <div class="glass-card rounded-xl p-lg"><h2 class="font-headline-md text-headline-md mb-md">Members</h2><div class="space-y-sm"><?php foreach ($members as $m): ?><div class="rounded-lg bg-surface-container-low border border-white/5 p-md"><p class="font-bold"><?= h($m['student_name']) ?></p><p class="text-sm text-on-surface-variant"><?= h($m['student_reg_no']) ?> · <?= h($m['role']) ?></p></div><?php endforeach; ?></div></div>
  <div class="glass-card rounded-xl p-lg"><h2 class="font-headline-md text-headline-md mb-md">Milestones</h2><div class="space-y-sm"><?php foreach ($milestones as $m): ?><div class="rounded-lg bg-surface-container-low border border-white/5 p-md flex justify-between gap-md"><div><p class="font-bold"><?= h($m['milestone_title']) ?></p><p class="text-sm text-on-surface-variant">Due <?= h(date('M d, Y', strtotime((string) $m['deadline']))) ?></p></div><span class="h-fit rounded-full px-sm py-xs text-xs bg-tertiary/20 text-tertiary"><?= h(strtoupper(str_replace('_', ' ', $m['status']))) ?></span></div><?php endforeach; ?></div></div>
</section>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
