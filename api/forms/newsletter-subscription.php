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

$email = strtolower(trim($input['email'] ?? ''));
$name = trim($input['name'] ?? '');

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

    // Ensure newsletter_subscribers table exists
    try {
        $tableExists = $db->fetchOne("SHOW TABLES LIKE 'newsletter_subscribers'");
        if (!$tableExists) {
            $db->query("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                name VARCHAR(255) NULL,
                is_active TINYINT(1) DEFAULT 1,
                subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                unsubscribed_at TIMESTAMP NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
        }
    } catch (Exception $schemaError) {
        error_log("Newsletter subscribers table check: " . $schemaError->getMessage());
    }

    $alreadySubscribed = false;
    try {
        $existing = $db->fetchOne(
            "SELECT id, is_active FROM newsletter_subscribers WHERE email = ? LIMIT 1",
            [$email]
        );
        if ($existing) {
            if (!empty($existing['is_active'])) {
                $alreadySubscribed = true;
            } else {
                $db->update(
                    "UPDATE newsletter_subscribers SET is_active = 1, name = COALESCE(NULLIF(?, ''), name), unsubscribed_at = NULL, updated_at = NOW() WHERE id = ?",
                    [$name, $existing['id']]
                );
            }
        } else {
            $db->insert(
                "INSERT INTO newsletter_subscribers (email, name, is_active, subscribed_at) VALUES (?, ?, 1, NOW())",
                [$email, $name !== '' ? $name : null]
            );
        }
    } catch (Exception $subscriberError) {
        error_log("Newsletter subscriber save error: " . $subscriberError->getMessage());
    }

    // Keep legacy form_submissions record for admin visibility
    $submissionData = [
        'email' => $email,
        'name' => $name,
        'submittedAt' => date('c')
    ];

    $db->insert(
        "INSERT INTO form_submissions (submission_type, data) VALUES (?, ?)",
        ['newsletter-subscription', json_encode($submissionData)]
    );

    http_response_code(201);
    echo json_encode([
        'message' => $alreadySubscribed
            ? 'You are already subscribed to the newsletter'
            : 'Subscribed to newsletter',
        'already_subscribed' => $alreadySubscribed
    ]);
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to subscribe to newsletter']);
}
