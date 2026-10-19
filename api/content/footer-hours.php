<?php
/**
 * Footer Hours API
 */
header('Content-Type: application/json');
ob_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    $auth = getAuth();
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        ob_end_flush();
        exit;
    }
}

if ($method === 'GET') {
    try {
        $hours = $db->fetchAll("SELECT * FROM footer_hours WHERE is_active = 1 ORDER BY order_index ASC");
        echo json_encode($hours);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch footer hours: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;
        
        $id = $input['id'] ?? null;
        $category = trim($input['category'] ?? '');
        $dayRange = trim($input['day_range'] ?? '');
        $hours = trim($input['hours'] ?? '');
        $orderIndex = intval($input['order_index'] ?? 0);
        
        if (empty($category) || empty($hours)) {
            throw new Exception('Category and hours are required');
        }
        
        if ($id) {
            $db->update(
                "UPDATE footer_hours SET category = ?, day_range = ?, hours = ?, order_index = ?, updated_at = NOW() WHERE id = ?",
                [$category, $dayRange, $hours, $orderIndex, $id]
            );
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Footer hours updated successfully']);
        } else {
            $newId = $db->insert(
                "INSERT INTO footer_hours (category, day_range, hours, order_index, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())",
                [$category, $dayRange, $hours, $orderIndex]
            );
            if (!$newId) {
                throw new Exception('Failed to create footer hours');
            }
            echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Footer hours created successfully']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save footer hours: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_GET;
        
        $id = $input['id'] ?? null;
        if (!$id) {
            throw new Exception('ID is required');
        }
        
        $db->update("UPDATE footer_hours SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        echo json_encode(['success' => true, 'message' => 'Footer hours deleted successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete footer hours: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

ob_end_flush();
?>
