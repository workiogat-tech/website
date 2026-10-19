<?php
/**
 * Programs API - Create, Read, Update, Delete
 */
// Start output buffering to catch any unexpected output
ob_start();

// Suppress error display for API endpoints (errors will be logged but not displayed)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header early
header('Content-Type: application/json; charset=utf-8');

// Prevent any output before this point
if (ob_get_level()) {
    ob_clean();
}

require_once __DIR__ . '/../../config/config.php';

// Clear any output that may have been generated after includes
if (ob_get_level()) {
    ob_clean();
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $programs = $db->fetchAll("SELECT * FROM programs WHERE is_active = 1 ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'data' => $programs]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch programs: ' . $e->getMessage()]);
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
        if (empty($input['name'])) {
            throw new Exception('Name is required');
        }
        
        // Insert new program
        $id = $db->insert(
            "INSERT INTO programs (name, description, duration, requirements, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())",
            [
                trim($input['name']),
                $input['description'] ?? null,
                $input['duration'] ?? null,
                $input['requirements'] ?? null,
                $input['image'] ?? null
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to create program');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Program created successfully']);
    } catch (Exception $e) {
        error_log("Programs API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create program: ' . $e->getMessage()]);
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
        
        // Update program
        $db->update(
            "UPDATE programs SET name = ?, description = ?, duration = ?, requirements = ?, image = ?, updated_at = NOW() WHERE id = ?",
            [
                trim($input['name']),
                $input['description'] ?? null,
                $input['duration'] ?? null,
                $input['requirements'] ?? null,
                $input['image'] ?? null,
                $input['id']
            ]
        );
        
        echo json_encode(['success' => true, 'message' => 'Program updated successfully']);
    } catch (Exception $e) {
        error_log("Programs API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update program: ' . $e->getMessage()]);
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
        $db->update("UPDATE programs SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        
        echo json_encode(['success' => true, 'message' => 'Program deleted successfully']);
    } catch (Exception $e) {
        error_log("Programs API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete program: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
if (ob_get_level()) {
    ob_end_flush();
}
exit;
?>
