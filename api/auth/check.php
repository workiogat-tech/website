<?php
/**
 * Check Authentication Status
 */
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$auth = getAuth();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();

echo json_encode([
    'success' => true,
    'isLoggedIn' => $isLoggedIn,
    'isAdmin' => $auth->isAdmin(),
    'user' => $currentUser
]);
