<?php
/**
 * Footer Quick Links API
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
        $links = $db->fetchAll("SELECT * FROM footer_quick_links WHERE is_active = 1 ORDER BY order_index ASC");
        echo json_encode($links);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch footer links: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;
        
        $id = $input['id'] ?? null;
        $label = trim($input['label'] ?? '');
        $url = trim($input['url'] ?? '');
        $section = trim($input['section'] ?? 'quick-links');
        $orderIndex = intval($input['order_index'] ?? 0);
        
        if (empty($label)) {
            throw new Exception('Label is required');
        }
        
        if ($id) {
            $db->update(
                "UPDATE footer_quick_links SET label = ?, url = ?, section = ?, order_index = ?, updated_at = NOW() WHERE id = ?",
                [$label, $url, $section, $orderIndex, $id]
            );
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Footer link updated successfully']);
        } else {
            $newId = $db->insert(
                "INSERT INTO footer_quick_links (label, url, section, order_index, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())",
                [$label, $url, $section, $orderIndex]
            );
            if (!$newId) {
                throw new Exception('Failed to create footer link');
            }
            echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Footer link created successfully']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save footer link: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_GET;
        
        $id = $input['id'] ?? null;
        if (!$id) {
            throw new Exception('ID is required');
        }
        
        $db->update("UPDATE footer_quick_links SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        echo json_encode(['success' => true, 'message' => 'Footer link deleted successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete footer link: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

ob_end_flush();
?>
