<?php
require_once __DIR__ . '/../includes/header.php';
require_role('faculty');

$pdo = db();
$assignedFyp = $pdo->prepare('SELECT COUNT(*) FROM fyp_groups WHERE supervisor = ?');
$assignedFyp->execute([user_name()]);
$assignedCount = (int) $assignedFyp->fetchColumn();

$pendingApprovals = (int) $pdo->query("SELECT COUNT(*) FROM fyp_groups WHERE status = 'pending'")->fetchColumn();
$upcomingEvents = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'active' AND event_date >= CURDATE()")->fetchColumn();
$recentAnnouncements = $pdo->query('SELECT * FROM announcements ORDER BY FIELD(priority,"urgent","important","normal"), created_at DESC LIMIT 4')->fetchAll();
$recentFyp = $pdo->query('SELECT fg.project_title, fg.status, fg.supervisor, u.name FROM fyp_groups fg JOIN users u ON u.id = fg.leader_id ORDER BY fg.created_at DESC LIMIT 4')->fetchAll();
$notifications = fetch_notifications(user_id(), 4);

render_header('Faculty Dashboard', 'dashboard');
?>
<section class="mb-xl grid grid-cols-1 lg:grid-cols-3 gap-lg items-end">
  <div class="lg:col-span-2">
    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Welcome back, <span class="gradient-text"><?= h(user_name()) ?></span></h2>
    <p class="font-body-md text-body-md text-on-surface-variant mb-lg">Your faculty view is now connected for announcements, FYP visibility, and campus event updates.</p>
    <div class="flex flex-wrap gap-md">
      <div class="glass-panel rounded-xl px-lg py-md flex items-center gap-md border-l-4 border-secondary-container"><span class="material-symbols-outlined text-secondary">science</span><div><p class="font-label-md text-label-md text-on-surface-variant uppercase">Assigned FYP Groups</p><p class="font-bold text-xl"><?= $assignedCount ?></p></div></div>
      <div class="glass-panel rounded-xl px-lg py-md flex items-center gap-md border-l-4 border-tertiary"><span class="material-symbols-outlined text-tertiary">schedule</span><div><p class="font-label-md text-label-md text-on-surface-variant uppercase">Pending Reviews</p><p class="font-bold text-xl"><?= $pendingApprovals ?></p></div></div>
      <div class="glass-panel rounded-xl px-lg py-md flex items-center gap-md border-l-4 border-primary"><span class="material-symbols-outlined text-primary">event</span><div><p class="font-label-md text-label-md text-on-surface-variant uppercase">Upcoming Events</p><p class="font-bold text-xl"><?= $upcomingEvents ?></p></div></div>
    </div>
  </div>
</section>

<section class="grid lg:grid-cols-2 gap-lg">
  <div class="glass-card rounded-xl p-lg">
    <div class="flex justify-between items-center mb-md"><h3 class="font-headline-md text-headline-md">Recent FYP Submissions</h3></div>
    <div class="space-y-sm">
      <?php foreach ($recentFyp as $row): ?>
        <div class="rounded-lg bg-surface-container-low border border-white/5 p-md">
          <div class="flex justify-between gap-md">
            <p class="font-bold"><?= h($row['project_title']) ?></p>
            <span class="rounded-full px-sm py-xs text-xs <?= $row['status'] === 'pending' ? 'bg-tertiary/20 text-tertiary' : ($row['status'] === 'approved' ? 'bg-secondary-container/20 text-secondary' : 'bg-error/20 text-error') ?>"><?= h(strtoupper($row['status'])) ?></span>
          </div>
          <p class="text-sm text-on-surface-variant mt-xs"><?= h($row['name']) ?><?= $row['supervisor'] ? ' · Supervisor: ' . h($row['supervisor']) : ' · Supervisor pending' ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (!$recentFyp): ?><p class="text-on-surface-variant">No FYP submissions yet.</p><?php endif; ?>
    </div>
  </div>
  <div class="glass-card rounded-xl p-lg">
    <div class="flex justify-between items-center mb-md"><h3 class="font-headline-md text-headline-md">Latest Announcements</h3><a class="text-secondary text-label-md" href="<?= h(base_url('faculty/announcements.php')) ?>">View all</a></div>
    <div class="space-y-sm">
      <?php foreach ($recentAnnouncements as $a): ?>
        <div class="rounded-lg bg-surface-container-low border border-white/5 p-md">
          <div class="flex justify-between gap-md"><p class="font-bold"><?= h($a['title']) ?></p><span class="rounded-full px-sm py-xs text-xs <?= $a['priority'] === 'urgent' ? 'bg-error/20 text-error' : ($a['priority'] === 'important' ? 'bg-tertiary/20 text-tertiary' : 'bg-secondary-container/20 text-secondary') ?>"><?= h(strtoupper($a['priority'])) ?></span></div>
          <p class="text-sm text-on-surface-variant mt-xs"><?= h($a['message']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="grid lg:grid-cols-3 gap-lg">
  <div class="glass-card rounded-xl p-lg lg:col-span-3"><h3 class="font-headline-md text-headline-md mb-md">My Notifications</h3><div class="space-y-sm"><?php foreach ($notifications as $notification): ?><div class="rounded-lg bg-surface-container-low border border-white/5 p-md text-sm text-on-surface-variant"><?= h($notification['message']) ?></div><?php endforeach; ?><?php if (!$notifications): ?><p class="text-on-surface-variant">No notifications yet.</p><?php endif; ?></div></div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
