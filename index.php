<?php
require_once __DIR__ . '/includes/auth.php';

if (!is_logged_in()) {
    redirect_to('login.php');
}

redirect_to(dashboard_for_role($_SESSION['user']['role']));
