<?php
require_once __DIR__ . '/../includes/header.php';
require_role('student');

$pdo = db();
$uid = user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $priority = (string) ($_POST['priority'] ?? 'medium');
    $description = trim((string) ($_POST['description'] ?? ''));
    $validPriorities = ['low', 'medium', 'high', 'urgent'];

    if ($title === '' || $category === '' || $description === '' || !in_array($priority, $validPriorities, true)) {
        flash('error', 'Please complete all complaint fields.');
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND category = ? AND status IN ('submitted','under_review','in_progress')");
        $stmt->execute([$uid, $category]);
        if ((int) $stmt->fetchColumn() > 0) {
            flash('error', 'You already have an active complaint in this category. Please wait until it is resolved.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO complaints (student_id, category, priority, title, description, status) VALUES (?, ?, ?, ?, ?, "submitted")');
            $stmt->execute([$uid, $category, $priority, $title, $description]);
            flash('success', 'Complaint submitted successfully.');
        }
    }
    redirect_to('student/complaints.php');
}

$stmt = $pdo->prepare('SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC');
$stmt->execute([$uid]);
$complaints = $stmt->fetchAll();
render_header('My Complaints', 'complaints');
?>
<section class="grid lg:grid-cols-3 gap-lg">
  <form class="lg:col-span-1 glass-card rounded-xl p-lg space-y-md" method="post">
    <?= csrf_field() ?>
    <div><h1 class="font-headline-lg text-headline-lg">Submit Complaint</h1><p class="text-on-surface-variant">Active duplicate categories are blocked until resolved.</p></div>
    <input class="form-input p-md" name="title" placeholder="Complaint title" required>
    <select class="form-input p-md" name="category" required>
      <option value="">Select category</option>
      <?php foreach (['Academic','Facilities','IT','Transport','Security','Other'] as $cat): ?><option><?= h($cat) ?></option><?php endforeach; ?>
    </select>
    <select class="form-input p-md" name="priority" required>
      <?php foreach (['low','medium','high','urgent'] as $priority): ?><option value="<?= h($priority) ?>"><?= h(ucfirst($priority)) ?></option><?php endforeach; ?>
    </select>
    <textarea class="form-input p-md min-h-40" name="description" placeholder="Describe the issue clearly..." required></textarea>
    <button class="w-full py-md rounded-lg bg-gradient-to-r from-secondary-container to-tertiary-container text-white font-bold">Submit Complaint</button>
  </form>
  <div class="lg:col-span-2 glass-card rounded-xl overflow-hidden">
    <div class="px-lg py-md border-b border-white/10 bg-white/5"><h2 class="font-headline-md text-headline-md">My Complaint History</h2></div>
    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-surface-container-high text-on-surface-variant"><tr><th class="p-md">Title</th><th class="p-md">Priority</th><th class="p-md">Status</th><th class="p-md">Admin Response</th><th class="p-md">Date</th></tr></thead>
        <tbody>
        <?php foreach ($complaints as $row): ?>
          <tr class="border-t border-white/5 hover:bg-white/5">
            <td class="p-md font-bold"><?= h($row['title']) ?><p class="text-sm text-on-surface-variant font-normal"><?= h($row['category']) ?> · <?= h($row['description']) ?></p></td>
            <td class="p-md"><span class="rounded-full px-sm py-xs text-xs <?= in_array($row['priority'], ['urgent','high'], true) ? 'bg-error/20 text-error' : 'bg-secondary-container/20 text-secondary' ?>"><?= h(strtoupper($row['priority'])) ?></span></td>
            <td class="p-md"><?= h(str_replace('_', ' ', ucfirst($row['status']))) ?></td>
            <td class="p-md text-on-surface-variant"><?= h($row['admin_response'] ?: 'Awaiting admin response') ?></td>
            <td class="p-md text-sm text-on-surface-variant"><?= h(date('M d, Y', strtotime($row['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$complaints): ?><tr><td class="p-lg text-on-surface-variant" colspan="5">No complaints submitted yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
