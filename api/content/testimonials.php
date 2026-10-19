<?php
/**
 * Testimonials API - Create, Read, Update, Delete
 */
// Start output buffering to catch any unexpected output
ob_start();

// Suppress error display for API endpoints (errors will be logged but not displayed)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header early - MUST be before any output
header('Content-Type: application/json; charset=utf-8');

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
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    try {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Get single testimonial
            $testimonial = $db->fetchOne("SELECT * FROM testimonials WHERE id = ? AND is_active = 1", [$id]);
            if ($testimonial) {
                echo json_encode(['success' => true, 'data' => $testimonial]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Testimonial not found']);
            }
        } else {
            // Get all testimonials
            $testimonials = $db->fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC");
            echo json_encode(['success' => true, 'data' => $testimonials]);
        }
    } catch (Exception $e) {
        error_log("Testimonials GET error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch testimonials: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    try {
        // Clear any output before processing
        if (ob_get_level()) {
            ob_clean();
        }
        
        require_once __DIR__ . '/../../includes/auth.php';
        $auth = getAuth();
        if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
            // Clear output before sending error
            if (ob_get_level()) {
                ob_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        // Handle both JSON and form data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }
        } else {
            $input = $_POST;
        }
        
        // Validate required fields
        if (empty($input['name']) || trim($input['name']) === '') {
            throw new Exception('Name is required');
        }
        if (empty($input['content']) || trim($input['content']) === '') {
            throw new Exception('Content is required');
        }
        
        // Insert new testimonial
        $rating = isset($input['rating']) ? intval($input['rating']) : 5;
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }
        
        $id = $db->insert(
            "INSERT INTO testimonials (name, position, content, image, rating, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())",
            [
                trim($input['name']),
                !empty($input['position']) ? trim($input['position']) : null,
                trim($input['content']),
                !empty($input['image']) ? trim($input['image']) : null,
                $rating
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to create testimonial');
        }
        
        // Clear output before sending success
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Testimonial created successfully']);
    } catch (Exception $e) {
        error_log("Testimonials API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        // Clear output before sending error
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create testimonial: ' . $e->getMessage()]);
    } catch (Throwable $e) {
        error_log("Testimonials API Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        // Clear output before sending error
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
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
        
        $rating = isset($input['rating']) ? intval($input['rating']) : 5;
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }
        
        // Update testimonial
        $db->update(
            "UPDATE testimonials SET name = ?, position = ?, content = ?, image = ?, rating = ?, updated_at = NOW() WHERE id = ?",
            [
                trim($input['name']),
                $input['position'] ?? null,
                trim($input['content']),
                $input['image'] ?? null,
                $rating,
                $input['id']
            ]
        );
        
        echo json_encode(['success' => true, 'message' => 'Testimonial updated successfully']);
    } catch (Exception $e) {
        error_log("Testimonials API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update testimonial: ' . $e->getMessage()]);
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
        $db->update("UPDATE testimonials SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        
        echo json_encode(['success' => true, 'message' => 'Testimonial deleted successfully']);
    } catch (Exception $e) {
        error_log("Testimonials API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete testimonial: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
} catch (Throwable $e) {
    // Catch any fatal errors or exceptions
    error_log("Testimonials API Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
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
