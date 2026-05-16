<?php
require_once __DIR__ . '/../includes/header.php';
require_role('student');

$announcements = db()->query('SELECT * FROM announcements ORDER BY FIELD(priority,"urgent","important","normal"), created_at DESC')->fetchAll();
render_header('Announcements', 'announcements');
?>
<section>
  <h1 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Smart Announcement Center</h1>
  <p class="text-on-surface-variant">Campus updates are ordered by priority so urgent notices stay visible.</p>
</section>
<section class="grid md:grid-cols-2 xl:grid-cols-3 gap-lg">
  <?php foreach ($announcements as $a): ?>
    <article class="glass-card rounded-xl p-lg">
      <div class="flex justify-between gap-md items-start">
        <h2 class="font-headline-md text-headline-md"><?= h($a['title']) ?></h2>
        <span class="rounded-full px-sm py-xs text-xs <?= $a['priority'] === 'urgent' ? 'bg-error/20 text-error' : ($a['priority'] === 'important' ? 'bg-tertiary/20 text-tertiary' : 'bg-secondary-container/20 text-secondary') ?>"><?= h(strtoupper($a['priority'])) ?></span>
      </div>
      <p class="text-on-surface-variant mt-md"><?= h($a['message']) ?></p>
      <p class="text-xs text-on-surface-variant mt-lg"><?= h(date('M d, Y h:i A', strtotime($a['created_at']))) ?></p>
    </article>
  <?php endforeach; ?>
  <?php if (!$announcements): ?><div class="glass-card rounded-xl p-lg text-on-surface-variant">No announcements published yet.</div><?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
