<?php
/**
 * Contact Form Submission
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
$message = trim($input['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, email, and message are required']);
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
    
    // Get contact email from database
    $contactContent = $db->fetchOne(
        "SELECT email FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
    );
    $contactEmail = $contactContent['email'] ?? null;
    
    // Prepare submission data
    $submissionData = [
        'name' => $name,
        'email' => $email,
        'phone' => $input['phone'] ?? null,
        'subject' => $input['subject'] ?? null,
        'message' => $message,
        'submittedAt' => date('c')
    ];
    
    // Save to database
    $db->insert(
        "INSERT INTO form_submissions (submission_type, data) VALUES (?, ?)",
        ['contact', json_encode($submissionData)]
    );
    
    // Create notification
    $db->insert(
        "INSERT INTO notifications (title, message, category) VALUES (?, ?, ?)",
        ['New Contact Form Submission', "New message from $name", 'forms']
    );
    
    // Send email notification (async - don't wait for it) - always send to admin emails
    $emailService = getEmailService();
    // Send in background (don't block response)
    register_shutdown_function(function() use ($emailService, $submissionData, $contactEmail) {
        $emailService->sendContactFormEmail($submissionData, $contactEmail);
    });
    
    http_response_code(201);
    echo json_encode([
        'message' => 'Contact form submitted successfully',
        'contactEmail' => $contactEmail
    ]);
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit contact form']);
}
