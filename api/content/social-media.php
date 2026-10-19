<?php
/**
 * Social Media API - Create, Read, Update, Delete
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
        $socialMedia = $db->fetchAll("SELECT * FROM social_media WHERE is_active = 1 ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'data' => $socialMedia]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch social media: ' . $e->getMessage()]);
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
        if (empty($input['platform'])) {
            throw new Exception('Platform is required');
        }
        if (empty($input['url'])) {
            throw new Exception('URL is required');
        }
        
        // Insert new social media
        $id = $db->insert(
            "INSERT INTO social_media (platform, url, icon, is_active, created_at) VALUES (?, ?, ?, 1, NOW())",
            [
                trim($input['platform']),
                trim($input['url']),
                $input['icon'] ?? null
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to create social media');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Social media created successfully']);
    } catch (Exception $e) {
        error_log("Social Media API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create social media: ' . $e->getMessage()]);
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
        
        // Update social media
        $db->update(
            "UPDATE social_media SET platform = ?, url = ?, icon = ?, updated_at = NOW() WHERE id = ?",
            [
                trim($input['platform']),
                trim($input['url']),
                $input['icon'] ?? null,
                $input['id']
            ]
        );
        
        echo json_encode(['success' => true, 'message' => 'Social media updated successfully']);
    } catch (Exception $e) {
        error_log("Social Media API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update social media: ' . $e->getMessage()]);
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
        $db->update("UPDATE social_media SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        
        echo json_encode(['success' => true, 'message' => 'Social media deleted successfully']);
    } catch (Exception $e) {
        error_log("Social Media API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete social media: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
