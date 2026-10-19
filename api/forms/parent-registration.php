<?php
/**
 * Parent Registration Form Submission
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

try {
    $db = getDB();
    
    // Save to database
    $submissionData = array_merge($input, ['submittedAt' => date('c')]);
    
    $db->insert(
        "INSERT INTO form_submissions (submission_type, data) VALUES (?, ?)",
        ['parent-registration', json_encode($submissionData)]
    );
    
    // Create notification
    $db->insert(
        "INSERT INTO notifications (title, message, category) VALUES (?, ?, ?)",
        ['New Parent Registration', 'A new parent has registered interest.', 'forms']
    );
    
    http_response_code(201);
    echo json_encode(['message' => 'Parent registration received']);
} catch (Exception $e) {
    error_log("Parent registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit parent registration']);
}
