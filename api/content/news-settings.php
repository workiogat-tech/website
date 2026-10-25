<?php
/**
 * News Settings API
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

if ($method !== 'GET') {
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'news_settings'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS news_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hero_image VARCHAR(500),
            title VARCHAR(200) DEFAULT 'News',
            subtitle TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} catch (Exception $e) {
    error_log("News settings table check error: " . $e->getMessage());
}
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM news_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if (!$content) {
            $content = [];
        }
        try {
            $content['news'] = $db->fetchAll("SELECT * FROM news WHERE is_active = 1 ORDER BY created_at DESC");
        } catch (Exception $listError) {
            $content['news'] = [];
        }
        echo json_encode($content);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch news settings: ' . $e->getMessage()]);
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
        $allowedFields = ['hero_image', 'title', 'subtitle'];
        
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
        $existing = $db->fetchOne("SELECT * FROM news_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE news_settings SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO news_settings (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save news settings');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'News settings saved successfully']);
    } catch (Exception $e) {
        error_log("News Settings API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save news settings: ' . $e->getMessage()]);
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
    error_log("Unexpected output in news-settings.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
?>
