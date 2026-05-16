<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_login();

$pdo = db();
$role = (string) (current_user()['role'] ?? 'student');
$query = trim((string) ($_GET['q'] ?? ''));
$results = [];

function add_search_result(array &$results, string $section, string $title, string $summary, string $url, string $meta = ''): void
{
    $results[] = [
        'section' => $section,
        'title' => $title,
        'summary' => $summary,
        'url' => $url,
        'meta' => $meta,
    ];
}

if ($query !== '' && mb_strlen($query) >= 2) {
    $like = '%' . $query . '%';

    if ($role === 'admin') {
        $stmt = $pdo->prepare("SELECT id, name, email, role, department, student_id
            FROM users
            WHERE name LIKE ? OR email LIKE ? OR COALESCE(student_id, '') LIKE ? OR COALESCE(department, '') LIKE ?
            ORDER BY name ASC
            LIMIT 8");
        $stmt->execute([$like, $like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            $meta = strtoupper((string) $row['role']);
            if (!empty($row['student_id'])) {
                $meta .= ' · ' . (string) $row['student_id'];
            }
            add_search_result(
                $results,
                'People',
                (string) $row['name'],
                (string) $row['email'] . (!empty($row['department']) ? ' · ' . (string) $row['department'] : ''),
                base_url('user_profile.php?id=' . (int) $row['id']),
                $meta
            );
        }

        $stmt = $pdo->prepare("SELECT c.title, c.description, c.category, c.status, u.name
            FROM complaints c
            JOIN users u ON u.id = c.student_id
            WHERE c.title LIKE ? OR c.description LIKE ? OR c.category LIKE ? OR u.name LIKE ?
            ORDER BY c.created_at DESC
            LIMIT 8");
        $stmt->execute([$like, $like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'Complaints',
                (string) $row['title'],
                (string) $row['description'],
                base_url('admin/complaints.php'),
                (string) $row['name'] . ' · ' . str_replace('_', ' ', (string) $row['status'])
            );
        }

        $stmt = $pdo->prepare("SELECT title, description, venue, event_date
            FROM events
            WHERE title LIKE ? OR description LIKE ? OR venue LIKE ?
            ORDER BY event_date ASC, event_time ASC
            LIMIT 8");
        $stmt->execute([$like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'Events',
                (string) $row['title'],
                (string) $row['description'],
                base_url('admin/events.php'),
                (string) $row['venue'] . ' · ' . date('M d, Y', strtotime((string) $row['event_date']))
            );
        }

        $stmt = $pdo->prepare("SELECT group_name, project_title, project_description, supervisor, status
            FROM fyp_groups
            WHERE group_name LIKE ? OR project_title LIKE ? OR project_description LIKE ? OR COALESCE(supervisor, '') LIKE ?
            ORDER BY created_at DESC
            LIMIT 8");
        $stmt->execute([$like, $like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'FYP Groups',
                (string) $row['project_title'],
                (string) $row['project_description'],
                base_url('admin/fyp.php'),
                (string) $row['group_name'] . ' · ' . str_replace('_', ' ', (string) $row['status'])
            );
        }

        $stmt = $pdo->prepare("SELECT title, message, priority, created_at
            FROM announcements
            WHERE title LIKE ? OR message LIKE ?
            ORDER BY created_at DESC
            LIMIT 8");
        $stmt->execute([$like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'Announcements',
                (string) $row['title'],
                (string) $row['message'],
                base_url('admin/announcements.php'),
                strtoupper((string) $row['priority']) . ' · ' . date('M d, Y', strtotime((string) $row['created_at']))
            );
        }
    } elseif ($role === 'faculty') {
        $stmt = $pdo->prepare("SELECT id, name, email, role, department, student_id
            FROM users
            WHERE name LIKE ? OR email LIKE ? OR COALESCE(student_id, '') LIKE ? OR COALESCE(department, '') LIKE ?
            ORDER BY name ASC
            LIMIT 8");
        $stmt->execute([$like, $like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            $meta = strtoupper((string) $row['role']);
            if (!empty($row['student_id'])) {
                $meta .= ' · ' . (string) $row['student_id'];
            }
            add_search_result(
                $results,
                'People',
                (string) $row['name'],
                (string) $row['email'] . (!empty($row['department']) ? ' · ' . (string) $row['department'] : ''),
                base_url('user_profile.php?id=' . (int) $row['id']),
                $meta
            );
        }

        $stmt = $pdo->prepare("SELECT title, message, priority, created_at
            FROM announcements
            WHERE title LIKE ? OR message LIKE ?
            ORDER BY created_at DESC
            LIMIT 8");
        $stmt->execute([$like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'Announcements',
                (string) $row['title'],
                (string) $row['message'],
                base_url('faculty/announcements.php'),
                strtoupper((string) $row['priority']) . ' · ' . date('M d, Y', strtotime((string) $row['created_at']))
            );
        }

        $stmt = $pdo->prepare("SELECT group_name, project_title, project_description, supervisor, status
            FROM fyp_groups
            WHERE group_name LIKE ? OR project_title LIKE ? OR project_description LIKE ? OR COALESCE(supervisor, '') LIKE ?
            ORDER BY created_at DESC
            LIMIT 8");
        $stmt->execute([$like, $like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'FYP Groups',
                (string) $row['project_title'],
                (string) $row['project_description'],
                base_url('faculty/dashboard.php'),
                ((string) $row['supervisor'] ?: 'Supervisor pending') . ' · ' . str_replace('_', ' ', (string) $row['status'])
            );
        }

        $stmt = $pdo->prepare("SELECT title, description, venue, event_date
            FROM events
            WHERE title LIKE ? OR description LIKE ? OR venue LIKE ?
            ORDER BY event_date ASC
            LIMIT 8");
        $stmt->execute([$like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'Events',
                (string) $row['title'],
                (string) $row['description'],
                base_url('faculty/dashboard.php'),
                (string) $row['venue'] . ' · ' . date('M d, Y', strtotime((string) $row['event_date']))
            );
        }
    } else {
        $uid = user_id();

        $stmt = $pdo->prepare("SELECT title, description, category, status
            FROM complaints
            WHERE student_id = ? AND (title LIKE ? OR description LIKE ? OR category LIKE ?)
            ORDER BY created_at DESC
            LIMIT 8");
        $stmt->execute([$uid, $like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'My Complaints',
                (string) $row['title'],
                (string) $row['description'],
                base_url('student/complaints.php'),
                (string) $row['category'] . ' · ' . str_replace('_', ' ', (string) $row['status'])
            );
        }

        $stmt = $pdo->prepare("SELECT title, description, venue, event_date
            FROM events
            WHERE title LIKE ? OR description LIKE ? OR venue LIKE ?
            ORDER BY event_date ASC, event_time ASC
            LIMIT 8");
        $stmt->execute([$like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'Events',
                (string) $row['title'],
                (string) $row['description'],
                base_url('student/events.php'),
                (string) $row['venue'] . ' · ' . date('M d, Y', strtotime((string) $row['event_date']))
            );
        }

        $stmtReg = $pdo->prepare('SELECT student_id FROM users WHERE id = ?');
        $stmtReg->execute([$uid]);
        $studentRegNo = (string) $stmtReg->fetchColumn();
        $stmt = $pdo->prepare("SELECT DISTINCT fg.group_name, fg.project_title, fg.project_description, fg.status, fg.supervisor
            FROM fyp_groups fg
            LEFT JOIN fyp_members fm ON fm.group_id = fg.id
            WHERE (fg.leader_id = ? OR fm.student_reg_no = ?)
            AND (fg.group_name LIKE ? OR fg.project_title LIKE ? OR fg.project_description LIKE ? OR COALESCE(fg.supervisor, '') LIKE ?)
            ORDER BY fg.created_at DESC
            LIMIT 8");
        $stmt->execute([$uid, $studentRegNo, $like, $like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'My FYP',
                (string) $row['project_title'],
                (string) $row['project_description'],
                base_url('student/fyp.php'),
                ((string) $row['supervisor'] ?: 'Supervisor pending') . ' · ' . str_replace('_', ' ', (string) $row['status'])
            );
        }

        $stmt = $pdo->prepare("SELECT title, message, priority, created_at
            FROM announcements
            WHERE title LIKE ? OR message LIKE ?
            ORDER BY created_at DESC
            LIMIT 8");
        $stmt->execute([$like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'Announcements',
                (string) $row['title'],
                (string) $row['message'],
                base_url('student/announcements.php'),
                strtoupper((string) $row['priority']) . ' · ' . date('M d, Y', strtotime((string) $row['created_at']))
            );
        }

        $stmt = $pdo->prepare("SELECT message, created_at
            FROM notifications
            WHERE user_id = ? AND message LIKE ?
            ORDER BY created_at DESC
            LIMIT 8");
        $stmt->execute([$uid, $like]);
        foreach ($stmt->fetchAll() as $row) {
            add_search_result(
                $results,
                'Notifications',
                (string) $row['message'],
                'Notification found in your personal alerts.',
                base_url('student/dashboard.php'),
                date('M d, Y', strtotime((string) $row['created_at']))
            );
        }

        $stmt = $pdo->prepare("SELECT id, name, email, department, student_id
            FROM users
            WHERE id = ? AND (name LIKE ? OR email LIKE ? OR COALESCE(student_id, '') LIKE ? OR COALESCE(department, '') LIKE ?)
            LIMIT 1");
        $stmt->execute([$uid, $like, $like, $like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            $meta = 'MY PROFILE';
            if (!empty($row['student_id'])) {
                $meta .= ' · ' . (string) $row['student_id'];
            }
            add_search_result(
                $results,
                'Profile',
                (string) $row['name'],
                (string) $row['email'] . (!empty($row['department']) ? ' · ' . (string) $row['department'] : ''),
                base_url('user_profile.php?id=' . (int) $row['id']),
                $meta
            );
        }
    }
}

render_header('Search Results', '');
?>
<section class="flex justify-between items-end gap-md flex-wrap">
  <div>
    <h1 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Search Results</h1>
    <p class="text-on-surface-variant">
      <?php if ($query === ''): ?>
        Use the portal search bar to find people, complaints, events, announcements, FYP records, and more.
      <?php elseif (mb_strlen($query) < 2): ?>
        Please enter at least 2 characters to search the portal.
      <?php else: ?>
        Showing matches for "<span class="text-on-surface font-semibold"><?= h($query) ?></span>".
      <?php endif; ?>
    </p>
  </div>
</section>

<?php if ($query !== '' && mb_strlen($query) >= 2): ?>
  <section class="grid gap-lg">
    <?php if ($results): ?>
      <?php foreach ($results as $result): ?>
        <article class="glass-card rounded-xl p-lg">
          <div class="flex items-start justify-between gap-md flex-wrap mb-sm">
            <div>
              <p class="text-xs uppercase tracking-wide text-on-surface-variant/60 mb-xs"><?= h($result['section']) ?></p>
              <h2 class="font-headline-md text-headline-md text-on-surface"><?= h($result['title']) ?></h2>
            </div>
            <?php if ($result['meta'] !== ''): ?>
              <span class="rounded-full px-sm py-xs text-xs bg-secondary-container/20 text-secondary"><?= h($result['meta']) ?></span>
            <?php endif; ?>
          </div>
          <p class="text-on-surface-variant mb-md"><?= h($result['summary']) ?></p>
          <a class="inline-flex items-center gap-sm text-secondary font-label-md" href="<?= h($result['url']) ?>">
            <span class="material-symbols-outlined text-sm">arrow_forward</span>
            <span>Open result</span>
          </a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="glass-card rounded-xl p-lg text-on-surface-variant">
        No portal records matched your search. Try a different keyword or broader phrase.
      </div>
    <?php endif; ?>
  </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
