<?php
/**
 * Media Sections API - Create, List, Update, Delete sections
 */
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

ob_clean();
header('Content-Type: application/json');

$auth = getAuth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Ensure table exists
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'media_sections'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS media_sections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            description TEXT,
            color VARCHAR(20) DEFAULT '#3B82F6',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} catch (Exception $e) {
    error_log("Media sections table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $sections = $db->fetchAll("SELECT * FROM media_sections WHERE is_active = 1 ORDER BY name ASC");
        echo json_encode(['success' => true, 'sections' => $sections ?: []]);
    } catch (Exception $e) {
        error_log("Sections GET error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch sections: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    // Handle JSON or form data
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
    } else {
        $input = $_POST;
    }
    
    $action = $input['action'] ?? '';
    
    if ($action === 'create_section') {
        $name = trim($input['name'] ?? '');
        $color = $input['color'] ?? '#3B82F6';
        
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Section name is required']);
            exit;
        }
        
        try {
            // Check if section with same name already exists
            $existing = $db->fetchOne("SELECT id FROM media_sections WHERE name = ? AND is_active = 1", [$name]);
            if ($existing) {
                http_response_code(400);
                echo json_encode(['error' => 'A section with this name already exists']);
                exit;
            }
            
            $id = $db->insert("INSERT INTO media_sections (name, color, is_active, created_at) VALUES (?, ?, 1, NOW())", [$name, $color]);
            
            if ($id) {
                echo json_encode(['success' => true, 'message' => 'Section created successfully', 'section' => ['id' => $id, 'name' => $name, 'color' => $color]]);
            } else {
                throw new Exception('Failed to create section');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create section: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

ob_end_flush();
