<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_login();

$pdo = db();
$currentRole = (string) (current_user()['role'] ?? 'student');
$profileId = (int) ($_GET['id'] ?? 0);

if ($profileId <= 0) {
    flash('error', 'Requested profile could not be found.');
    redirect_to('search.php');
}

if ($currentRole === 'student' && $profileId !== user_id()) {
    redirect_to(dashboard_for_role($currentRole));
}

$stmt = $pdo->prepare('SELECT id, name, email, role, student_id, department, semester, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$profileId]);
$profile = $stmt->fetch();

if (!$profile) {
    flash('error', 'Requested profile could not be found.');
    redirect_to('search.php');
}

$complaintCount = 0;
$registrationCount = 0;
$fypLeadCount = 0;

if ((string) $profile['role'] === 'student') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM complaints WHERE student_id = ?');
    $stmt->execute([$profileId]);
    $complaintCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM event_registrations WHERE student_id = ?');
    $stmt->execute([$profileId]);
    $registrationCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM fyp_groups WHERE leader_id = ?');
    $stmt->execute([$profileId]);
    $fypLeadCount = (int) $stmt->fetchColumn();
}

render_header('User Profile', '');
?>
<section class="grid lg:grid-cols-[1.1fr_0.9fr] gap-lg">
  <div class="glass-card rounded-xl p-lg">
    <div class="flex items-start justify-between gap-md mb-lg">
      <div>
        <p class="text-xs uppercase tracking-wide text-on-surface-variant/60 mb-xs">Directory Profile</p>
        <h1 class="font-headline-lg text-headline-lg text-on-surface"><?= h((string) $profile['name']) ?></h1>
        <p class="text-on-surface-variant mt-xs"><?= h((string) $profile['email']) ?></p>
      </div>
      <span class="rounded-full px-sm py-xs text-xs bg-secondary-container/20 text-secondary"><?= h(strtoupper((string) $profile['role'])) ?></span>
    </div>

    <div class="grid sm:grid-cols-2 gap-md">
      <div class="rounded-xl bg-surface-container-low border border-white/5 p-md">
        <p class="text-xs uppercase text-on-surface-variant/60 mb-xs">Department</p>
        <p class="font-bold text-on-surface"><?= h((string) ($profile['department'] ?: 'Not provided')) ?></p>
      </div>
      <div class="rounded-xl bg-surface-container-low border border-white/5 p-md">
        <p class="text-xs uppercase text-on-surface-variant/60 mb-xs">Student ID</p>
        <p class="font-bold text-on-surface"><?= h((string) ($profile['student_id'] ?: 'Not assigned')) ?></p>
      </div>
      <div class="rounded-xl bg-surface-container-low border border-white/5 p-md">
        <p class="text-xs uppercase text-on-surface-variant/60 mb-xs">Semester</p>
        <p class="font-bold text-on-surface"><?= h((string) ($profile['semester'] ?: 'Not assigned')) ?></p>
      </div>
      <div class="rounded-xl bg-surface-container-low border border-white/5 p-md">
        <p class="text-xs uppercase text-on-surface-variant/60 mb-xs">Joined Portal</p>
        <p class="font-bold text-on-surface"><?= h(date('M d, Y', strtotime((string) $profile['created_at']))) ?></p>
      </div>
    </div>
  </div>

  <div class="space-y-lg">
    <div class="glass-card rounded-xl p-lg">
      <h2 class="font-headline-md text-headline-md text-on-surface mb-md">Activity Snapshot</h2>
      <div class="space-y-sm">
        <div class="rounded-xl bg-surface-container-low border border-white/5 p-md flex items-center justify-between">
          <span class="text-on-surface-variant">Complaints</span>
          <span class="font-bold text-on-surface"><?= h((string) $complaintCount) ?></span>
        </div>
        <div class="rounded-xl bg-surface-container-low border border-white/5 p-md flex items-center justify-between">
          <span class="text-on-surface-variant">Event Registrations</span>
          <span class="font-bold text-on-surface"><?= h((string) $registrationCount) ?></span>
        </div>
        <div class="rounded-xl bg-surface-container-low border border-white/5 p-md flex items-center justify-between">
          <span class="text-on-surface-variant">FYP Groups Led</span>
          <span class="font-bold text-on-surface"><?= h((string) $fypLeadCount) ?></span>
        </div>
      </div>
    </div>

    <div class="glass-card rounded-xl p-lg">
      <h2 class="font-headline-md text-headline-md text-on-surface mb-md">Quick Actions</h2>
      <div class="space-y-sm">
        <a class="flex items-center justify-between rounded-xl bg-surface-container-low border border-white/5 p-md hover:bg-white/5 transition-all" href="<?= h(base_url('search.php?q=' . urlencode((string) $profile['name']))) ?>">
          <span class="text-on-surface-variant">Back to search results</span>
          <span class="material-symbols-outlined text-secondary">arrow_forward</span>
        </a>
        <a class="flex items-center justify-between rounded-xl bg-surface-container-low border border-white/5 p-md hover:bg-white/5 transition-all" href="<?= h(base_url('security.php')) ?>">
          <span class="text-on-surface-variant">Open security settings</span>
          <span class="material-symbols-outlined text-secondary">shield</span>
        </a>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
