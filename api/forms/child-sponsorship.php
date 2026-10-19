<?php
/**
 * Child Sponsorship Form Submission
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
$sponsorName = trim($input['sponsor_name'] ?? '');
$sponsorEmail = trim($input['sponsor_email'] ?? '');

if (empty($sponsorName) || empty($sponsorEmail)) {
    http_response_code(400);
    echo json_encode(['error' => 'Sponsor name and email are required']);
    exit();
}

// Validate email
if (!filter_var($sponsorEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit();
}

try {
    $db = getDB();
    
    // Get coordinator contact from sponsorship content
    $sponsorshipContent = $db->fetchOne(
        "SELECT coordinator_email, coordinator_phone FROM sponsorship_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
    );
    $coordinatorEmail = $sponsorshipContent['coordinator_email'] ?? null;
    $coordinatorPhone = $sponsorshipContent['coordinator_phone'] ?? null;
    
    // Prepare submission data
    $submissionData = [
        'sponsor_name' => $sponsorName,
        'sponsor_email' => $sponsorEmail,
        'sponsor_phone' => $input['sponsor_phone'] ?? null,
        'child_name' => $input['child_name'] ?? null,
        'sponsorship_type' => $input['sponsorship_type'] ?? null,
        'amount' => $input['amount'] ?? null,
        'message' => $input['message'] ?? null,
        'coordinatorEmail' => $coordinatorEmail,
        'coordinatorPhone' => $coordinatorPhone,
        'submittedAt' => date('c')
    ];
    
    // Save to database
    $db->insert(
        "INSERT INTO form_submissions (submission_type, data) VALUES (?, ?)",
        ['child-sponsorship', json_encode($submissionData)]
    );
    
    // Create notification
    $notificationMessage = $coordinatorEmail 
        ? "A new child sponsorship request was submitted. Contact: $coordinatorEmail" . ($coordinatorPhone ? " | $coordinatorPhone" : "")
        : 'A new child sponsorship request was submitted.';
    
    $db->insert(
        "INSERT INTO notifications (title, message, category) VALUES (?, ?, ?)",
        ['New Child Sponsorship Request', $notificationMessage, 'forms']
    );
    
    // Send email notification (async) - always send to admin emails
    $emailService = getEmailService();
    register_shutdown_function(function() use ($emailService, $submissionData, $coordinatorEmail) {
        $emailService->sendChildSponsorshipEmail($submissionData, $coordinatorEmail);
    });
    
    http_response_code(201);
    echo json_encode(['message' => 'Child sponsorship request received']);
} catch (Exception $e) {
    error_log("Child sponsorship error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit child sponsorship request']);
}
