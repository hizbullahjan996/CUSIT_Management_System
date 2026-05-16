<?php
require_once __DIR__ . '/../includes/header.php';
require_role('student');

$pdo = db();
$uid = user_id();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $eventId = (int) ($_POST['event_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) registered FROM events e WHERE id = ?');
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    if (!$event) {
        flash('error', 'Event not found.');
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM event_registrations WHERE event_id = ? AND student_id = ?');
        $stmt->execute([$eventId, $uid]);
        if ((int) $stmt->fetchColumn() > 0) {
            flash('error', 'You are already registered for this event.');
        } elseif ($event['status'] !== 'active') {
            flash('error', 'This event is not open for registration.');
        } elseif ((int) $event['registered'] >= (int) $event['total_seats']) {
            flash('error', 'This event is full.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO event_registrations (event_id, student_id) VALUES (?, ?)');
            $stmt->execute([$eventId, $uid]);
            add_notification($uid, 'You are registered for ' . $event['title'] . '.');
            flash('success', 'Event registration completed.');
        }
    }
    redirect_to('student/events.php');
}

$events = $pdo->query("SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) AS registered FROM events e WHERE e.status = 'active' AND e.event_date >= CURDATE() ORDER BY e.event_date, e.event_time")->fetchAll();
$stmt = $pdo->prepare('SELECT e.*, er.registered_at FROM event_registrations er JOIN events e ON e.id = er.event_id WHERE er.student_id = ? ORDER BY er.registered_at DESC');
$stmt->execute([$uid]);
$history = $stmt->fetchAll();
$registeredIds = array_map(static fn($e) => (int) $e['id'], $history);
render_header('Event Registration', 'events');
?>
<section>
  <h1 class="font-display-lg text-display-lg gradient-text mb-sm">CUSIT Skillathon 2026</h1>
  <p class="text-on-surface-variant max-w-2xl">Discover campus events, register once, and see live remaining seats.</p>
</section>
<section class="grid md:grid-cols-2 xl:grid-cols-3 gap-lg">
  <?php foreach ($events as $event): $remaining = max(0, (int) $event['total_seats'] - (int) $event['registered']); $isRegistered = in_array((int) $event['id'], $registeredIds, true); ?>
    <article class="glass-card rounded-xl p-lg flex flex-col gap-md">
      <div class="flex justify-between gap-md"><h2 class="font-headline-md text-headline-md"><?= h($event['title']) ?></h2><span class="rounded-full px-sm py-xs text-xs bg-secondary-container/20 text-secondary"><?= $remaining ?> seats</span></div>
      <p class="text-on-surface-variant flex-1"><?= h($event['description']) ?></p>
      <div class="text-sm text-on-surface-variant"><p><?= h(date('M d, Y', strtotime($event['event_date']))) ?> · <?= h(date('h:i A', strtotime($event['event_time']))) ?></p><p><?= h($event['venue']) ?></p></div>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>"><button class="w-full py-sm rounded-lg <?= $isRegistered || $remaining <= 0 ? 'bg-surface-container-high text-on-surface-variant' : 'bg-gradient-to-r from-secondary-container to-tertiary-container text-white' ?> font-bold" <?= $isRegistered || $remaining <= 0 ? 'disabled' : '' ?>><?= $isRegistered ? 'Registered' : ($remaining <= 0 ? 'Full' : 'Register') ?></button></form>
    </article>
  <?php endforeach; ?>
</section>
<section class="glass-card rounded-xl p-lg">
  <h2 class="font-headline-md text-headline-md mb-md">Registration History</h2>
  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-md">
    <?php foreach ($history as $row): ?><div class="rounded-lg bg-surface-container-low p-md border border-white/5"><p class="font-bold"><?= h($row['title']) ?></p><p class="text-sm text-on-surface-variant"><?= h($row['venue']) ?> · <?= h(date('M d, Y', strtotime($row['event_date']))) ?></p><p class="text-xs text-secondary mt-sm">Registered <?= h(date('M d, Y', strtotime($row['registered_at']))) ?></p></div><?php endforeach; ?>
    <?php if (!$history): ?><p class="text-on-surface-variant">You have not registered for events yet.</p><?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
