<?php
/**
 * Testimonials Settings API
 */
// Start output buffering to catch any unexpected output
ob_start();

// Suppress error display for API endpoints (errors will be logged but not displayed)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header early - MUST be before any output
header('Content-Type: application/json; charset=utf-8');

// Prevent any output before this point
if (ob_get_level()) {
    ob_clean();
}

// Wrap everything in try-catch to ensure JSON response even on fatal errors
try {
    require_once __DIR__ . '/../../config/config.php';
    
    // Re-suppress error display after config.php (which may set it to 1)
    ini_set('display_errors', 0);
    
    // Clear any output that may have been generated after includes
    if (ob_get_level()) {
        ob_clean();
    }

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Check if table exists, if not create it
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'testimonials_settings'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS testimonials_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hero_image VARCHAR(500),
            title VARCHAR(200) DEFAULT 'Testimonials',
            subtitle TEXT,
            enable_slider BOOLEAN DEFAULT TRUE,
            slide_direction VARCHAR(20) DEFAULT 'left',
            slide_interval_ms INT DEFAULT 3000,
            items_per_view INT DEFAULT 3,
            card_width INT DEFAULT 280,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Check and add missing columns if they don't exist
        $columns = $db->fetchAll("SHOW COLUMNS FROM testimonials_settings");
        $columnNames = array_column($columns, 'Field');
        
        if (!in_array('enable_slider', $columnNames)) {
            $db->query("ALTER TABLE testimonials_settings ADD COLUMN enable_slider BOOLEAN DEFAULT TRUE");
        }
        if (!in_array('slide_direction', $columnNames)) {
            $db->query("ALTER TABLE testimonials_settings ADD COLUMN slide_direction VARCHAR(20) DEFAULT 'left'");
        }
        if (!in_array('slide_interval_ms', $columnNames)) {
            $db->query("ALTER TABLE testimonials_settings ADD COLUMN slide_interval_ms INT DEFAULT 3000");
        }
        if (!in_array('items_per_view', $columnNames)) {
            $db->query("ALTER TABLE testimonials_settings ADD COLUMN items_per_view INT DEFAULT 3");
        }
        if (!in_array('card_width', $columnNames)) {
            $db->query("ALTER TABLE testimonials_settings ADD COLUMN card_width INT DEFAULT 280");
        }
    }
} catch (Exception $e) {
    error_log("Testimonials settings table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM testimonials_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch testimonials settings: ' . $e->getMessage()]);
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
        $allowedFields = ['hero_image', 'title', 'subtitle', 'enable_slider', 'slide_direction', 'slide_interval_ms', 'items_per_view', 'card_width'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                // Handle boolean and integer values properly
                if ($key === 'enable_slider') {
                    $values[] = ($value === 'true' || $value === true || $value === '1' || $value === 1) ? 1 : 0;
                } elseif (in_array($key, ['slide_interval_ms', 'items_per_view', 'card_width'])) {
                    $values[] = is_numeric($value) ? intval($value) : ($key === 'slide_interval_ms' ? 3000 : ($key === 'items_per_view' ? 3 : 280));
                } else {
                    $values[] = ($value === '' || $value === null) ? null : $value;
                }
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM testimonials_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE testimonials_settings SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO testimonials_settings (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save testimonials settings');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Testimonials settings saved successfully']);
    } catch (Exception $e) {
        error_log("Testimonials Settings API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save testimonials settings: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
} catch (Throwable $e) {
    // Catch any fatal errors or exceptions
    error_log("Testimonials Settings API Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    // Clear any output
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Ensure JSON header is set
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

// Clean output buffer and send response
if (ob_get_level()) {
    ob_end_flush();
}
exit;
?>
