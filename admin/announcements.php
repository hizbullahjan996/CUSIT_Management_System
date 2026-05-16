<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    $body = trim((string) ($_POST['body'] ?? ''));
    $priority = (string) ($_POST['priority'] ?? 'normal');
    if ($title === '' || $body === '' || !in_array($priority, ['normal','important','urgent'], true)) {
        flash('error', 'Please complete the announcement form.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO announcements (title, message, priority, created_by) VALUES (?, ?, ?, ?)');
        $stmt->execute([$title, $body, $priority, user_id()]);
        if ($priority === 'urgent') {
            $students = $pdo->query("SELECT id FROM users WHERE role = 'student'")->fetchAll();
            foreach ($students as $student) {
                add_notification((int) $student['id'], 'Urgent announcement: ' . $title);
            }
        }
        flash('success', 'Announcement published.');
    }
    redirect_to('admin/announcements.php');
}

$announcements = $pdo->query('SELECT a.*, u.name AS author FROM announcements a LEFT JOIN users u ON u.id = a.created_by ORDER BY a.created_at DESC')->fetchAll();
render_header('Announcements', 'announcements');
?>
<section class="grid lg:grid-cols-3 gap-lg">
  <form class="glass-card rounded-xl p-lg space-y-md" method="post">
    <?= csrf_field() ?>
    <div><h1 class="font-headline-lg text-headline-lg">Publish Announcement</h1><p class="text-on-surface-variant">Students will see priority badges instantly.</p></div>
    <input class="form-input p-md" name="title" placeholder="Announcement title" required>
    <select class="form-input p-md" name="priority"><?php foreach (['normal','important','urgent'] as $p): ?><option value="<?= h($p) ?>"><?= h(ucfirst($p)) ?></option><?php endforeach; ?></select>
    <textarea class="form-input p-md min-h-40" name="body" placeholder="Announcement body" required></textarea>
    <button class="w-full py-md rounded-lg bg-gradient-to-r from-secondary-container to-tertiary-container text-white font-bold">Publish</button>
  </form>
  <div class="lg:col-span-2 glass-card rounded-xl p-lg">
    <h2 class="font-headline-md text-headline-md mb-md">Published Feed</h2>
    <div class="space-y-md">
      <?php foreach ($announcements as $a): ?>
        <article class="rounded-xl bg-surface-container-low border border-white/5 p-md">
          <div class="flex justify-between gap-md flex-wrap"><h3 class="font-bold text-lg"><?= h($a['title']) ?></h3><span class="rounded-full px-sm py-xs text-xs <?= $a['priority'] === 'urgent' ? 'bg-error/20 text-error' : ($a['priority'] === 'important' ? 'bg-tertiary/20 text-tertiary' : 'bg-secondary-container/20 text-secondary') ?>"><?= h(strtoupper($a['priority'])) ?></span></div>
          <p class="text-on-surface-variant mt-sm"><?= h($a['message']) ?></p>
          <p class="text-xs text-on-surface-variant mt-md">Published <?= h(date('M d, Y h:i A', strtotime($a['created_at']))) ?> by <?= h($a['author'] ?? 'Admin') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
