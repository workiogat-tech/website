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
require_once __DIR__ . '/../../includes/storage.php';

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

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'DELETE' || $method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        $body = [];
    }
    if ($method === 'POST' && ($body['action'] ?? '') !== 'delete') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Unknown media action.']);
        ob_end_flush();
        exit;
    }

    $id = $body['id'] ?? null;
    $url = trim((string) ($body['file_url'] ?? $body['file_path'] ?? ''));
    $row = null;
    if ($id !== null && $id !== '' && is_numeric($id)) {
        $row = $db->fetchOne("SELECT id, filename, file_path FROM media WHERE id = ?", [(int) $id]);
    }
    $target = $url;
    if ($row) {
        $target = $row['file_path'] ?: ($row['filename'] ?: $url);
    }
    if ($target === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Choose a picture to delete.']);
        ob_end_flush();
        exit;
    }

    try {
        removeSavedImage($db, $target);
        $media = $db->fetchAll("SELECT id, filename, file_path FROM media");
        foreach ($media as $item) {
            $matchesId = $row && (string) $item['id'] === (string) $row['id'];
            $matchesFile = sameImage((string) ($item['file_path'] ?? ''), $target)
                || sameImage((string) ($item['filename'] ?? ''), $target);
            if ($matchesId || $matchesFile) {
                $db->delete("DELETE FROM media WHERE id = ?", [$item['id']]);
            }
        }
        deleteStoredFile($target);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        ob_end_flush();
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Picture deleted.']);
    ob_end_flush();
    exit;
}

try {
    try {
        $db->query("SET statement_timeout = '8s'");
    } catch (Exception $timeoutError) {
        error_log('Media list timeout was not set: ' . $timeoutError->getMessage());
    }

    try {
        $media = $db->fetchAll("SELECT m.id, m.filename, m.original_name, m.file_path, m.file_size, m.section_id,
                                       ms.name as section_name, ms.color as section_color
                                FROM media m
                                LEFT JOIN media_sections ms ON m.section_id = ms.id
                                ORDER BY m.id DESC
                                LIMIT 200");
    } catch (Exception $queryError) {
        error_log('Media join skipped: ' . $queryError->getMessage());
        $media = $db->fetchAll("SELECT id, filename, original_name, file_path, file_size, section_id
                                FROM media
                                ORDER BY id DESC
                                LIMIT 200");
    }
    
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

    $known = [];
    foreach ($formatted as $item) {
        $known[$item['filename']] = true;
        $known[basename(parse_url($item['file_url'], PHP_URL_PATH) ?: '')] = true;
    }

    if (count($formatted) === 0) {
        try {
            foreach (listSupabaseImagesPage() as $object) {
                $base = basename($object['name']);
                if (isset($known[$object['name']]) || isset($known[$base]) || isset($known[$object['url']])) {
                    continue;
                }
                $known[$base] = true;
                $formatted[] = [
                    'id' => null,
                    'filename' => $base,
                    'original_name' => $base,
                    'file_path' => $object['url'],
                    'file_url' => $object['url'],
                    'file_size' => $object['size'],
                    'section_id' => null,
                    'section_name' => null,
                    'section_color' => null,
                ];
            }
        } catch (Exception $storageError) {
            error_log('Storage list failed: ' . $storageError->getMessage());
        }
    }
    
    echo json_encode(['success' => true, 'media' => $formatted], JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// Clean output buffer and send response
ob_end_flush();
?>
