<?php
/**
 * News API - Create, Read, Update, Delete
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
        $news = $db->fetchAll("SELECT * FROM news WHERE is_active = 1 ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'data' => $news]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch news: ' . $e->getMessage()]);
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
        if (empty($input['title'])) {
            throw new Exception('Title is required');
        }
        
        // Generate excerpt if not provided
        $excerpt = $input['excerpt'] ?? null;
        if (!$excerpt && !empty($input['content'])) {
            $excerpt = substr(strip_tags($input['content']), 0, 200);
        }
        
        // Insert new news
        $publishedAt = !empty($input['published_at']) ? $input['published_at'] : date('Y-m-d H:i:s');
        $id = $db->insert(
            "INSERT INTO news (title, content, excerpt, image, author, published_at, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())",
            [
                trim($input['title']),
                $input['content'] ?? null,
                $excerpt,
                $input['image'] ?? null,
                $input['author'] ?? null,
                $publishedAt
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to create news');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'News created successfully']);
    } catch (Exception $e) {
        error_log("News API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create news: ' . $e->getMessage()]);
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
        
        // Generate excerpt if not provided
        $excerpt = $input['excerpt'] ?? null;
        if (!$excerpt && !empty($input['content'])) {
            $excerpt = substr(strip_tags($input['content']), 0, 200);
        }
        
        // Update news
        $db->update(
            "UPDATE news SET title = ?, content = ?, excerpt = ?, image = ?, author = ?, published_at = ?, updated_at = NOW() WHERE id = ?",
            [
                trim($input['title']),
                $input['content'] ?? null,
                $excerpt,
                $input['image'] ?? null,
                $input['author'] ?? null,
                $input['published_at'] ?? null,
                $input['id']
            ]
        );
        
        echo json_encode(['success' => true, 'message' => 'News updated successfully']);
    } catch (Exception $e) {
        error_log("News API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update news: ' . $e->getMessage()]);
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
        $db->update("UPDATE news SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        
        echo json_encode(['success' => true, 'message' => 'News deleted successfully']);
    } catch (Exception $e) {
        error_log("News API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete news: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
