<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/ParticipantName-WebDev/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect_to('login.php');
    }
}

function require_role(string $role): void
{
    require_login();
    if (($_SESSION['user']['role'] ?? '') !== $role) {
        redirect_to(dashboard_for_role((string) ($_SESSION['user']['role'] ?? 'student')));
    }
}
