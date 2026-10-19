<?php
/**
 * Public API - Header Data
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';

try {
    $db = getDB();
    $branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null;
    $contactInfo = $db->fetchOne("SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null;
    
    // Load navigation
    $navItems = [];
    $navChildren = [];
    try {
        $allNavItems = $db->fetchAll("SELECT * FROM navigation_items WHERE is_active = 1 ORDER BY parent_id ASC, order_index ASC");
        foreach ($allNavItems as $item) {
            if (empty($item['parent_id'])) {
                $navItems[] = $item;
            } else {
                if (!isset($navChildren[$item['parent_id']])) {
                    $navChildren[$item['parent_id']] = [];
                }
                $navChildren[$item['parent_id']][] = $item;
            }
        }
    } catch (Exception $e) {
        error_log("Navigation error: " . $e->getMessage());
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => [
            'branding' => $branding,
            'contactInfo' => $contactInfo,
            'navigation' => $navItems,
            'navigationChildren' => $navChildren
        ]
    ]);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
