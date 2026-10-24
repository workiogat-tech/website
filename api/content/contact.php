<?php
/**
 * Contact Content API
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

// Check if hero_image and image columns exist, if not add them
try {
    $columns = $db->fetchAll("SHOW COLUMNS FROM contact_content");
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('hero_image', $columnNames)) {
        $db->query("ALTER TABLE contact_content ADD COLUMN hero_image VARCHAR(500) AFTER map_embed");
    }
    if (!in_array('image', $columnNames)) {
        $db->query("ALTER TABLE contact_content ADD COLUMN image VARCHAR(500) AFTER hero_image");
    }
} catch (Exception $e) {
    error_log("Contact content column check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch contact content: ' . $e->getMessage()]);
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
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }
        } else {
            $input = $_POST;
        }
        
        if (empty($input)) {
            throw new Exception('No data provided');
        }
        
        // Allowed fields. A contact screen has more than one form, so keep
        // values that this request did not send.
        $allowedFields = ['address', 'phone', 'email', 'hours', 'intro_text', 'map_embed', 'hero_image', 'image'];
        $existing = $db->fetchOne("SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: [];
        
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $value = $input[$field];
                $fields[] = $field;
                $values[] = ($value === '' || $value === null) ? null : $value;
            } elseif (array_key_exists($field, $existing) && $existing[$field] !== null && $existing[$field] !== '') {
                $fields[] = $field;
                $values[] = $existing[$field];
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        if (!empty($existing['id'])) {
            $db->update("UPDATE contact_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO contact_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save contact content');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Contact content saved successfully']);
    } catch (Exception $e) {
        error_log("Contact Content API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save contact content: ' . $e->getMessage()]);
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
    error_log("Unexpected output in contact.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
?>
