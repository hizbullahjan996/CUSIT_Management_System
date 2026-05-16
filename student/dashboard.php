<?php
require_once __DIR__ . '/../includes/header.php';
require_role('student');

$pdo = db();
$uid = user_id();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM complaints WHERE student_id = ?');
$stmt->execute([$uid]);
$myComplaints = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status IN ('submitted','under_review','in_progress')");
$stmt->execute([$uid]);
$activeComplaints = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status = 'resolved'");
$stmt->execute([$uid]);
$resolvedComplaints = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM event_registrations WHERE student_id = ?');
$stmt->execute([$uid]);
$myEvents = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT student_id FROM users WHERE id = ?');
$stmt->execute([$uid]);
$studentRegNo = (string) $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT fg.* FROM fyp_groups fg LEFT JOIN fyp_members fm ON fm.group_id = fg.id WHERE fg.leader_id = ? OR fm.student_reg_no = ? ORDER BY fg.id DESC LIMIT 1');
$stmt->execute([$uid, $studentRegNo]);
$fyp = $stmt->fetch();
$announcements = $pdo->query('SELECT * FROM announcements ORDER BY FIELD(priority,"urgent","important","normal"), created_at DESC LIMIT 4')->fetchAll();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM event_registrations er JOIN events e ON e.id = er.event_id WHERE er.student_id = ? AND e.status = 'active' AND e.event_date >= CURDATE()");
$stmt->execute([$uid]);
$upcomingRegisteredEvents = (int) $stmt->fetchColumn();
$events = $pdo->query("SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) registered FROM events e WHERE e.status = 'active' AND e.event_date >= CURDATE() ORDER BY e.event_date, e.event_time LIMIT 4")->fetchAll();
$notifications = fetch_notifications($uid, 4);

render_header('Student Dashboard', 'dashboard');
?>
<section class="mb-xl grid grid-cols-1 lg:grid-cols-3 gap-lg items-end">
  <div class="lg:col-span-2">
    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Welcome back, <span class="gradient-text"><?= h(user_name()) ?></span></h2>
    <p class="font-body-md text-body-md text-on-surface-variant mb-lg">Your complaints, event registrations, FYP status, announcements, and notifications are synced live.</p>
    <div class="flex flex-wrap gap-md">
      <div class="glass-panel rounded-xl px-lg py-md flex items-center gap-md border-l-4 border-secondary-container"><span class="material-symbols-outlined text-secondary">assignment</span><div><p class="font-label-md text-label-md text-on-surface-variant uppercase">My Complaints</p><p class="font-bold text-xl"><?= $myComplaints ?></p></div></div>
      <div class="glass-panel rounded-xl px-lg py-md flex items-center gap-md border-l-4 border-tertiary"><span class="material-symbols-outlined text-tertiary">event_available</span><div><p class="font-label-md text-label-md text-on-surface-variant uppercase">Registered Events</p><p class="font-bold text-xl"><?= $myEvents ?> / <?= $upcomingRegisteredEvents ?> upcoming</p></div></div>
      <div class="glass-panel rounded-xl px-lg py-md flex items-center gap-md border-l-4 border-primary"><span class="material-symbols-outlined text-primary">science</span><div><p class="font-label-md text-label-md text-on-surface-variant uppercase">FYP Status</p><p class="font-bold text-xl"><?= h($fyp['status'] ?? 'Not Started') ?></p></div></div>
    </div>
  </div>
</section>

<section class="grid lg:grid-cols-2 gap-lg">
  <div class="glass-card rounded-xl p-lg">
    <div class="flex justify-between items-center mb-md"><h3 class="font-headline-md text-headline-md">Latest Announcements</h3><a class="text-secondary text-label-md" href="<?= h(base_url('student/announcements.php')) ?>">View all</a></div>
    <div class="space-y-sm">
      <?php foreach ($announcements as $a): ?>
        <div class="rounded-lg bg-surface-container-low border border-white/5 p-md">
          <div class="flex justify-between gap-md"><p class="font-bold"><?= h($a['title']) ?></p><span class="rounded-full px-sm py-xs text-xs <?= $a['priority'] === 'urgent' ? 'bg-error/20 text-error' : ($a['priority'] === 'important' ? 'bg-tertiary/20 text-tertiary' : 'bg-secondary-container/20 text-secondary') ?>"><?= h(strtoupper($a['priority'])) ?></span></div>
          <p class="text-sm text-on-surface-variant mt-xs"><?= h($a['message']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="glass-card rounded-xl p-lg">
    <div class="flex justify-between items-center mb-md"><h3 class="font-headline-md text-headline-md">Upcoming Events</h3><a class="text-secondary text-label-md" href="<?= h(base_url('student/events.php')) ?>">Register</a></div>
    <div class="space-y-sm">
      <?php foreach ($events as $event): $remaining = max(0, (int) $event['total_seats'] - (int) $event['registered']); ?>
        <div class="rounded-lg bg-surface-container-low border border-white/5 p-md flex justify-between gap-md">
          <div><p class="font-bold"><?= h($event['title']) ?></p><p class="text-sm text-on-surface-variant"><?= h(date('M d, Y', strtotime($event['event_date']))) ?> at <?= h(date('h:i A', strtotime($event['event_time']))) ?> · <?= h($event['venue']) ?></p></div>
          <span class="text-secondary font-bold"><?= $remaining ?> seats</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="grid lg:grid-cols-3 gap-lg">
  <div class="glass-card rounded-xl p-lg"><h3 class="font-headline-md text-headline-md mb-md">Complaint Status</h3><p class="text-on-surface-variant">Active: <?= $activeComplaints ?></p><p class="text-on-surface-variant">Resolved: <?= $resolvedComplaints ?></p></div>
  <div class="glass-card rounded-xl p-lg lg:col-span-2"><h3 class="font-headline-md text-headline-md mb-md">My Notifications</h3><div class="space-y-sm"><?php foreach ($notifications as $notification): ?><div class="rounded-lg bg-surface-container-low border border-white/5 p-md text-sm text-on-surface-variant"><?= h($notification['message']) ?></div><?php endforeach; ?><?php if (!$notifications): ?><p class="text-on-surface-variant">No notifications yet.</p><?php endif; ?></div></div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
