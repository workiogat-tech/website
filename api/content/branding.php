<?php
/**
 * Branding API
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

// Ensure branding table exists and favicon column can store long media URLs
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'branding'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS branding (
            id INT AUTO_INCREMENT PRIMARY KEY,
            school_name VARCHAR(200),
            tagline TEXT,
            logo TEXT,
            favicon TEXT,
            primary_color VARCHAR(7),
            secondary_color VARCHAR(7),
            font_family VARCHAR(100),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        $columns = $db->fetchAll("SHOW COLUMNS FROM branding");
        $columnNames = [];
        foreach ($columns as $column) {
            $name = strtolower((string)($column['Field'] ?? $column['field'] ?? $column['column_name'] ?? ''));
            if ($name !== '') {
                $columnNames[] = $name;
            }
        }
        if (!in_array('favicon', $columnNames, true)) {
            try {
                $db->query("ALTER TABLE branding ADD COLUMN favicon TEXT NULL");
            } catch (Exception $e) {
                error_log('Branding favicon column add failed: ' . $e->getMessage());
            }
        } else {
            try {
                $db->query("ALTER TABLE branding MODIFY COLUMN favicon TEXT");
            } catch (Exception $e) {
                try {
                    $db->query("ALTER TABLE branding ALTER COLUMN favicon TYPE TEXT");
                } catch (Exception $e2) {
                    error_log('Branding favicon widen skipped: ' . $e2->getMessage());
                }
            }
        }
        if (!in_array('font_family', $columnNames, true)) {
            try {
                $db->query("ALTER TABLE branding ADD COLUMN font_family VARCHAR(100) NULL");
            } catch (Exception $e) {
                error_log('Branding font_family column add failed: ' . $e->getMessage());
            }
        }
        if (in_array('logo', $columnNames, true)) {
            try {
                $db->query("ALTER TABLE branding MODIFY COLUMN logo TEXT");
            } catch (Exception $e) {
                try {
                    $db->query("ALTER TABLE branding ALTER COLUMN logo TYPE TEXT");
                } catch (Exception $e2) {
                    // ignore
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Branding table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch branding: ' . $e->getMessage()]);
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
        
        // Allowed fields in branding table
        $allowedFields = ['school_name', 'tagline', 'logo', 'favicon', 'primary_color', 'secondary_color', 'font_family'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            // Skip system fields and only allow valid database columns
            if ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                // Handle empty strings as NULL for optional fields
                $values[] = ($value === '' || $value === null) ? null : $value;
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");

        // Keep the previous favicon if the form submitted an empty one by mistake
        $faviconIndex = array_search('favicon', $fields, true);
        if ($faviconIndex !== false && ($values[$faviconIndex] === null || $values[$faviconIndex] === '') && $existing && !empty($existing['favicon'])) {
            $values[$faviconIndex] = $existing['favicon'];
        }
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE branding SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO branding (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save branding');
        }

        $saved = $db->fetchOne("SELECT * FROM branding WHERE id = ?", [$id]) ?: [];
        
        echo json_encode([
            'success' => true,
            'id' => $id,
            'message' => 'Branding saved successfully',
            'favicon' => $saved['favicon'] ?? ($input['favicon'] ?? null)
        ]);
    } catch (Exception $e) {
        error_log("Branding API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save branding: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
