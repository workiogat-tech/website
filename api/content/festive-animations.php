<?php
/**
 * Festive Animations Settings API
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

// Ensure table exists with correct structure
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'festive_animations_settings'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS festive_animations_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            enabled BOOLEAN DEFAULT FALSE,
            opacity DECIMAL(3,2) DEFAULT 0.9,
            speed DECIMAL(3,2) DEFAULT 0.6,
            size DECIMAL(3,2) DEFAULT 2.0,
            intensity DECIMAL(3,2) DEFAULT 1.0,
            colors TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Check and add missing columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM festive_animations_settings");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'enabled' => "ALTER TABLE festive_animations_settings ADD COLUMN enabled BOOLEAN DEFAULT FALSE AFTER id",
            'opacity' => "ALTER TABLE festive_animations_settings ADD COLUMN opacity DECIMAL(3,2) DEFAULT 0.9 AFTER enabled",
            'speed' => "ALTER TABLE festive_animations_settings ADD COLUMN speed DECIMAL(3,2) DEFAULT 0.6 AFTER opacity",
            'size' => "ALTER TABLE festive_animations_settings ADD COLUMN size DECIMAL(3,2) DEFAULT 2.0 AFTER speed",
            'intensity' => "ALTER TABLE festive_animations_settings ADD COLUMN intensity DECIMAL(3,2) DEFAULT 1.0 AFTER size",
            'colors' => "ALTER TABLE festive_animations_settings ADD COLUMN colors TEXT AFTER intensity",
        ];
        
        foreach ($requiredColumns as $colName => $alterSql) {
            if (!in_array($colName, $columnNames)) {
                try {
                    $db->query($alterSql);
                } catch (Exception $e) {
                    error_log("Error adding column $colName to festive_animations_settings: " . $e->getMessage());
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Festive animations table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM festive_animations_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch festive animations settings: ' . $e->getMessage()]);
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
        
        // Process colors JSON
        $colorsJson = null;
        if (isset($input['colors'])) {
            if (is_string($input['colors'])) {
                // Try to decode if it's a JSON string
                $decoded = json_decode($input['colors'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $colorsJson = json_encode($decoded);
                } else {
                    $colorsJson = $input['colors'];
                }
            } elseif (is_array($input['colors'])) {
                $colorsJson = json_encode($input['colors']);
            }
        }
        
        // Process opacity and intensity from percentage
        if (isset($input['opacity_percent'])) {
            $input['opacity'] = floatval($input['opacity_percent']) / 100;
        }
        if (isset($input['intensity_percent'])) {
            $input['intensity'] = floatval($input['intensity_percent']) / 100;
        }
        
        // Allowed fields
        $allowedFields = ['enabled', 'opacity', 'speed', 'size', 'intensity', 'colors'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key === 'colors') {
                $fields[] = 'colors';
                $values[] = $colorsJson;
            } elseif ($key === 'enabled') {
                $fields[] = 'enabled';
                // Handle various formats: 'on', true, '1', 1, 'true', etc.
                $enabledValue = ($value === 'on' || $value === true || $value === '1' || $value === 1 || $value === 'true') ? 1 : 0;
                $values[] = $enabledValue;
            } elseif ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                $values[] = ($value === '' || $value === null) ? null : $value;
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM festive_animations_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE festive_animations_settings SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO festive_animations_settings (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save festive animations settings');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Festive animations settings saved successfully']);
    } catch (Exception $e) {
        error_log("Festive Animations Settings API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save festive animations settings: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
