<?php
/**
 * About Content API
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

// Check if table exists, create if not
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'about_content'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS about_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title TEXT,
            content TEXT,
            image VARCHAR(500),
            hero_image VARCHAR(500),
            about_image_2 VARCHAR(500),
            hero_caption VARCHAR(200),
            mission_title VARCHAR(200),
            mission_content TEXT,
            mission_paragraph2 TEXT,
            core_values JSON,
            statistics JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Check if columns exist, if not add them
        $columns = $db->fetchAll("SHOW COLUMNS FROM about_content");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'hero_image' => "ALTER TABLE about_content ADD COLUMN hero_image VARCHAR(500) AFTER image",
            'about_image_2' => "ALTER TABLE about_content ADD COLUMN about_image_2 VARCHAR(500) AFTER hero_image",
            'hero_caption' => "ALTER TABLE about_content ADD COLUMN hero_caption VARCHAR(200) AFTER hero_image",
            'mission_title' => "ALTER TABLE about_content ADD COLUMN mission_title VARCHAR(200) AFTER content",
            'mission_content' => "ALTER TABLE about_content ADD COLUMN mission_content TEXT AFTER mission_title",
            'mission_paragraph2' => "ALTER TABLE about_content ADD COLUMN mission_paragraph2 TEXT AFTER mission_content",
            'core_values' => "ALTER TABLE about_content ADD COLUMN core_values JSON AFTER mission_paragraph2",
            'statistics' => "ALTER TABLE about_content ADD COLUMN statistics JSON AFTER core_values"
        ];
        
        foreach ($requiredColumns as $colName => $sql) {
            if (!in_array($colName, $columnNames)) {
                try {
                    $db->query($sql);
                    error_log("Added missing column '{$colName}' to about_content table");
                } catch (Exception $e) {
                    error_log("Failed to add column '{$colName}': " . $e->getMessage());
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("About content table/column check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM about_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        if (!$content) {
            $content = [];
        }

        try {
            $vision = $db->fetchOne("SELECT vision_text FROM vision_mission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            if ($vision && array_key_exists('vision_text', $vision)) {
                $content['vision_text'] = $vision['vision_text'] ?? '';
            }
        } catch (Exception $e) {
            error_log('About vision load skipped: ' . $e->getMessage());
        }

        try {
            $whyChoose = $db->fetchOne("SELECT title, image FROM why_choose_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            if ($whyChoose) {
                $content['why_choose_title'] = $whyChoose['title'] ?? '';
                $content['why_choose_image'] = $whyChoose['image'] ?? '';
            }
            $points = $db->fetchAll("SELECT id, point_text, order_index FROM why_choose_points WHERE is_active = 1 ORDER BY order_index ASC, id ASC");
            $content['why_choose_points'] = is_array($points) ? $points : [];
        } catch (Exception $e) {
            error_log('About why-choose load skipped: ' . $e->getMessage());
        }

        echo json_encode($content);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch about content: ' . $e->getMessage()]);
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
        
        // Process core_values and statistics arrays
        $coreValues = [];
        if (isset($input['core_values']) && is_array($input['core_values'])) {
            $coreValues = array_values($input['core_values']); // Re-index array
        }
        $coreValuesJson = !empty($coreValues) ? json_encode($coreValues) : null;
        
        $statistics = [];
        if (isset($input['statistics']) && is_array($input['statistics'])) {
            $statistics = array_values($input['statistics']); // Re-index array
        }
        $statisticsJson = !empty($statistics) ? json_encode($statistics) : null;
        
        // Allowed fields
        $allowedFields = ['hero_caption', 'title', 'content', 'image', 'hero_image', 'about_image_2', 'mission_title', 'mission_content', 'mission_paragraph2', 'core_values', 'statistics'];
        
        // Filter and validate input
        $fields = ['is_active'];
        $values = [1];
        
        foreach ($input as $key => $value) {
            if ($key === 'core_values') {
                $fields[] = 'core_values';
                $values[] = $coreValuesJson;
            } elseif ($key === 'statistics') {
                $fields[] = 'statistics';
                $values[] = $statisticsJson;
            } elseif ($key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                $values[] = ($value === '' || $value === null) ? null : $value;
            }
        }
        
        if (count($fields) <= 1) {
            throw new Exception('No valid fields to save');
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM about_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE about_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO about_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        $id = $db->insert($sql, $values);
        
        if (!$id) {
            throw new Exception('Failed to save about content');
        }
        
        // Handle Vision content
        if (isset($input['vision_text'])) {
            try {
                $visionText = trim($input['vision_text'] ?? '');
                $existingVision = $db->fetchOne("SELECT id FROM vision_mission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
                
                if ($existingVision) {
                    $db->update(
                        "UPDATE vision_mission_content SET vision_text = ?, updated_at = NOW() WHERE id = ?",
                        [$visionText, $existingVision['id']]
                    );
                } else {
                    // Create table if it doesn't exist
                    try {
                        $db->query("CREATE TABLE IF NOT EXISTS vision_mission_content (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            vision_title VARCHAR(200) DEFAULT 'OUR VISION',
                            vision_text TEXT,
                            vision_icon_emoji VARCHAR(10) DEFAULT '💡',
                            mission_title VARCHAR(200) DEFAULT 'OUR MISSION',
                            mission_text TEXT,
                            mission_icon_emoji VARCHAR(10) DEFAULT '🎓',
                            core_values_title VARCHAR(200) DEFAULT 'OUR CORE VALUES',
                            core_values_icon_emoji VARCHAR(10) DEFAULT '👥',
                            is_active BOOLEAN DEFAULT TRUE,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                    } catch (Exception $e) {
                        // Table might already exist
                    }
                    
                    $db->insert(
                        "INSERT INTO vision_mission_content (vision_text, is_active) VALUES (?, 1)",
                        [$visionText]
                    );
                }
            } catch (Exception $e) {
                error_log("Vision content save error: " . $e->getMessage());
            }
        }
        
        // Handle Why Choose content
        if (isset($input['why_choose_title']) || isset($input['why_choose_image']) || isset($input['why_choose_points'])) {
            try {
                // Create tables if they don't exist
                try {
                    $db->query("CREATE TABLE IF NOT EXISTS why_choose_content (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(200),
                        image VARCHAR(500),
                        is_active BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                    
                    $db->query("CREATE TABLE IF NOT EXISTS why_choose_points (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        point_text TEXT NOT NULL,
                        order_index INT DEFAULT 0,
                        is_active BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_order (order_index)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                } catch (Exception $e) {
                    // Tables might already exist
                }
                
                $whyChooseTitle = trim($input['why_choose_title'] ?? '');
                $whyChooseImage = trim($input['why_choose_image'] ?? '');
                $whyChoosePoints = $input['why_choose_points'] ?? [];
                
                $existingWhyChoose = $db->fetchOne("SELECT id FROM why_choose_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
                
                if ($existingWhyChoose) {
                    $db->update(
                        "UPDATE why_choose_content SET title = ?, image = ?, updated_at = NOW() WHERE id = ?",
                        [$whyChooseTitle, $whyChooseImage, $existingWhyChoose['id']]
                    );
                } else {
                    $db->insert(
                        "INSERT INTO why_choose_content (title, image, is_active) VALUES (?, ?, 1)",
                        [$whyChooseTitle, $whyChooseImage]
                    );
                }
                
                // Handle Why Choose Points
                if (is_array($whyChoosePoints) && !empty($whyChoosePoints)) {
                    $orderIndex = 0;
                    $updatedPointIds = [];
                    
                    foreach ($whyChoosePoints as $point) {
                        $pointText = trim($point['point_text'] ?? '');
                        $pointId = !empty($point['id']) ? intval($point['id']) : null;
                        
                        if (empty($pointText)) continue;
                        
                        if ($pointId) {
                            // Update existing point
                            $db->update(
                                "UPDATE why_choose_points SET point_text = ?, order_index = ?, updated_at = NOW() WHERE id = ? AND is_active = 1",
                                [$pointText, $orderIndex, $pointId]
                            );
                            $updatedPointIds[] = $pointId;
                        } else {
                            // Insert new point
                            $newPointId = $db->insert(
                                "INSERT INTO why_choose_points (point_text, order_index, is_active) VALUES (?, ?, 1)",
                                [$pointText, $orderIndex]
                            );
                            if ($newPointId) {
                                $updatedPointIds[] = $newPointId;
                            }
                        }
                        $orderIndex++;
                    }
                    
                    // Soft delete points that are no longer in the list
                    if (!empty($updatedPointIds)) {
                        $placeholders = implode(',', array_fill(0, count($updatedPointIds), '?'));
                        $db->update(
                            "UPDATE why_choose_points SET is_active = 0 WHERE id NOT IN ($placeholders) AND is_active = 1",
                            $updatedPointIds
                        );
                    } else {
                        // If no existing points were updated, deactivate all existing
                        $db->update("UPDATE why_choose_points SET is_active = 0 WHERE is_active = 1");
                    }
                } elseif (empty($whyChoosePoints)) {
                    // If points array is empty, deactivate all existing points
                    $db->update("UPDATE why_choose_points SET is_active = 0 WHERE is_active = 1");
                }
            } catch (Exception $e) {
                error_log("Why Choose content save error: " . $e->getMessage());
            }
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'About content saved successfully']);
    } catch (Exception $e) {
        error_log("About Content API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save about content: ' . $e->getMessage()]);
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
    error_log("Unexpected output in about.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
?>
