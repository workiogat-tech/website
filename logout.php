<?php
/**
 * Logout Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = getAuth();
$auth->logout();

header('Location: /');
exit();
