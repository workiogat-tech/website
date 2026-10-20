<?php
/**
 * Give Back Content API
 */
// Start output buffering to catch any unexpected output
ob_start();

// Suppress error display for API endpoints (errors will be logged but not displayed)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';

// Clear any output that may have been generated
ob_clean();

// Set JSON header
header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Ensure payment_details, contact_phone, contact_email columns exist
try {
    $columns = $db->fetchAll("SHOW COLUMNS FROM giveback_content");
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('payment_details', $columnNames)) {
        $db->query("ALTER TABLE giveback_content ADD COLUMN payment_details TEXT");
    }
    if (!in_array('contact_phone', $columnNames)) {
        $db->query("ALTER TABLE giveback_content ADD COLUMN contact_phone VARCHAR(50)");
    }
    if (!in_array('contact_email', $columnNames)) {
        $db->query("ALTER TABLE giveback_content ADD COLUMN contact_email VARCHAR(100)");
    }
    if (!in_array('needs', $columnNames)) {
        $db->query("ALTER TABLE giveback_content ADD COLUMN needs TEXT");
    }
} catch (Exception $e) {
    error_log("Giveback content column check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM giveback_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            // Parse JSON fields
            if (!empty($content['payment_details'])) {
                $decoded = json_decode($content['payment_details'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $content['payment_details'] = $decoded;
                } else {
                    $content['payment_details'] = [];
                }
            } else {
                $content['payment_details'] = [];
            }
            if (!empty($content['needs'])) {
                $decoded = json_decode($content['needs'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $content['needs'] = $decoded;
                } else {
                    $content['needs'] = [];
                }
            } else {
                $content['needs'] = [];
            }
            echo json_encode($content);
        } else {
            echo json_encode(['payment_details' => [], 'needs' => []]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch giveback content: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST' || $method === 'PUT') {
    try {
        require_once __DIR__ . '/../../includes/auth.php';
        $auth = getAuth();
        if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        // Handle both JSON and form data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }
        
        $title = trim($input['title'] ?? '');
        $subtitle = trim($input['subtitle'] ?? '');
        $description = trim($input['description'] ?? '');
        $heroImage = trim($input['hero_image'] ?? '');
        $contactPhone = trim($input['contact_phone'] ?? '');
        $contactEmail = trim($input['contact_email'] ?? '');
        
        // Handle payment_details
        $paymentDetails = null;
        if (isset($input['payment_details']) && is_array($input['payment_details'])) {
            $paymentDetails = json_encode($input['payment_details']);
        } elseif (isset($input['payment_details']) && is_string($input['payment_details'])) {
            $paymentDetails = $input['payment_details'];
        }
        
        // Handle needs
        $needs = null;
        if (isset($input['needs'])) {
            if (is_array($input['needs'])) {
                $needs = json_encode($input['needs']);
            } elseif (is_string($input['needs'])) {
                $needs = $input['needs'];
            }
        }
        
        // Check if content exists
        $existing = $db->fetchOne(
            "SELECT id FROM giveback_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($existing) {
            // Update existing
            $db->query(
                "UPDATE giveback_content SET 
                    title = ?, 
                    subtitle = ?, 
                    description = ?, 
                    hero_image = ?,
                    contact_phone = ?,
                    contact_email = ?,
                    payment_details = ?,
                    needs = ?,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?",
                [
                    $title ?: null,
                    $subtitle ?: null,
                    $description ?: null,
                    $heroImage ?: null,
                    $contactPhone ?: null,
                    $contactEmail ?: null,
                    $paymentDetails,
                    $needs,
                    $existing['id']
                ]
            );
            
            $updated = $db->fetchOne("SELECT * FROM giveback_content WHERE id = ?", [$existing['id']]);
            echo json_encode([
                'message' => 'Giveback content updated successfully',
                'data' => $updated
            ]);
        } else {
            // Insert new
            $db->query(
                "INSERT INTO giveback_content (title, subtitle, description, hero_image, contact_phone, contact_email, payment_details, needs, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [
                    $title ?: null,
                    $subtitle ?: null,
                    $description ?: null,
                    $heroImage ?: null,
                    $contactPhone ?: null,
                    $contactEmail ?: null,
                    $paymentDetails,
                    $needs
                ]
            );
            
            $newId = $db->lastInsertId();
            $newContent = $db->fetchOne("SELECT * FROM giveback_content WHERE id = ?", [$newId]);
            echo json_encode([
                'message' => 'Giveback content created successfully',
                'data' => $newContent
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save giveback content: ' . $e->getMessage()]);
        error_log("Giveback content save error: " . $e->getMessage());
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
$output = ob_get_clean();
$trimmed = trim($output);
if ($trimmed === '') {
    // Response was already sent.
} elseif ($trimmed[0] === '{' || $trimmed[0] === '[') {
    echo $output;
} else {
    error_log("Unexpected output in giveback.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
