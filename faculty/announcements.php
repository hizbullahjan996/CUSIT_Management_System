<?php
require_once __DIR__ . '/../includes/header.php';
require_role('faculty');

$pdo = db();
$announcements = $pdo->query('SELECT a.*, u.name AS author_name FROM announcements a JOIN users u ON u.id = a.created_by ORDER BY FIELD(priority,"urgent","important","normal"), created_at DESC')->fetchAll();

render_header('Announcements', 'announcements');
?>
<section class="glass-card rounded-xl p-lg">
  <div class="flex items-center justify-between mb-lg">
    <div>
      <h2 class="font-headline-lg text-headline-lg">Campus Announcements</h2>
      <p class="text-on-surface-variant">Faculty can follow important, urgent, and general portal updates here.</p>
    </div>
  </div>

  <div class="space-y-md">
    <?php foreach ($announcements as $announcement): ?>
      <article class="rounded-xl bg-surface-container-low border border-white/5 p-lg">
        <div class="flex flex-wrap items-center justify-between gap-md mb-sm">
          <h3 class="font-headline-md text-headline-md"><?= h($announcement['title']) ?></h3>
          <span class="rounded-full px-sm py-xs text-xs <?= $announcement['priority'] === 'urgent' ? 'bg-error/20 text-error' : ($announcement['priority'] === 'important' ? 'bg-tertiary/20 text-tertiary' : 'bg-secondary-container/20 text-secondary') ?>"><?= h(strtoupper($announcement['priority'])) ?></span>
        </div>
        <p class="text-on-surface-variant mb-sm"><?= nl2br(h($announcement['message'])) ?></p>
        <p class="text-sm text-on-surface-variant/70">Published by <?= h($announcement['author_name']) ?> on <?= h(date('M d, Y', strtotime($announcement['created_at']))) ?></p>
      </article>
    <?php endforeach; ?>
    <?php if (!$announcements): ?><p class="text-on-surface-variant">No announcements available yet.</p><?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
