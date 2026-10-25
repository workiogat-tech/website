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
header('Cache-Control: no-store, no-cache, must-revalidate');

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

// Column setup runs on save. A page view only reads the saved row.
if ($method !== 'GET') {
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
}

function testimonialsSliderFlag($value): int {
    if ($value === false || $value === 0 || $value === '0' || $value === 'f' || $value === 'false' || $value === 'off') {
        return 0;
    }
    return 1;
}

function testimonialsSettingsRow(array $row): array {
    $row['enable_slider'] = testimonialsSliderFlag($row['enable_slider'] ?? 1);
    $row['slide_direction'] = (($row['slide_direction'] ?? '') === 'right') ? 'right' : 'left';
    $row['slide_interval_ms'] = max(1000, min(10000, intval($row['slide_interval_ms'] ?? 3000)));
    $row['items_per_view'] = max(1, min(6, intval($row['items_per_view'] ?? 3)));
    $row['card_width'] = max(200, min(600, intval($row['card_width'] ?? 280)));
    return $row;
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM testimonials_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            $content = testimonialsSettingsRow($content);
            try {
                $content['testimonials'] = $db->fetchAll(
                    "SELECT id, name, position, content, image, rating FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC"
                );
            } catch (Exception $listError) {
                $content['testimonials'] = [];
            }
            echo json_encode($content);
        } else {
            echo json_encode(['testimonials' => []]);
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
        
        $allowedFields = ['hero_image', 'title', 'subtitle', 'enable_slider', 'slide_direction', 'slide_interval_ms', 'items_per_view', 'card_width'];
        $existing = $db->fetchOne("SELECT * FROM testimonials_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: [];
        $record = [
            'hero_image' => $existing['hero_image'] ?? null,
            'title' => $existing['title'] ?? 'Testimonials',
            'subtitle' => $existing['subtitle'] ?? '',
            'enable_slider' => testimonialsSliderFlag($existing['enable_slider'] ?? 1),
            'slide_direction' => (($existing['slide_direction'] ?? '') === 'right') ? 'right' : 'left',
            'slide_interval_ms' => intval($existing['slide_interval_ms'] ?? 3000) ?: 3000,
            'items_per_view' => intval($existing['items_per_view'] ?? 3) ?: 3,
            'card_width' => intval($existing['card_width'] ?? 280) ?: 280,
        ];

        $received = false;
        foreach ($allowedFields as $key) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $received = true;
            $value = $input[$key];
            if ($key === 'enable_slider') {
                $record[$key] = testimonialsSliderFlag($value);
            } elseif (in_array($key, ['slide_interval_ms', 'items_per_view', 'card_width'], true)) {
                $record[$key] = is_numeric($value) ? intval($value) : $record[$key];
            } else {
                $record[$key] = ($value === null) ? null : $value;
            }
        }

        if (!$received) {
            throw new Exception('No valid fields to save');
        }

        $record = testimonialsSettingsRow($record);
        $fields = array_keys($record);
        $values = array_values($record);

        if (!empty($existing['id'])) {
            $sets = [];
            foreach ($fields as $field) {
                $sets[] = $field . ' = ?';
            }
            $values[] = $existing['id'];
            $db->update(
                "UPDATE testimonials_settings SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = ?",
                $values
            );
            $id = $existing['id'];
        } else {
            $placeholders = array_fill(0, count($fields), '?');
            $id = $db->insert(
                "INSERT INTO testimonials_settings (" . implode(', ', $fields) . ", is_active, created_at) VALUES (" . implode(', ', $placeholders) . ", 1, NOW())",
                array_values($record)
            );
        }

        if (!$id) {
            throw new Exception('Failed to save testimonials settings');
        }

        $saved = $db->fetchOne("SELECT * FROM testimonials_settings WHERE id = ?", [$id]) ?: $record;
        echo json_encode([
            'success' => true,
            'id' => $id,
            'message' => 'Testimonials settings saved successfully',
            'settings' => testimonialsSettingsRow($saved)
        ]);
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
