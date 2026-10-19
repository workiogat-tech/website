<?php
/**
 * Login Endpoint
 */
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? $_POST['email'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit();
}

// Attempt login
$auth = getAuth();
$result = $auth->login($email, $password);

if ($result['success']) {
    echo json_encode([
        'message' => 'Login successful',
        'user' => $result['user'],
        'mustChangePassword' => $result['mustChangePassword'] ?? false
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => $result['error']]);
}
