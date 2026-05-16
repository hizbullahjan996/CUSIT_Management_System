<?php
declare(strict_types=1);

function base_url(string $path = ''): string
{
    return '/ParticipantName-WebDev/' . ltrim($path, '/');
}

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $posted = $_POST['csrf_token'] ?? '';
    if (!is_string($posted) || !hash_equals($_SESSION['csrf_token'] ?? '', $posted)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function flash(?string $type = null, ?string $message = null): ?array
{
    if ($type !== null && $message !== null) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        return null;
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']['id']);
}

function user_id(): int
{
    return (int) ($_SESSION['user']['id'] ?? 0);
}

function user_name(): string
{
    return $_SESSION['user']['name'] ?? 'CUSIT User';
}

function dashboard_for_role(string $role): string
{
    return match ($role) {
        'admin' => 'admin/dashboard.php',
        'faculty' => 'faculty/dashboard.php',
        default => 'student/dashboard.php',
    };
}

function add_notification(int $userId, string $message): void
{
    $stmt = db()->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
    $stmt->execute([$userId, $message]);
}

function fetch_notifications(int $userId, int $limit = 5): array
{
    $stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int) $limit);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function event_datetime(array $event): string
{
    return trim(($event['event_date'] ?? '') . ' ' . ($event['event_time'] ?? ''));
}

function template_path(string $relativePath): string
{
    return dirname(__DIR__) . '/' . ltrim($relativePath, '/');
}
