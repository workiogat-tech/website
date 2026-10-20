<?php
/**
 * Resources Content API
 */
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
ob_clean();

header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        // Ensure table exists first
        $tableExists = $db->fetchOne("SHOW TABLES LIKE 'parents_content'");
        if (!$tableExists) {
            echo json_encode([]);
            exit;
        }
        
        $content = $db->fetchOne(
            "SELECT * FROM parents_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        error_log("Resources GET error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch resources content: ' . $e->getMessage()]);
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
        
        // Handle JSON input
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
        
        // Ensure parents_content table exists
        try {
            $tableExists = $db->fetchOne("SHOW TABLES LIKE 'parents_content'");
            if (!$tableExists) {
                $db->query("CREATE TABLE IF NOT EXISTS parents_content (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    hero_caption VARCHAR(255) DEFAULT 'Resources & Information',
                    hero_title VARCHAR(255) DEFAULT 'Resources',
                    hero_image VARCHAR(500),
                    section_title VARCHAR(255) DEFAULT 'Resources & Information',
                    section_subtitle TEXT,
                    resource_detail_sections TEXT,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
            } else {
                // Ensure columns exist
                $columns = $db->fetchAll("SHOW COLUMNS FROM parents_content");
                $columnNames = array_column($columns, 'Field');
                
                if (!in_array('hero_caption', $columnNames)) {
                    $db->query("ALTER TABLE parents_content ADD COLUMN hero_caption VARCHAR(255) DEFAULT 'Resources & Information' AFTER id");
                }
                if (!in_array('hero_title', $columnNames)) {
                    $db->query("ALTER TABLE parents_content ADD COLUMN hero_title VARCHAR(255) DEFAULT 'Resources' AFTER hero_caption");
                }
                if (!in_array('hero_image', $columnNames)) {
                    $db->query("ALTER TABLE parents_content ADD COLUMN hero_image VARCHAR(500) AFTER hero_title");
                }
                if (!in_array('section_title', $columnNames)) {
                    $db->query("ALTER TABLE parents_content ADD COLUMN section_title VARCHAR(255) DEFAULT 'Resources & Information' AFTER hero_image");
                }
                if (!in_array('section_subtitle', $columnNames)) {
                    $db->query("ALTER TABLE parents_content ADD COLUMN section_subtitle TEXT AFTER section_title");
                }
                if (!in_array('resource_detail_sections', $columnNames)) {
                    $db->query("ALTER TABLE parents_content ADD COLUMN resource_detail_sections TEXT AFTER section_subtitle");
                }
            }
        } catch (Exception $e) {
            error_log("Resources table check error: " . $e->getMessage());
            // Don't throw - continue with empty data
        }
        
        // Allowed fields
        $allowedFields = ['hero_caption', 'hero_title', 'hero_image', 'section_title', 'section_subtitle', 'resource_detail_sections'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                // Handle JSON fields
                if ($key === 'resource_detail_sections') {
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
        $existing = $db->fetchOne("SELECT * FROM parents_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE parents_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO parents_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save resources content');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Resources content saved successfully']);
    } catch (Exception $e) {
        error_log("Resources Content API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save resources content: ' . $e->getMessage()]);
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
    error_log("Unexpected output in resources.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
?>
