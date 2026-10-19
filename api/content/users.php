<?php
/**
 * Users API - Create, Read, Update, Delete
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
        require_once __DIR__ . '/../../includes/auth.php';
        $auth = getAuth();
        if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $users = $db->fetchAll("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'users' => $users, 'data' => $users]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch users: ' . $e->getMessage()]);
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
        if (empty($input['username'])) {
            throw new Exception('Username is required');
        }
        if (empty($input['email'])) {
            throw new Exception('Email is required');
        }
        if (empty($input['password'])) {
            throw new Exception('Password is required');
        }
        
        // Validate email
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }
        
        // Check if user exists
        $existing = $db->fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [trim($input['username']), trim($input['email'])]);
        if ($existing) {
            throw new Exception('User with this username or email already exists');
        }
        
        // Hash password
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $role = $input['role'] ?? 'user';
        if (!in_array($role, ['user', 'admin'])) {
            $role = 'user';
        }
        
        $id = $db->insert(
            "INSERT INTO users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())",
            [
                trim($input['username']),
                trim($input['email']),
                $hashedPassword,
                $role
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to create user');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'User created successfully']);
    } catch (Exception $e) {
        error_log("Users API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create user: ' . $e->getMessage()]);
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
        
        // Update user
        $updates = [];
        $values = [];
        
        if (!empty($input['username'])) {
            $updates[] = "username = ?";
            $values[] = trim($input['username']);
        }
        if (!empty($input['email'])) {
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }
            $updates[] = "email = ?";
            $values[] = trim($input['email']);
        }
        if (!empty($input['password'])) {
            $updates[] = "password_hash = ?";
            $values[] = password_hash($input['password'], PASSWORD_DEFAULT);
        }
        if (isset($input['role'])) {
            $role = in_array($input['role'], ['user', 'admin']) ? $input['role'] : 'user';
            $updates[] = "role = ?";
            $values[] = $role;
        }
        
        if (empty($updates)) {
            throw new Exception('No fields to update');
        }
        
        $updates[] = "updated_at = NOW()";
        $values[] = $input['id'];
        
        $db->update("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?", $values);
        
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } catch (Exception $e) {
        error_log("Users API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update user: ' . $e->getMessage()]);
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
        
        // Don't allow deleting yourself
        $currentUser = $auth->getCurrentUser();
        if ($currentUser && $currentUser['id'] == $id) {
            throw new Exception('You cannot delete your own account');
        }
        
        // Hard delete (or soft delete if you prefer)
        $db->delete("DELETE FROM users WHERE id = ?", [$id]);
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (Exception $e) {
        error_log("Users API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete user: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
