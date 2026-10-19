<?php
/**
 * Media List API
 */
// Start output buffering to catch any unexpected output
ob_start();

// Suppress error display for API endpoints (errors will be logged but not displayed)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Clear any output that may have been generated
ob_clean();

// Set JSON header
header('Content-Type: application/json');

$auth = getAuth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();

try {
    $media = $db->fetchAll("SELECT m.*, ms.name as section_name, ms.color as section_color 
                           FROM media m 
                           LEFT JOIN media_sections ms ON m.section_id = ms.id 
                           ORDER BY m.created_at DESC 
                           LIMIT 100");
    
    // Format media items
    $formatted = [];
    $uploadUrl = defined('UPLOAD_URL') ? UPLOAD_URL : '/uploads/';
    
    foreach ($media as $item) {
        $imageUrl = $item['file_path'] ?? $item['file_url'] ?? '';
        
        // If no URL, try to construct from filename
        if (empty($imageUrl) && !empty($item['filename'])) {
            $imageUrl = $uploadUrl . $item['filename'];
        }
        
        // Handle folder paths (like "h/" or subdirectories)
        if (!empty($imageUrl)) {
            // Normalize path separators
            $imageUrl = str_replace('\\', '/', $imageUrl);
            
            // If it's a relative path without leading slash, add upload URL
            if (!empty($item['filename']) && !str_starts_with($imageUrl, '/') && !str_starts_with($imageUrl, 'http')) {
                // Check if filename contains folder structure (e.g., "h/image.jpg")
                if (strpos($item['filename'], '/') !== false || strpos($item['filename'], '\\') !== false) {
                    $imageUrl = $uploadUrl . str_replace('\\', '/', $item['filename']);
                } else {
                    $imageUrl = $uploadUrl . $item['filename'];
                }
            }
            
            // Fix paths that start with /uploads/ but need base path
            if (substr($imageUrl, 0, 9) === '/uploads/' && $uploadUrl !== '/uploads/') {
                $imageUrl = $uploadUrl . substr($imageUrl, 9);
            }
            
            // Ensure absolute paths for relative URLs
            if (!str_starts_with($imageUrl, 'http') && !str_starts_with($imageUrl, 'data:') && !str_starts_with($imageUrl, '/')) {
                $imageUrl = '/' . ltrim($imageUrl, '/');
            }
        }
        
        $formatted[] = [
            'id' => $item['id'],
            'filename' => $item['filename'] ?? '',
            'original_name' => $item['original_name'] ?? '',
            'file_path' => $imageUrl,
            'file_url' => $imageUrl,
            'file_size' => $item['file_size'] ?? 0,
            'section_id' => $item['section_id'] ?? null,
            'section_name' => $item['section_name'] ?? null,
            'section_color' => $item['section_color'] ?? null,
        ];
    }
    
    echo json_encode(['success' => true, 'media' => $formatted], JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// Clean output buffer and send response
ob_end_flush();
?>
