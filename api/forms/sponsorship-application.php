<?php
/**
 * Sponsorship Application Form Submission
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/emailService.php';

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

// Validate required fields
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');

if (empty($name) || empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Name and email are required']);
    exit();
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit();
}

try {
    $db = getDB();
    
    // Get coordinator email from sponsorship content
    $sponsorshipContent = $db->fetchOne(
        "SELECT coordinator_email FROM sponsorship_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
    );
    $coordinatorEmail = $sponsorshipContent['coordinator_email'] ?? null;
    
    // Prepare submission data
    $submissionData = [
        'name' => $name,
        'email' => $email,
        'phone' => $input['phone'] ?? null,
        'sponsorship_level' => $input['sponsorship_level'] ?? null,
        'message' => $input['message'] ?? null,
        'coordinatorEmail' => $coordinatorEmail,
        'submittedAt' => date('c')
    ];
    
    // Save to database
    $db->insert(
        "INSERT INTO form_submissions (submission_type, data) VALUES (?, ?)",
        ['sponsorship-application', json_encode($submissionData)]
    );
    
    // Create notification
    $notificationMessage = $coordinatorEmail 
        ? "A new sponsorship application was submitted. Contact: $coordinatorEmail"
        : 'A new sponsorship application was submitted.';
    
    $db->insert(
        "INSERT INTO notifications (title, message, category) VALUES (?, ?, ?)",
        ['New Sponsorship Application', $notificationMessage, 'forms']
    );
    
    // Send email notification (async) - always send to admin emails
    $emailService = getEmailService();
    register_shutdown_function(function() use ($emailService, $submissionData, $coordinatorEmail) {
        $emailService->sendSponsorshipApplicationEmail($submissionData, $coordinatorEmail);
    });
    
    http_response_code(201);
    echo json_encode([
        'message' => 'Sponsorship application received',
        'coordinatorEmail' => $coordinatorEmail
    ]);
} catch (Exception $e) {
    error_log("Sponsorship application error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit sponsorship application']);
}
