<?php
/**
 * Footer Content API
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
            "SELECT * FROM footer_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch footer content: ' . $e->getMessage()]);
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
        
        // Process JSON fields
        $socialLinksJson = null;
        if (isset($input['social_links'])) {
            if (is_string($input['social_links'])) {
                $socialLinksJson = $input['social_links'];
            } elseif (is_array($input['social_links'])) {
                $socialLinksJson = json_encode($input['social_links']);
            }
        }
        
        $quickLinksJson = null;
        if (isset($input['quick_links'])) {
            if (is_string($input['quick_links'])) {
                $quickLinksJson = $input['quick_links'];
            } elseif (is_array($input['quick_links'])) {
                $quickLinksJson = json_encode($input['quick_links']);
            }
        }
        
        // Allowed fields
        $allowedFields = ['copyright_text', 'social_links', 'quick_links'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key === 'social_links') {
                $fields[] = 'social_links';
                $values[] = $socialLinksJson;
            } elseif ($key === 'quick_links') {
                $fields[] = 'quick_links';
                $values[] = $quickLinksJson;
            } elseif ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                $values[] = ($value === '' || $value === null) ? null : $value;
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM footer_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE footer_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO footer_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save footer content');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Footer content saved successfully']);
    } catch (Exception $e) {
        error_log("Footer Content API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save footer content: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
