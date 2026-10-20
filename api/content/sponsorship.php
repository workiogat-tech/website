<?php
/**
 * Sponsorship Content API
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

// Ensure all required columns exist
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'sponsorship_content'");
    if ($tableExists) {
        $columns = $db->fetchAll("SHOW COLUMNS FROM sponsorship_content");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'cta_title' => 'VARCHAR(200)',
            'cta_subtitle' => 'TEXT',
            'apply_button_text' => 'VARCHAR(200)',
            'sponsor_button_text' => 'VARCHAR(200)',
            'sukrop_link' => 'VARCHAR(500)',
            'sponsorship_steps' => 'JSON',
            'sponsorship_levels' => 'JSON',
            'transformations_title' => 'VARCHAR(200)',
            'transformations_subtitle' => 'TEXT',
            'impact_level_title' => 'VARCHAR(200)',
            'impact_level_subtitle' => 'TEXT',
            'how_it_works_title' => 'VARCHAR(200)',
            'how_it_works_subtitle' => 'TEXT',
            'success_stories' => 'JSON',
            'coordinator_email' => 'VARCHAR(100)',
            'coordinator_phone' => 'VARCHAR(50)'
        ];
        
        foreach ($requiredColumns as $column => $type) {
            if (!in_array($column, $columnNames)) {
                $db->query("ALTER TABLE sponsorship_content ADD COLUMN $column $type");
            }
        }
    }
} catch (Exception $e) {
    error_log("Sponsorship table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM sponsorship_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch sponsorship content: ' . $e->getMessage()]);
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
        
        // Process sponsorship_steps array
        $sponsorshipSteps = [];
        if (isset($input['sponsorship_steps']) && is_array($input['sponsorship_steps'])) {
            $sponsorshipSteps = array_values($input['sponsorship_steps']);
        }
        $sponsorshipStepsJson = !empty($sponsorshipSteps) ? json_encode($sponsorshipSteps) : null;
        
        // Process sponsorship_levels array
        $sponsorshipLevels = [];
        if (isset($input['sponsorship_levels']) && is_array($input['sponsorship_levels'])) {
            foreach ($input['sponsorship_levels'] as $level) {
                $benefits = [];
                if (!empty($level['benefits_text'])) {
                    $benefits = array_filter(array_map('trim', explode("\n", $level['benefits_text'])));
                }
                $sponsorshipLevels[] = [
                    'name' => $level['name'] ?? '',
                    'amount' => $level['amount'] ?? '',
                    'description' => $level['description'] ?? '',
                    'benefits' => $benefits
                ];
            }
        }
        $sponsorshipLevelsJson = !empty($sponsorshipLevels) ? json_encode($sponsorshipLevels) : null;
        
        // Process success_stories array
        $successStories = [];
        if (isset($input['success_stories']) && is_array($input['success_stories'])) {
            foreach ($input['success_stories'] as $story) {
                $successStories[] = [
                    'name' => $story['name'] ?? '',
                    'age' => $story['age'] ?? '',
                    'before' => [
                        'image' => $story['before_image'] ?? '',
                        'description' => $story['before_description'] ?? ''
                    ],
                    'after' => [
                        'image' => $story['after_image'] ?? '',
                        'description' => $story['after_description'] ?? ''
                    ],
                    'story' => $story['story'] ?? ''
                ];
            }
        }
        $successStoriesJson = !empty($successStories) ? json_encode($successStories) : null;
        
        // Allowed fields
        $allowedFields = [
            'title', 'subtitle', 'description', 'hero_image', 'additional_slides',
            'cta_title', 'cta_subtitle', 'apply_button_text', 'sponsor_button_text',
            'sukrop_link', 'sponsorship_steps', 'sponsorship_levels',
            'transformations_title', 'transformations_subtitle',
            'impact_level_title', 'impact_level_subtitle',
            'how_it_works_title', 'how_it_works_subtitle',
            'success_stories',
            'coordinator_email', 'coordinator_phone'
        ];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key === 'additional_slides') {
                $fields[] = 'additional_slides';
                $values[] = $additionalSlidesJson;
            } elseif ($key === 'sponsorship_steps') {
                $fields[] = 'sponsorship_steps';
                $values[] = $sponsorshipStepsJson;
            } elseif ($key === 'sponsorship_levels') {
                $fields[] = 'sponsorship_levels';
                $values[] = $sponsorshipLevelsJson;
            } elseif ($key === 'success_stories') {
                $fields[] = 'success_stories';
                $values[] = $successStoriesJson;
            } elseif ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                $values[] = ($value === '' || $value === null) ? null : $value;
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM sponsorship_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE sponsorship_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO sponsorship_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save sponsorship content');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Sponsorship content saved successfully']);
    } catch (Exception $e) {
        error_log("Sponsorship Content API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save sponsorship content: ' . $e->getMessage()]);
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
    error_log("Unexpected output in sponsorship.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
?>
