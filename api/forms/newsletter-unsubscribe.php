<?php
/**
 * Newsletter Unsubscribe
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$email = strtolower(trim($input['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid email is required']);
    exit();
}

try {
    $db = getDB();
    $existing = $db->fetchOne(
        "SELECT id FROM newsletter_subscribers WHERE email = ? LIMIT 1",
        [$email]
    );

    if ($existing) {
        $db->update(
            "UPDATE newsletter_subscribers SET is_active = 0, unsubscribed_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$existing['id']]
        );
    }

    echo json_encode(['message' => 'You have been unsubscribed from the newsletter']);
} catch (Exception $e) {
    error_log("Newsletter unsubscribe error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to unsubscribe']);
}
