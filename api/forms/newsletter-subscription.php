<?php
/**
 * Newsletter Subscription Form Submission
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate email
$email = trim($input['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit();
}

try {
    $db = getDB();
    
    // Save to database
    $submissionData = [
        'email' => $email,
        'submittedAt' => date('c')
    ];
    
    $db->insert(
        "INSERT INTO form_submissions (submission_type, data) VALUES (?, ?)",
        ['newsletter-subscription', json_encode($submissionData)]
    );
    
    http_response_code(201);
    echo json_encode(['message' => 'Subscribed to newsletter']);
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to subscribe to newsletter']);
}
