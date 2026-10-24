<?php
/**
 * Map Settings API
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
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'map_settings'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS map_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            latitude DECIMAL(10, 8),
            longitude DECIMAL(11, 8),
            zoom INT DEFAULT 15,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $columns = $db->fetchAll("SHOW COLUMNS FROM map_settings");
        $columnNames = array_column($columns, 'Field');
        if (!in_array('marker_title', $columnNames, true)) {
            $db->query("ALTER TABLE map_settings ADD COLUMN marker_title VARCHAR(200)");
        }
        if (!in_array('marker_description', $columnNames, true)) {
            $db->query("ALTER TABLE map_settings ADD COLUMN marker_description TEXT");
        }
        if (!in_array('zoom', $columnNames, true)) {
            $db->query("ALTER TABLE map_settings ADD COLUMN zoom INT DEFAULT 15");
        }
    }
} catch (Exception $e) {
    error_log("Map settings table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM map_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch map settings: ' . $e->getMessage()]);
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
        $allowedFields = ['latitude', 'longitude', 'zoom', 'marker_title', 'marker_description'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                $values[] = ($value === '' || $value === null) ? null : $value;
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM map_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE map_settings SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO map_settings (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save map settings');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Map settings saved successfully']);
    } catch (Exception $e) {
        error_log("Map Settings API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save map settings: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
