<?php
/**
 * Verify Token/Session Endpoint
 */
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$auth = getAuth();

if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    echo json_encode([
        'valid' => true,
        'user' => $user
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
}
