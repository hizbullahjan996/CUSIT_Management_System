<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_login();

$pdo = db();
$user = current_user() ?? [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $error = 'Please complete all password fields.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirmation do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([user_id()]);
        $currentHash = (string) $stmt->fetchColumn();

        if ($currentHash === '' || !password_verify($currentPassword, $currentHash)) {
            $error = 'Your current password is incorrect.';
        } elseif (password_verify($newPassword, $currentHash)) {
            $error = 'Please choose a different password from your current one.';
        } else {
            $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), user_id()]);
            add_notification(user_id(), 'Your portal password was updated successfully.');
            flash('success', 'Security settings updated. Your password has been changed.');
            redirect_to('security.php');
        }
    }
}

$roleLabel = ucfirst((string) ($user['role'] ?? 'user'));
$securityTips = [
    'Use a unique password for your campus portal account.',
    'Avoid sharing your login with classmates or colleagues.',
    'Log out on shared lab systems after each session.',
];

render_header('Security', 'security');
?>
<section class="grid lg:grid-cols-[1.05fr_0.95fr] gap-lg">
  <div class="glass-card rounded-xl p-lg">
    <div class="flex items-start justify-between gap-md mb-lg">
      <div>
        <h2 class="font-headline-lg text-headline-lg text-on-surface">Account Security</h2>
        <p class="text-on-surface-variant mt-xs">Manage your login credentials and review the identity details connected to your portal account.</p>
      </div>
      <div class="w-12 h-12 rounded-xl bg-secondary-container/20 flex items-center justify-center text-secondary">
        <span class="material-symbols-outlined">shield_lock</span>
      </div>
    </div>

    <?php if ($error !== ''): ?>
      <div class="rounded-lg border border-error/30 bg-error/10 text-error px-md py-sm mb-lg"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="grid sm:grid-cols-2 gap-md mb-lg">
      <div class="rounded-xl bg-surface-container-low border border-white/5 p-md">
        <p class="text-xs uppercase text-on-surface-variant/60 mb-xs">Portal Role</p>
        <p class="font-bold text-on-surface"><?= h($roleLabel) ?></p>
      </div>
      <div class="rounded-xl bg-surface-container-low border border-white/5 p-md">
        <p class="text-xs uppercase text-on-surface-variant/60 mb-xs">Email</p>
        <p class="font-bold text-on-surface break-all"><?= h((string) ($user['email'] ?? '')) ?></p>
      </div>
      <div class="rounded-xl bg-surface-container-low border border-white/5 p-md">
        <p class="text-xs uppercase text-on-surface-variant/60 mb-xs">Department</p>
        <p class="font-bold text-on-surface"><?= h((string) ($user['department'] ?? 'Not provided')) ?></p>
      </div>
      <div class="rounded-xl bg-surface-container-low border border-white/5 p-md">
        <p class="text-xs uppercase text-on-surface-variant/60 mb-xs">Student ID / Record</p>
        <p class="font-bold text-on-surface"><?= h((string) ($user['student_id'] ?? 'Not assigned')) ?></p>
      </div>
    </div>

    <form method="post" action="<?= h(base_url('security.php')) ?>" class="space-y-md">
      <?= csrf_field() ?>
      <div>
        <label class="block font-label-md text-label-md text-on-surface-variant mb-xs" for="current_password">Current Password</label>
        <input class="form-input px-md py-md" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
      </div>
      <div class="grid sm:grid-cols-2 gap-md">
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-xs" for="new_password">New Password</label>
          <input class="form-input px-md py-md" id="new_password" name="new_password" type="password" autocomplete="new-password" required>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-xs" for="confirm_password">Confirm Password</label>
          <input class="form-input px-md py-md" id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required>
        </div>
      </div>
      <button class="inline-flex items-center gap-sm px-lg py-md rounded-lg bg-gradient-to-r from-secondary-container to-secondary-fixed-dim text-on-secondary font-bold shadow-lg neon-glow-primary" type="submit">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">lock_reset</span>
        <span>Update Password</span>
      </button>
    </form>
  </div>

  <div class="space-y-lg">
    <div class="glass-card rounded-xl p-lg">
      <h3 class="font-headline-md text-headline-md text-on-surface mb-md">Security Guidance</h3>
      <div class="space-y-sm">
        <?php foreach ($securityTips as $tip): ?>
          <div class="rounded-lg bg-surface-container-low border border-white/5 p-md flex gap-sm items-start">
            <span class="material-symbols-outlined text-secondary mt-[2px]">verified_user</span>
            <p class="text-on-surface-variant"><?= h($tip) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="glass-card rounded-xl p-lg">
      <h3 class="font-headline-md text-headline-md text-on-surface mb-md">Session Protection</h3>
      <div class="space-y-sm text-on-surface-variant">
        <p>Your portal uses server-side sessions, CSRF tokens, and password hashing for account protection.</p>
        <p>Use the sidebar logout button whenever you finish work on a shared machine or lab system.</p>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
