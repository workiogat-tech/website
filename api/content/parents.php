<?php
/**
 * Parents Portal Content API
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

// Ensure resource_detail_sections column exists
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'parents_portal_content'");
    if ($tableExists) {
        $columns = $db->fetchAll("SHOW COLUMNS FROM parents_portal_content");
        $columnNames = array_column($columns, 'Field');
        
        if (!in_array('resource_detail_sections', $columnNames)) {
            $db->query("ALTER TABLE parents_portal_content ADD COLUMN resource_detail_sections TEXT NULL");
        }
    }
} catch (Exception $e) {
    error_log("Schema check for resource_detail_sections: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM parents_portal_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch parents portal content: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST' || $method === 'PUT') {
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
        
        if (empty($input)) {
            throw new Exception('No data provided');
        }
        
        // Process additional_slides array
        $additionalSlides = [];
        if (isset($input['additional_slides']) && is_array($input['additional_slides'])) {
            $additionalSlides = array_values($input['additional_slides']);
        }
        $additionalSlidesJson = !empty($additionalSlides) ? json_encode($additionalSlides) : null;
        
        // Process resource_detail_sections
        $resourceDetailSections = [];
        if (isset($input['resource_detail_sections'])) {
            if (is_string($input['resource_detail_sections'])) {
                $decoded = json_decode($input['resource_detail_sections'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $resourceDetailSections = $decoded;
                }
            } elseif (is_array($input['resource_detail_sections'])) {
                $resourceDetailSections = $input['resource_detail_sections'];
            }
        } else {
            // Try to build from array inputs
            $titles = $input['resource_section_title'] ?? [];
            $images = $input['resource_section_image'] ?? [];
            $descriptions = $input['resource_section_description'] ?? [];
            
            if (is_array($titles) && count($titles) > 0) {
                $maxCount = max(count($titles), count($images), count($descriptions));
                for ($i = 0; $i < $maxCount; $i++) {
                    $title = trim($titles[$i] ?? '');
                    $image = trim($images[$i] ?? '');
                    $description = trim($descriptions[$i] ?? '');
                    
                    if (!empty($title) || !empty($image) || !empty($description)) {
                        $resourceDetailSections[] = [
                            'title' => $title,
                            'image' => $image,
                            'description' => $description
                        ];
                    }
                }
            }
        }
        $resourceDetailSectionsJson = !empty($resourceDetailSections) ? json_encode($resourceDetailSections) : null;
        
        // Allowed fields
        $allowedFields = ['title', 'subtitle', 'description', 'hero_image', 'additional_slides', 'resource_detail_sections'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key === 'additional_slides') {
                $fields[] = 'additional_slides';
                $values[] = $additionalSlidesJson;
            } elseif (in_array($key, ['resource_section_title', 'resource_section_image', 'resource_section_description'])) {
                // Skip the individual array inputs as we've already processed them into resource_detail_sections
                continue;
            } elseif ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                $values[] = ($value === '' || $value === null) ? null : $value;
            }
        }
        
        // Ensure resource_detail_sections is added if we have data
        if (!in_array('resource_detail_sections', $fields) && !empty($resourceDetailSectionsJson)) {
            $fields[] = 'resource_detail_sections';
            $values[] = $resourceDetailSectionsJson;
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM parents_portal_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE parents_portal_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO parents_portal_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save parents portal content');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Parents portal content saved successfully']);
    } catch (Exception $e) {
        error_log("Parents Portal Content API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save parents portal content: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
$output = ob_get_clean();
if (!empty($output) && trim($output) !== '') {
    // If there's unexpected output, log it and return error
    error_log("Unexpected output in parents.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
    }
} else {
    // No unexpected output, response was already sent
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}
?>
