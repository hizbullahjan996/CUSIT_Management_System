<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$pdo = db();
$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
$activeComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('submitted','under_review','in_progress')")->fetchColumn();
$pendingComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('submitted','under_review')")->fetchColumn();
$resolvedComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'")->fetchColumn();
$totalEvents = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$upcomingEvents = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'active' AND event_date >= CURDATE()")->fetchColumn();
$registeredStudents = (int) $pdo->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn();
$totalFyp = (int) $pdo->query('SELECT COUNT(*) FROM fyp_groups')->fetchColumn();
$pendingFyp = (int) $pdo->query("SELECT COUNT(*) FROM fyp_groups WHERE status = 'pending'")->fetchColumn();
$totalAnnouncements = (int) $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
$urgent = $pdo->query("SELECT c.*, u.name FROM complaints c JOIN users u ON u.id = c.student_id WHERE c.priority IN ('high','urgent') AND c.status <> 'resolved' ORDER BY FIELD(c.priority,'urgent','high'), c.created_at DESC LIMIT 5")->fetchAll();
$events = $pdo->query("SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) AS registered FROM events e WHERE e.status = 'active' AND e.event_date >= CURDATE() ORDER BY e.event_date, e.event_time LIMIT 4")->fetchAll();
$latestRegistrations = $pdo->query("SELECT u.name, e.title, er.registered_at FROM event_registrations er JOIN users u ON u.id = er.student_id JOIN events e ON e.id = er.event_id ORDER BY er.registered_at DESC LIMIT 4")->fetchAll();
$recentFyp = $pdo->query("SELECT fg.project_title, fg.status, u.name FROM fyp_groups fg JOIN users u ON u.id = fg.leader_id ORDER BY fg.created_at DESC LIMIT 4")->fetchAll();
$latestAnnouncements = $pdo->query("SELECT title, priority, created_at FROM announcements ORDER BY created_at DESC LIMIT 4")->fetchAll();

render_header('Admin Dashboard', 'dashboard');
?>
<section class="grid grid-cols-12 gap-gutter">
  <div class="col-span-12 lg:col-span-8 glass-panel rounded-xl p-lg overflow-hidden relative">
    <h2 class="font-headline-lg text-headline-lg mb-xs">Good Morning, <?= h(user_name()) ?></h2>
    <p class="text-on-surface-variant/80 font-body-md max-w-2xl">Your campus command center is connected to live complaints, events, FYP approvals, and announcements.</p>
    <div class="grid sm:grid-cols-3 gap-md mt-lg">
      <div class="px-md py-sm bg-surface-container rounded-lg border border-white/5"><p class="text-xs text-on-surface-variant/60 uppercase">Students</p><p class="text-2xl font-bold"><?= $totalStudents ?></p></div>
      <div class="px-md py-sm bg-surface-container rounded-lg border border-white/5"><p class="text-xs text-on-surface-variant/60 uppercase">Events</p><p class="text-2xl font-bold"><?= $totalEvents ?></p></div>
      <div class="px-md py-sm bg-surface-container rounded-lg border border-white/5"><p class="text-xs text-on-surface-variant/60 uppercase">Announcements</p><p class="text-2xl font-bold"><?= $totalAnnouncements ?></p></div>
    </div>
  </div>
  <div class="col-span-12 lg:col-span-4 glass-panel rounded-xl p-lg">
    <p class="text-on-surface-variant font-label-md text-label-md mb-xs">Pending FYP Approvals</p>
    <h3 class="font-headline-lg text-headline-lg text-tertiary"><?= $pendingFyp ?></h3>
    <a class="inline-flex mt-md text-secondary font-label-md" href="<?= h(base_url('admin/fyp.php')) ?>">Review groups</a>
  </div>
</section>

<section class="grid md:grid-cols-2 xl:grid-cols-4 gap-lg">
  <?php
  $cards = [
      ['Total Students', $totalStudents, 'groups', 'border-secondary-container'],
      ['Total Complaints', $totalComplaints, 'assignment', 'border-secondary-container'],
      ['Active Complaints', $activeComplaints, 'assignment_late', 'border-error'],
      ['Pending Complaints', $pendingComplaints, 'pending', 'border-tertiary'],
      ['Resolved Complaints', $resolvedComplaints, 'check_circle', 'border-primary'],
      ['Total Events', $totalEvents, 'event', 'border-secondary-container'],
      ['Upcoming Events', $upcomingEvents, 'event_available', 'border-tertiary'],
      ['Registered Students', $registeredStudents, 'how_to_reg', 'border-primary'],
      ['Total FYP Groups', $totalFyp, 'science', 'border-secondary-container'],
      ['Pending FYP', $pendingFyp, 'schedule', 'border-tertiary'],
      ['Announcements', $totalAnnouncements, 'campaign', 'border-primary'],
  ];
  foreach ($cards as [$label, $value, $icon, $border]): ?>
    <div class="glass-card rounded-xl p-md border-l-4 <?= h($border) ?>">
      <div class="flex justify-between items-start"><p class="text-on-surface-variant font-label-md"><?= h($label) ?></p><span class="material-symbols-outlined text-secondary"><?= h($icon) ?></span></div>
      <h4 class="text-3xl font-bold font-headline-md text-on-surface mt-md"><?= h($value) ?></h4>
    </div>
  <?php endforeach; ?>
</section>

<section class="grid lg:grid-cols-2 gap-lg">
  <div class="glass-card rounded-xl p-lg">
    <div class="flex justify-between items-center mb-md"><h3 class="font-headline-md text-headline-md">Urgent Complaints</h3><a class="text-secondary text-label-md" href="<?= h(base_url('admin/complaints.php')) ?>">Open module</a></div>
    <div class="space-y-sm">
      <?php foreach ($urgent as $row): ?>
        <div class="rounded-lg bg-surface-container-low border border-white/5 p-md flex justify-between gap-md">
          <div><p class="font-bold"><?= h($row['category']) ?> <span class="text-on-surface-variant font-normal">by <?= h($row['name']) ?></span></p><p class="text-sm text-on-surface-variant"><?= h($row['description']) ?></p></div>
          <span class="h-fit rounded-full px-sm py-xs text-xs <?= $row['priority'] === 'urgent' ? 'bg-error/20 text-error' : 'bg-tertiary/20 text-tertiary' ?>"><?= h(strtoupper($row['priority'])) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$urgent): ?><p class="text-on-surface-variant">No urgent complaints right now.</p><?php endif; ?>
    </div>
  </div>
  <div class="glass-card rounded-xl p-lg">
    <div class="flex justify-between items-center mb-md"><h3 class="font-headline-md text-headline-md">Upcoming Events</h3><a class="text-secondary text-label-md" href="<?= h(base_url('admin/events.php')) ?>">Manage</a></div>
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
  <div class="glass-card rounded-xl p-lg"><h3 class="font-headline-md text-headline-md mb-md">Latest Registrations</h3><div class="space-y-sm"><?php foreach ($latestRegistrations as $row): ?><div class="rounded-lg bg-surface-container-low border border-white/5 p-md"><p class="font-bold"><?= h($row['name']) ?></p><p class="text-sm text-on-surface-variant"><?= h($row['title']) ?> · <?= h(date('M d, Y', strtotime($row['registered_at']))) ?></p></div><?php endforeach; ?></div></div>
  <div class="glass-card rounded-xl p-lg"><h3 class="font-headline-md text-headline-md mb-md">Recent FYP Submissions</h3><div class="space-y-sm"><?php foreach ($recentFyp as $row): ?><div class="rounded-lg bg-surface-container-low border border-white/5 p-md"><p class="font-bold"><?= h($row['project_title']) ?></p><p class="text-sm text-on-surface-variant"><?= h($row['name']) ?> · <?= h($row['status']) ?></p></div><?php endforeach; ?></div></div>
  <div class="glass-card rounded-xl p-lg"><h3 class="font-headline-md text-headline-md mb-md">Latest Announcements</h3><div class="space-y-sm"><?php foreach ($latestAnnouncements as $row): ?><div class="rounded-lg bg-surface-container-low border border-white/5 p-md"><p class="font-bold"><?= h($row['title']) ?></p><p class="text-sm text-on-surface-variant"><?= h($row['priority']) ?> · <?= h(date('M d, Y', strtotime($row['created_at']))) ?></p></div><?php endforeach; ?></div></div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
