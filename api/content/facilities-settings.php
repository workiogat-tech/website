<?php
/**
 * Facilities Settings API
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

// Check if table exists, if not create it
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'facilities_settings'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS facilities_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hero_image VARCHAR(500),
            title VARCHAR(200) DEFAULT 'Our Facilities',
            subtitle TEXT,
            features TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Check and add features column if it doesn't exist
        $columns = $db->fetchAll("SHOW COLUMNS FROM facilities_settings");
        $columnNames = array_column($columns, 'Field');
        
        if (!in_array('features', $columnNames)) {
            $db->query("ALTER TABLE facilities_settings ADD COLUMN features TEXT");
        }
    }
} catch (Exception $e) {
    error_log("Facilities settings table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM facilities_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            // Decode features JSON if it exists
            if (!empty($content['features'])) {
                $decoded = json_decode($content['features'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $content['features'] = $decoded;
                }
            } else {
                $content['features'] = [];
            }
            echo json_encode($content);
        } else {
            echo json_encode(['features' => []]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch facilities settings: ' . $e->getMessage()]);
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
        $allowedFields = ['hero_image', 'title', 'subtitle', 'features'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                // Handle features as JSON
                if ($key === 'features') {
                    if (is_array($value)) {
                        $values[] = json_encode($value);
                    } else {
                        $values[] = ($value === '' || $value === null) ? null : $value;
                    }
                } else {
                    $values[] = ($value === '' || $value === null) ? null : $value;
                }
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM facilities_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE facilities_settings SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO facilities_settings (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save facilities settings');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Facilities settings saved successfully']);
    } catch (Exception $e) {
        error_log("Facilities Settings API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save facilities settings: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
$output = ob_get_clean();
if (!empty($output) && trim($output) !== '') {
    // If there's unexpected output, log it and return error
    error_log("Unexpected output in facilities-settings.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
    }
} else {
    // No unexpected output, response was already sent
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}
?>
