<?php
/**
 * Navigation Menu API
 * Handles CRUD operations for navigation menu items
 */
header('Content-Type: application/json');
ob_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Check authentication for POST, PUT, DELETE
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
    // Get single item or all items
    $id = $_GET['id'] ?? null;
    
    try {
        if ($id) {
            $item = $db->fetchOne("SELECT * FROM navigation_items WHERE id = ?", [$id]);
            if ($item) {
                echo json_encode($item);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Navigation item not found']);
            }
        } else {
            $items = $db->fetchAll("SELECT * FROM navigation_items WHERE is_active = 1 ORDER BY parent_id ASC, order_index ASC");
            echo json_encode($items);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch navigation items: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    // Create or update navigation item
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $id = $input['id'] ?? null;
        $label = trim($input['label'] ?? '');
        $url = trim($input['url'] ?? '');
        $parentId = !empty($input['parent_id']) ? intval($input['parent_id']) : null;
        $icon = trim($input['icon'] ?? '');
        $orderIndex = intval($input['order_index'] ?? 0);
        
        if (empty($label)) {
            throw new Exception('Label is required');
        }
        
        // Validate parent_id exists if provided
        if ($parentId) {
            $parent = $db->fetchOne("SELECT id FROM navigation_items WHERE id = ? AND is_active = 1", [$parentId]);
            if (!$parent) {
                throw new Exception('Parent menu item does not exist');
            }
        }
        
        if ($id) {
            // Update existing item
            $db->update(
                "UPDATE navigation_items SET label = ?, url = ?, parent_id = ?, icon = ?, order_index = ?, updated_at = NOW() WHERE id = ?",
                [$label, $url, $parentId, $icon, $orderIndex, $id]
            );
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Navigation item updated successfully']);
        } else {
            // Create new item
            $newId = $db->insert(
                "INSERT INTO navigation_items (label, url, parent_id, icon, order_index, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())",
                [$label, $url, $parentId, $icon, $orderIndex]
            );
            if (!$newId) {
                throw new Exception('Failed to create navigation item');
            }
            echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Navigation item created successfully']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save navigation item: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    // Delete navigation item (soft delete)
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_GET;
        }
        
        $id = $input['id'] ?? null;
        
        if (!$id) {
            throw new Exception('ID is required');
        }
        
        // Soft delete (set is_active = 0) and also delete children
        $db->update("UPDATE navigation_items SET is_active = 0, updated_at = NOW() WHERE id = ? OR parent_id = ?", [$id, $id]);
        
        echo json_encode(['success' => true, 'message' => 'Navigation item deleted successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete navigation item: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

ob_end_flush();
?>
