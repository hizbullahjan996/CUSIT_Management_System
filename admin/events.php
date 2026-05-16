<?php
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$pdo = db();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'save');
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Event deleted.');
    } else {
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $venue = trim((string) ($_POST['venue'] ?? ''));
        $eventDate = (string) ($_POST['event_date'] ?? '');
        $eventTime = (string) ($_POST['event_time'] ?? '');
        $seats = max(1, (int) ($_POST['total_seats'] ?? 0));
        $status = (string) ($_POST['status'] ?? 'active');
        if ($title === '' || $description === '' || $venue === '' || $eventDate === '' || $eventTime === '' || !in_array($status, ['active', 'closed', 'cancelled'], true)) {
            flash('error', 'Please complete all event fields.');
        } elseif ($id > 0) {
            $stmt = $pdo->prepare('UPDATE events SET title = ?, description = ?, venue = ?, event_date = ?, event_time = ?, total_seats = ?, status = ? WHERE id = ?');
            $stmt->execute([$title, $description, $venue, $eventDate, $eventTime, $seats, $status, $id]);
            flash('success', 'Event updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO events (title, description, venue, event_date, event_time, total_seats, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $description, $venue, $eventDate, $eventTime, $seats, $status]);
            flash('success', 'Event created.');
        }
    }
    redirect_to('admin/events.php');
}

$events = $pdo->query('SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) AS registered FROM events e ORDER BY e.event_date DESC, e.event_time DESC')->fetchAll();
$registrations = [];
if (isset($_GET['registrations'])) {
    $stmt = $pdo->prepare('SELECT u.name, u.email, u.student_id AS reg_no, er.registered_at FROM event_registrations er JOIN users u ON u.id = er.student_id WHERE er.event_id = ? ORDER BY er.registered_at DESC');
    $stmt->execute([(int) $_GET['registrations']]);
    $registrations = $stmt->fetchAll();
}
render_header('Events', 'events');
?>
<section class="grid lg:grid-cols-3 gap-lg">
  <form class="glass-card rounded-xl p-lg space-y-md" method="post">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($edit['id'] ?? 0) ?>">
    <div><h1 class="font-headline-lg text-headline-lg"><?= $edit ? 'Edit Event' : 'Create Event' ?></h1><p class="text-on-surface-variant">Seat availability updates from registrations.</p></div>
    <input class="form-input p-md" name="title" placeholder="Event title" value="<?= h($edit['title'] ?? '') ?>" required>
    <textarea class="form-input p-md min-h-28" name="description" placeholder="Description" required><?= h($edit['description'] ?? '') ?></textarea>
    <input class="form-input p-md" name="venue" placeholder="Venue" value="<?= h($edit['venue'] ?? '') ?>" required>
    <input class="form-input p-md" name="event_date" type="date" value="<?= h($edit['event_date'] ?? '') ?>" required>
    <input class="form-input p-md" name="event_time" type="time" value="<?= h($edit['event_time'] ?? '') ?>" required>
    <input class="form-input p-md" name="total_seats" type="number" min="1" placeholder="Total seats" value="<?= h($edit['total_seats'] ?? '') ?>" required>
    <select class="form-input p-md" name="status"><?php foreach (['active','closed','cancelled'] as $state): ?><option value="<?= h($state) ?>" <?= ($edit['status'] ?? 'active') === $state ? 'selected' : '' ?>><?= h(ucfirst($state)) ?></option><?php endforeach; ?></select>
    <button class="w-full py-md rounded-lg bg-gradient-to-r from-secondary-container to-tertiary-container text-white font-bold"><?= $edit ? 'Update Event' : 'Create Event' ?></button>
  </form>
  <div class="lg:col-span-2 glass-card rounded-xl overflow-hidden">
    <div class="px-lg py-md border-b border-white/10 bg-white/5"><h2 class="font-headline-md text-headline-md">Event Registry</h2></div>
    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-surface-container-high text-on-surface-variant"><tr><th class="p-md">Event</th><th class="p-md">Schedule</th><th class="p-md">Seats</th><th class="p-md">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($events as $event): $remaining = max(0, (int) $event['total_seats'] - (int) $event['registered']); ?>
          <tr class="border-t border-white/5">
            <td class="p-md"><p class="font-bold"><?= h($event['title']) ?></p><p class="text-sm text-on-surface-variant"><?= h($event['description']) ?></p></td>
            <td class="p-md"><p><?= h(date('M d, Y', strtotime($event['event_date']))) ?> <?= h(date('h:i A', strtotime($event['event_time']))) ?></p><p class="text-sm text-on-surface-variant"><?= h($event['venue']) ?> · <?= h(ucfirst($event['status'])) ?></p></td>
            <td class="p-md"><p class="font-bold text-secondary"><?= $remaining ?> remaining</p><p class="text-sm text-on-surface-variant"><?= (int) $event['registered'] ?> / <?= (int) $event['total_seats'] ?> registered</p></td>
            <td class="p-md">
              <div class="flex flex-wrap gap-sm">
                <a class="px-sm py-xs rounded-lg bg-secondary-container/20 text-secondary" href="<?= h(base_url('admin/events.php?edit=' . $event['id'])) ?>">Edit</a>
                <a class="px-sm py-xs rounded-lg bg-tertiary/20 text-tertiary" href="<?= h(base_url('admin/events.php?registrations=' . $event['id'])) ?>">Students</a>
                <form method="post" onsubmit="return confirm('Delete this event?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $event['id'] ?>"><button class="px-sm py-xs rounded-lg bg-error/20 text-error" name="action" value="delete">Delete</button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php if (isset($_GET['registrations'])): ?>
<section class="glass-card rounded-xl p-lg">
  <h2 class="font-headline-md text-headline-md mb-md">Registered Students</h2>
  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-md">
    <?php foreach ($registrations as $r): ?><div class="rounded-lg bg-surface-container-low p-md border border-white/5"><p class="font-bold"><?= h($r['name']) ?></p><p class="text-sm text-on-surface-variant"><?= h($r['reg_no']) ?> · <?= h($r['email']) ?></p><p class="text-xs text-secondary mt-sm"><?= h(date('M d, Y h:i A', strtotime($r['registered_at']))) ?></p></div><?php endforeach; ?>
    <?php if (!$registrations): ?><p class="text-on-surface-variant">No students registered yet.</p><?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
