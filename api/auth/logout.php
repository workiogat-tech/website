<?php
/**
 * Logout Endpoint
 */
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$auth = getAuth();
$result = $auth->logout();

echo json_encode(['message' => 'Logout successful']);
