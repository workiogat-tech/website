<?php
/**
 * Teachers API - Create, Read, Update, Delete
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
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Get single teacher
            $teacher = $db->fetchOne("SELECT * FROM teachers WHERE id = ? AND is_active = 1", [$id]);
            if ($teacher) {
                echo json_encode(['success' => true, 'data' => $teacher]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Teacher not found']);
            }
        } else {
            // Get all teachers
            $teachers = $db->fetchAll("SELECT * FROM teachers WHERE is_active = 1 ORDER BY created_at DESC");
            echo json_encode(['success' => true, 'data' => $teachers]);
        }
    } catch (Exception $e) {
        error_log("Teachers GET error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch teachers: ' . $e->getMessage()]);
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
        
        // Insert new teacher
        $id = $db->insert(
            "INSERT INTO teachers (name, position, department, bio, image, email, phone, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())",
            [
                trim($input['name']),
                $input['position'] ?? null,
                $input['department'] ?? null,
                $input['bio'] ?? null,
                $input['image'] ?? null,
                $input['email'] ?? null,
                $input['phone'] ?? null
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to create teacher');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Teacher created successfully']);
    } catch (Exception $e) {
        error_log("Teachers API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create teacher: ' . $e->getMessage()]);
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
        
        // Update teacher
        $db->update(
            "UPDATE teachers SET name = ?, position = ?, department = ?, bio = ?, image = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?",
            [
                trim($input['name']),
                $input['position'] ?? null,
                $input['department'] ?? null,
                $input['bio'] ?? null,
                $input['image'] ?? null,
                $input['email'] ?? null,
                $input['phone'] ?? null,
                $input['id']
            ]
        );
        
        echo json_encode(['success' => true, 'message' => 'Teacher updated successfully']);
    } catch (Exception $e) {
        error_log("Teachers API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update teacher: ' . $e->getMessage()]);
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
        $db->update("UPDATE teachers SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        
        echo json_encode(['success' => true, 'message' => 'Teacher deleted successfully']);
    } catch (Exception $e) {
        error_log("Teachers API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete teacher: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
