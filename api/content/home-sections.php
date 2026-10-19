<?php
/**
 * Home Sections API - Create, Read, Update, Delete
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
        $sections = $db->fetchAll("SELECT * FROM home_sections_content WHERE is_active = 1 ORDER BY order_index ASC");
        echo json_encode(['success' => true, 'data' => $sections]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch home sections: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
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
        
        // Validate required fields
        if (empty($input['section_name'])) {
            throw new Exception('Section name is required');
        }
        
        // Get max order_index
        $maxOrder = $db->fetchOne("SELECT MAX(order_index) as max_order FROM home_sections_content");
        $orderIndex = ($maxOrder['max_order'] ?? 0) + 1;
        
        // Insert new section
        $id = $db->insert(
            "INSERT INTO home_sections_content (section_name, title, subtitle, content, image, order_index, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())",
            [
                trim($input['section_name']),
                $input['title'] ?? null,
                $input['subtitle'] ?? null,
                $input['content'] ?? null,
                $input['image'] ?? null,
                $orderIndex
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to create home section');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Home section created successfully']);
    } catch (Exception $e) {
        error_log("Home Sections API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create home section: ' . $e->getMessage()]);
    }
} elseif ($method === 'PUT') {
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
        } else {
            $input = $_POST;
        }
        
        if (empty($input['id'])) {
            throw new Exception('ID is required');
        }
        
        // Update section
        $db->update(
            "UPDATE home_sections_content SET section_name = ?, title = ?, subtitle = ?, content = ?, image = ?, order_index = ?, updated_at = NOW() WHERE id = ?",
            [
                trim($input['section_name']),
                $input['title'] ?? null,
                $input['subtitle'] ?? null,
                $input['content'] ?? null,
                $input['image'] ?? null,
                $input['order_index'] ?? 0,
                $input['id']
            ]
        );
        
        echo json_encode(['success' => true, 'message' => 'Home section updated successfully']);
    } catch (Exception $e) {
        error_log("Home Sections API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update home section: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    try {
        require_once __DIR__ . '/../../includes/auth.php';
        $auth = getAuth();
        if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            throw new Exception('ID is required');
        }
        
        // Soft delete
        $db->update("UPDATE home_sections_content SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        
        echo json_encode(['success' => true, 'message' => 'Home section deleted successfully']);
    } catch (Exception $e) {
        error_log("Home Sections API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete home section: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
