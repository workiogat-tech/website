<?php
/**
 * Clubs Content API
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
        $content = $db->fetchOne(
            "SELECT * FROM clubs_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch clubs content: ' . $e->getMessage()]);
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
        
        // Ensure table has color columns
        try {
            $tableExists = $db->fetchOne("SHOW TABLES LIKE 'clubs_content'");
            if ($tableExists !== false && $tableExists !== null) {
                $columns = $db->fetchAll("SHOW COLUMNS FROM clubs_content");
                $columnNames = array_column($columns, 'Field');
                
                // Add title_color column if it doesn't exist
                if (!in_array('title_color', $columnNames)) {
                    $db->query("ALTER TABLE clubs_content ADD COLUMN title_color VARCHAR(7) DEFAULT '#ffffff'");
                }
                
                // Add subtitle_color column if it doesn't exist
                if (!in_array('subtitle_color', $columnNames)) {
                    $db->query("ALTER TABLE clubs_content ADD COLUMN subtitle_color VARCHAR(7) DEFAULT '#ffffff'");
                }
            }
        } catch (Exception $e) {
            error_log("Error ensuring clubs_content color columns: " . $e->getMessage());
            // Continue anyway - columns might already exist
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
        
        // Handle clubs array from form data
        $clubsArray = [];
        if (isset($input['clubs'])) {
            // If clubs is a JSON string, decode it
            if (is_string($input['clubs'])) {
                $decoded = json_decode($input['clubs'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $clubsArray = $decoded;
                }
            } elseif (is_array($input['clubs'])) {
                $clubsArray = $input['clubs'];
            }
        } elseif (isset($input['club_name']) && is_array($input['club_name'])) {
            // Build clubs array from form fields
            $clubNames = $input['club_name'] ?? [];
            $clubImages = $input['club_image'] ?? [];
            $clubDescriptions = $input['club_description'] ?? [];
            
            for ($i = 0; $i < count($clubNames); $i++) {
                $name = trim($clubNames[$i] ?? '');
                $image = trim($clubImages[$i] ?? '');
                $description = trim($clubDescriptions[$i] ?? '');
                
                if (!empty($name) || !empty($image) || !empty($description)) {
                    $clubsArray[] = [
                        'name' => $name,
                        'image' => $image,
                        'description' => $description
                    ];
                }
            }
        }
        
        // Allowed fields
        $allowedFields = ['title', 'subtitle', 'content', 'hero_image', 'title_color', 'subtitle_color', 'clubs'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        // Handle color fields - use text field value if color picker is empty
        if (empty($input['title_color']) && !empty($input['title_color_text'])) {
            $input['title_color'] = $input['title_color_text'];
        }
        if (empty($input['subtitle_color']) && !empty($input['subtitle_color_text'])) {
            $input['subtitle_color'] = $input['subtitle_color_text'];
        }
        
        foreach ($input as $key => $value) {
            // Skip color text fields (they're just for display/sync, use the actual color picker values)
            if ($key === 'title_color_text' || $key === 'subtitle_color_text') {
                continue;
            }
            
            if ($key !== 'id' && $key !== 'is_active' && $key !== 'club_name' && $key !== 'club_image' && $key !== 'club_description' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                if ($key === 'clubs') {
                    $values[] = json_encode($clubsArray);
                } else {
                    $values[] = ($value === '' || $value === null) ? null : $value;
                }
            }
        }
        
        // Add clubs field if not already added
        if (!in_array('clubs', $fields)) {
            $fields[] = 'clubs';
            $values[] = json_encode($clubsArray);
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM clubs_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE clubs_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO clubs_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save clubs content');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Clubs content saved successfully']);
    } catch (Exception $e) {
        error_log("Clubs Content API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save clubs content: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
$output = ob_get_clean();
$trimmed = trim($output);
if ($trimmed === '') {
    // Response was already sent.
} elseif ($trimmed[0] === '{' || $trimmed[0] === '[') {
    echo $output;
} else {
    error_log("Unexpected output in clubs.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
?>
