<?php
/**
 * School Background Content API
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

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM school_background_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch school background content: ' . $e->getMessage()]);
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
        
        // Allowed fields
        $allowedFields = ['title', 'subtitle', 'content', 'content_title', 'hero_image', 'history_image', 'content_image', 'video_url', 'video_embed_code', 'history_section', 'milestones'];
        
        // Ensure columns exist
        try {
            $columns = $db->fetchAll("SHOW COLUMNS FROM school_background_content");
            $columnNames = array_column($columns, 'Field');
            if (!in_array('history_section', $columnNames)) {
                $db->query("ALTER TABLE school_background_content ADD COLUMN history_section TEXT");
            }
            if (!in_array('milestones', $columnNames)) {
                $db->query("ALTER TABLE school_background_content ADD COLUMN milestones TEXT");
            }
            if (!in_array('history_image', $columnNames)) {
                $db->query("ALTER TABLE school_background_content ADD COLUMN history_image VARCHAR(500) AFTER hero_image");
            }
            if (!in_array('content_image', $columnNames)) {
                $db->query("ALTER TABLE school_background_content ADD COLUMN content_image VARCHAR(500) AFTER history_image");
            }
            if (!in_array('video_embed_code', $columnNames)) {
                $db->query("ALTER TABLE school_background_content ADD COLUMN video_embed_code TEXT AFTER video_url");
            }
            if (!in_array('content_title', $columnNames)) {
                $db->query("ALTER TABLE school_background_content ADD COLUMN content_title VARCHAR(255) AFTER content");
            }
        } catch (Exception $e) {
            error_log("School background column check error: " . $e->getMessage());
        }
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                // Handle JSON fields
                if ($key === 'history_section' || $key === 'milestones') {
                    $values[] = is_array($value) ? json_encode($value) : ($value ?: null);
                } else {
                    $values[] = ($value === '' || $value === null) ? null : $value;
                }
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM school_background_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE school_background_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO school_background_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save school background content');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'School background content saved successfully']);
    } catch (Exception $e) {
        error_log("School Background Content API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save school background content: ' . $e->getMessage()]);
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
    error_log("Unexpected output in school-background.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
?>
