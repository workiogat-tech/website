<?php
/**
 * Facilities API - Create, Read, Update, Delete
 */
// CRITICAL: Disable ALL output before anything else
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Start output buffering IMMEDIATELY - before any includes
ob_start();

// Set error handler to suppress all output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Log but never display
    error_log("PHP Error in facilities.php [$errno]: $errstr in $errfile:$errline");
    // Suppress all error output
    return true; // Return true to suppress default error handler
}, E_ALL);

// Catch fatal errors and output JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Fatal error: ' . $error['message']]);
        exit;
    }
    
    // Check if there's any unexpected output
    $output = ob_get_contents();
    if (!empty($output) && trim($output) !== '') {
        // If output doesn't look like JSON, log it and return error
        $trimmed = trim($output);
        if (!preg_match('/^[\s\n\r]*[\{\[]/', $trimmed)) {
            error_log("Unexpected non-JSON output in facilities.php: " . substr($output, 0, 500));
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
            exit;
        }
    }
});

// Include config - it will call session_start, but we've suppressed errors
require_once __DIR__ . '/../../config/config.php';

// Re-disable error display after config.php (in case it re-enabled it)
ini_set('display_errors', 0);

// Clear any output that might have been generated (from config.php, session_start, etc.)
$output = ob_get_clean();
if (!empty($output) && trim($output) !== '') {
    // Check if it's HTML (error page) or just whitespace
    $trimmed = trim($output);
    if (preg_match('/<html|<body|<div|<p|<br/i', $trimmed) || strlen($trimmed) > 50) {
        // It's HTML - log it and return error
        error_log("HTML output detected in facilities.php: " . substr($output, 0, 500));
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Server returned HTML instead of JSON. Check PHP error logs.']);
        exit;
    } else {
        // Just whitespace or minor output - log and continue
        error_log("Minor output before JSON in facilities.php: " . substr($output, 0, 100));
    }
}
ob_start(); // Start fresh buffer for JSON output

// Set JSON header
header('Content-Type: application/json');

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
} catch (Exception $e) {
    $output = ob_get_clean();
    if (!empty($output) && trim($output) !== '') {
        error_log("Unexpected output in database connection error: " . substr($output, 0, 200));
    }
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

// Check and add missing columns (capacity and label)
try {
    $columns = $db->fetchAll("SHOW COLUMNS FROM facilities");
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('capacity', $columnNames)) {
        $db->query("ALTER TABLE facilities ADD COLUMN capacity INT DEFAULT NULL");
    }
    if (!in_array('label', $columnNames)) {
        $db->query("ALTER TABLE facilities ADD COLUMN label VARCHAR(200) DEFAULT NULL");
    }
} catch (Exception $e) {
    error_log("Facilities table column check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Get single facility
            $facility = $db->fetchOne("SELECT * FROM facilities WHERE id = ? AND is_active = 1", [$id]);
            if ($facility) {
                $output = ob_get_clean();
                if (!empty($output) && trim($output) !== '') {
                    error_log("Unexpected output before JSON response in facilities.php: " . substr($output, 0, 200));
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'data' => $facility]);
                exit;
            } else {
                $output = ob_get_clean();
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Facility not found']);
                exit;
            }
        } else {
            // Get all facilities
            $facilities = $db->fetchAll("SELECT * FROM facilities WHERE is_active = 1 ORDER BY created_at DESC");
            $output = ob_get_clean();
            if (!empty($output) && trim($output) !== '') {
                error_log("Unexpected output before JSON response in facilities.php: " . substr($output, 0, 200));
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $facilities]);
            exit;
        }
    } catch (Exception $e) {
        error_log("Facilities GET error: " . $e->getMessage());
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output in error handler: " . substr($output, 0, 200));
        }
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to fetch facilities: ' . $e->getMessage()]);
        exit;
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
        
        // Insert new facility
        $id = $db->insert(
            "INSERT INTO facilities (name, description, image, capacity, label, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())",
            [
                trim($input['name']),
                $input['description'] ?? null,
                $input['image'] ?? null,
                !empty($input['capacity']) ? intval($input['capacity']) : null,
                !empty($input['label']) ? trim($input['label']) : null
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to create facility');
        }
        
        // Ensure we output valid JSON - clear any previous output
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output before JSON response in facilities.php: " . substr($output, 0, 200));
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Facility created successfully']);
        exit;
    } catch (Exception $e) {
        error_log("Facilities API Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output in error handler: " . substr($output, 0, 200));
        }
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to create facility: ' . $e->getMessage()]);
        exit;
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
        
        // Update facility
        $db->update(
            "UPDATE facilities SET name = ?, description = ?, image = ?, capacity = ?, label = ?, updated_at = NOW() WHERE id = ?",
            [
                trim($input['name']),
                $input['description'] ?? null,
                $input['image'] ?? null,
                !empty($input['capacity']) ? intval($input['capacity']) : null,
                !empty($input['label']) ? trim($input['label']) : null,
                $input['id']
            ]
        );
        
        // Ensure we output valid JSON - clear any previous output
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output before JSON response in facilities.php: " . substr($output, 0, 200));
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Facility updated successfully']);
        exit;
    } catch (Exception $e) {
        error_log("Facilities API Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output in error handler: " . substr($output, 0, 200));
        }
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to update facility: ' . $e->getMessage()]);
        exit;
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
        $db->update("UPDATE facilities SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        
        // Ensure we output valid JSON - clear any previous output
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output before JSON response in facilities.php: " . substr($output, 0, 200));
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Facility deleted successfully']);
        exit;
    } catch (Exception $e) {
        error_log("Facilities API Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output in error handler: " . substr($output, 0, 200));
        }
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to delete facility: ' . $e->getMessage()]);
        exit;
    }
} else {
    $output = ob_get_clean();
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
