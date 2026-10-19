<?php
/**
 * Homepage Sections API
 * Handles CRUD operations for all homepage sections
 */
// Start output buffering to catch any unexpected output
if (ob_get_level() == 0) {
    ob_start();
}

// Suppress error display for API endpoints (errors will be logged but not displayed)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Clear any output that may have been generated
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

// Set JSON header - before any output
if (!headers_sent()) {
    header('Content-Type: application/json');
}

$auth = getAuth();
$auth->requireAdmin();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Ensure we catch any errors and return proper JSON
    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
            }
            echo json_encode(['error' => 'Fatal error: ' . $error['message']]);
            exit;
        }
    });
    
    if ($method === 'POST') {
        // Handle both JSON and form data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }
            $sectionType = $input['section_type'] ?? '';
        } else {
            $input = $_POST;
            $sectionType = $_POST['section_type'] ?? '';
        }
        
        if ($sectionType === 'academics') {
            // Update or insert academics content
            $existing = $db->fetchOne("SELECT id FROM academics_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            
            $data = [
                'title' => $input['title'] ?? $_POST['title'] ?? '',
                'description' => $input['description'] ?? $_POST['description'] ?? '',
                'student_count' => $input['student_count'] ?? $_POST['student_count'] ?? '',
                'success_rate' => $input['success_rate'] ?? $_POST['success_rate'] ?? '',
                'motto' => $input['motto'] ?? $_POST['motto'] ?? '',
                'image' => $input['image'] ?? $_POST['image'] ?? '',
                'curriculum_link' => $input['curriculum_link'] ?? $_POST['curriculum_link'] ?? '',
                'button_text' => $input['button_text'] ?? $_POST['button_text'] ?? 'Learn More About Our Curriculum'
            ];
            
            // Ensure button_text column exists
            try {
                $columns = $db->fetchAll("SHOW COLUMNS FROM academics_content");
                $columnNames = array_column($columns, 'Field');
                if (!in_array('button_text', $columnNames)) {
                    $db->query("ALTER TABLE academics_content ADD COLUMN button_text VARCHAR(200) DEFAULT 'Learn More About Our Curriculum'");
                }
            } catch (Exception $e) {
                error_log("Academics button_text column check error: " . $e->getMessage());
            }
            
            if ($existing) {
                $db->update(
                    "UPDATE academics_content SET title = ?, description = ?, student_count = ?, success_rate = ?, motto = ?, image = ?, curriculum_link = ?, button_text = ?, updated_at = NOW() WHERE id = ?",
                    [$data['title'], $data['description'], $data['student_count'], $data['success_rate'], $data['motto'], $data['image'], $data['curriculum_link'], $data['button_text'], $existing['id']]
                );
            } else {
                $db->insert(
                    "INSERT INTO academics_content (title, description, student_count, success_rate, motto, image, curriculum_link, button_text, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
                    [$data['title'], $data['description'], $data['student_count'], $data['success_rate'], $data['motto'], $data['image'], $data['curriculum_link'], $data['button_text']]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Academics content saved successfully']);
            
        } elseif ($sectionType === 'sports') {
            // Update or insert sports content
            $existing = $db->fetchOne("SELECT id FROM sports_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            
            $data = [
                'title' => $input['title'] ?? $_POST['title'] ?? 'Sports',
                'description' => $input['description'] ?? $_POST['description'] ?? '',
                'image' => $input['image'] ?? $_POST['image'] ?? ''
            ];
            
            // Ensure table exists
            try {
                $tableExists = $db->fetchOne("SHOW TABLES LIKE 'sports_content'");
                if (!$tableExists) {
                    $db->query("CREATE TABLE IF NOT EXISTS sports_content (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(200),
                        description TEXT,
                        image VARCHAR(500),
                        is_active TINYINT(1) DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )");
                }
            } catch (Exception $e) {
                error_log("Sports content table check error: " . $e->getMessage());
            }
            
            if ($existing) {
                $db->update(
                    "UPDATE sports_content SET title = ?, description = ?, image = ?, updated_at = NOW() WHERE id = ?",
                    [$data['title'], $data['description'], $data['image'], $existing['id']]
                );
            } else {
                $db->insert(
                    "INSERT INTO sports_content (title, description, image, is_active) VALUES (?, ?, ?, 1)",
                    [$data['title'], $data['description'], $data['image']]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Sports content saved successfully']);
            
        } elseif ($sectionType === 'custom-section') {
            // Update or insert custom section content
            $existing = $db->fetchOne("SELECT id FROM custom_section_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            
            $data = [
                'title' => $input['title'] ?? $_POST['title'] ?? '',
                'description' => $input['description'] ?? $_POST['description'] ?? '',
                'image' => $input['image'] ?? $_POST['image'] ?? '',
                'button_text' => $input['button_text'] ?? $_POST['button_text'] ?? '',
                'button_link' => $input['button_link'] ?? $_POST['button_link'] ?? ''
            ];
            
            // Ensure table exists
            try {
                $tableExists = $db->fetchOne("SHOW TABLES LIKE 'custom_section_content'");
                if (!$tableExists) {
                    $db->query("CREATE TABLE IF NOT EXISTS custom_section_content (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(200),
                        description TEXT,
                        image VARCHAR(500),
                        button_text VARCHAR(200),
                        button_link VARCHAR(500),
                        is_active TINYINT(1) DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )");
                }
            } catch (Exception $e) {
                error_log("Custom section content table check error: " . $e->getMessage());
            }
            
            if ($existing) {
                $db->update(
                    "UPDATE custom_section_content SET title = ?, description = ?, image = ?, button_text = ?, button_link = ?, updated_at = NOW() WHERE id = ?",
                    [$data['title'], $data['description'], $data['image'], $data['button_text'], $data['button_link'], $existing['id']]
                );
            } else {
                $db->insert(
                    "INSERT INTO custom_section_content (title, description, image, button_text, button_link, is_active) VALUES (?, ?, ?, ?, ?, 1)",
                    [$data['title'], $data['description'], $data['image'], $data['button_text'], $data['button_link']]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Custom section content saved successfully']);
            
        } elseif ($sectionType === 'three-columns') {
            // Handle three-column sections - OPTIMIZED for speed
            try {
                // Set execution time limit for this operation (reduced from 30s)
                set_time_limit(10);
                
                // Ensure table exists (cache this check to avoid repeated queries)
                static $tableChecked = false;
                if (!$tableChecked) {
                    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'three_column_sections'");
                    if (!$tableExists) {
                        $db->query("CREATE TABLE IF NOT EXISTS three_column_sections (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            title VARCHAR(200) NOT NULL,
                            description TEXT,
                            image VARCHAR(500),
                            order_index INT DEFAULT 0,
                            is_active TINYINT(1) DEFAULT 1,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            INDEX idx_order (order_index)
                        )");
                    }
                    $tableChecked = true;
                }
                
                // Process sections first to validate data
                $sections = $input['sections'] ?? $_POST['sections'] ?? [];
                if (!is_array($sections) || empty($sections)) {
                    throw new Exception('No sections data provided');
                }
                
                // Prepare all data first
                $sectionsToSave = [];
                $sectionIds = [];
                foreach ($sections as $section) {
                    $title = trim($section['title'] ?? '');
                    $description = trim($section['description'] ?? '');
                    $image = trim($section['image'] ?? '');
                    $orderIndex = intval($section['order_index'] ?? 0);
                    $id = isset($section['id']) ? intval($section['id']) : null;
                    
                    if (empty($title)) continue;
                    
                    $sectionsToSave[] = [
                        'id' => $id,
                        'title' => $title,
                        'description' => $description,
                        'image' => $image,
                        'order_index' => $orderIndex
                    ];
                    if ($id) {
                        $sectionIds[] = $id;
                    }
                }
                
                if (empty($sectionsToSave)) {
                    throw new Exception('No valid sections to save');
                }
                
                // OPTIMIZED: Use transaction for atomic operations
                $db->beginTransaction();
                
                try {
                    // Only deactivate sections that are NOT in the current save list (more efficient)
                    if (!empty($sectionIds)) {
                        $placeholders = implode(',', array_fill(0, count($sectionIds), '?'));
                        $db->update(
                            "UPDATE three_column_sections SET is_active = 0 WHERE id NOT IN ($placeholders)",
                            $sectionIds
                        );
                    } else {
                        // If no existing IDs, deactivate all
                        $db->update("UPDATE three_column_sections SET is_active = 0");
                    }
                    
                    // Save all sections in batch
                    foreach ($sectionsToSave as $section) {
                        if ($section['id']) {
                            // Update existing - use single query with all fields
                            $db->update(
                                "UPDATE three_column_sections SET title = ?, description = ?, image = ?, order_index = ?, is_active = 1, updated_at = NOW() WHERE id = ?",
                                [$section['title'], $section['description'], $section['image'], $section['order_index'], $section['id']]
                            );
                        } else {
                            // Insert new
                            $db->insert(
                                "INSERT INTO three_column_sections (title, description, image, order_index, is_active) VALUES (?, ?, ?, ?, 1)",
                                [$section['title'], $section['description'], $section['image'], $section['order_index']]
                            );
                        }
                    }
                    
                    // Commit transaction
                    $db->commit();
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $db->rollback();
                    throw $e;
                }
                
                // Clear output buffer and send response immediately
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    header('Content-Type: application/json');
                }
                echo json_encode(['success' => true, 'message' => 'Three column sections saved successfully']);
                exit;
            } catch (Exception $e) {
                error_log("Three columns save error: " . $e->getMessage());
                // Ensure rollback if transaction was started
                try {
                    if ($db->inTransaction()) {
                        $db->rollback();
                    }
                } catch (Exception $rollbackError) {
                    error_log("Rollback error: " . $rollbackError->getMessage());
                }
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                }
                echo json_encode(['error' => 'Failed to save three column sections: ' . $e->getMessage()]);
                exit;
            }
            
        } elseif ($sectionType === 'vision-mission') {
            $existing = $db->fetchOne("SELECT id FROM vision_mission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            
            $data = [
                'vision_title' => $input['vision_title'] ?? $_POST['vision_title'] ?? 'OUR VISION',
                'vision_text' => $input['vision_text'] ?? $_POST['vision_text'] ?? '',
                'vision_icon_emoji' => $input['vision_icon_emoji'] ?? $_POST['vision_icon_emoji'] ?? '💡',
                'mission_title' => $input['mission_title'] ?? $_POST['mission_title'] ?? 'OUR MISSION',
                'mission_text' => $input['mission_text'] ?? $_POST['mission_text'] ?? '',
                'mission_icon_emoji' => $input['mission_icon_emoji'] ?? $_POST['mission_icon_emoji'] ?? '🎓',
                'core_values_title' => $input['core_values_title'] ?? $_POST['core_values_title'] ?? 'OUR CORE VALUES',
                'core_values_icon_emoji' => $input['core_values_icon_emoji'] ?? $_POST['core_values_icon_emoji'] ?? '👥'
            ];
            
            if ($existing) {
                $db->update(
                    "UPDATE vision_mission_content SET vision_title = ?, vision_text = ?, vision_icon_emoji = ?, mission_title = ?, mission_text = ?, mission_icon_emoji = ?, core_values_title = ?, core_values_icon_emoji = ?, updated_at = NOW() WHERE id = ?",
                    [$data['vision_title'], $data['vision_text'], $data['vision_icon_emoji'], $data['mission_title'], $data['mission_text'], $data['mission_icon_emoji'], $data['core_values_title'], $data['core_values_icon_emoji'], $existing['id']]
                );
            } else {
                $db->insert(
                    "INSERT INTO vision_mission_content (vision_title, vision_text, vision_icon_emoji, mission_title, mission_text, mission_icon_emoji, core_values_title, core_values_icon_emoji, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
                    [$data['vision_title'], $data['vision_text'], $data['vision_icon_emoji'], $data['mission_title'], $data['mission_text'], $data['mission_icon_emoji'], $data['core_values_title'], $data['core_values_icon_emoji']]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Vision/Mission content saved successfully']);
            
        } elseif ($sectionType === 'why-choose') {
            $existing = $db->fetchOne("SELECT id FROM why_choose_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            
            $title = trim($input['title'] ?? $_POST['title'] ?? '');
            $image = trim($input['image'] ?? $_POST['image'] ?? '');
            
            if ($existing) {
                $db->update(
                    "UPDATE why_choose_content SET title = ?, image = ?, updated_at = NOW() WHERE id = ?",
                    [$title, $image, $existing['id']]
                );
            } else {
                $db->insert(
                    "INSERT INTO why_choose_content (title, image, is_active) VALUES (?, ?, 1)",
                    [$title, $image]
                );
            }
            
            // Handle points
            $points = $input['points'] ?? [];
            if (is_array($points) && !empty($points)) {
                // Get max order index
                $maxOrder = $db->fetchOne("SELECT MAX(order_index) as max_order FROM why_choose_points WHERE is_active = 1");
                $orderIndex = ($maxOrder && isset($maxOrder['max_order'])) ? intval($maxOrder['max_order']) + 1 : 1;
                
                $updatedPointIds = [];
                
                foreach ($points as $pointKey => $point) {
                    // Handle both array format and object format
                    if (is_array($point)) {
                        $pointText = $point['point_text'] ?? $point;
                        $pointId = $point['id'] ?? null;
                    } else {
                        $pointText = $point;
                        $pointId = null;
                    }
                    
                    // Extract ID from key if it's numeric (existing point)
                    if (is_numeric($pointKey)) {
                        $pointId = intval($pointKey);
                    }
                    
                    if (empty(trim($pointText))) {
                        continue; // Skip empty points
                    }
                    
                    if ($pointId !== null && is_numeric($pointId) && $pointId > 0) {
                        // Update existing point
                        $db->update(
                            "UPDATE why_choose_points SET point_text = ?, updated_at = NOW() WHERE id = ? AND is_active = 1",
                            [trim($pointText), $pointId]
                        );
                        $updatedPointIds[] = $pointId;
                    } else {
                        // Insert new point
                        $db->insert(
                            "INSERT INTO why_choose_points (point_text, order_index, is_active) VALUES (?, ?, 1)",
                            [trim($pointText), $orderIndex]
                        );
                        $orderIndex++;
                    }
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
            } elseif (empty($points)) {
                // If points array is empty, deactivate all existing points
                $db->update("UPDATE why_choose_points SET is_active = 0 WHERE is_active = 1");
            }
            
            echo json_encode(['success' => true, 'message' => 'Why Choose content saved successfully']);
            
        } elseif ($sectionType === 'join-section') {
            $existing = $db->fetchOne("SELECT id FROM join_section_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            
            $data = [
                'title' => $input['title'] ?? $_POST['title'] ?? '',
                'description' => $input['description'] ?? $_POST['description'] ?? '',
                'contact_button_text' => $input['contact_button_text'] ?? $_POST['contact_button_text'] ?? '',
                'contact_button_link' => $input['contact_button_link'] ?? $_POST['contact_button_link'] ?? '',
                'learn_more_button_text' => $input['learn_more_button_text'] ?? $_POST['learn_more_button_text'] ?? '',
                'learn_more_button_link' => $input['learn_more_button_link'] ?? $_POST['learn_more_button_link'] ?? ''
            ];
            
            if ($existing) {
                $db->update(
                    "UPDATE join_section_content SET title = ?, description = ?, contact_button_text = ?, contact_button_link = ?, learn_more_button_text = ?, learn_more_button_link = ?, updated_at = NOW() WHERE id = ?",
                    [$data['title'], $data['description'], $data['contact_button_text'], $data['contact_button_link'], $data['learn_more_button_text'], $data['learn_more_button_link'], $existing['id']]
                );
            } else {
                $db->insert(
                    "INSERT INTO join_section_content (title, description, contact_button_text, contact_button_link, learn_more_button_text, learn_more_button_link, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)",
                    [$data['title'], $data['description'], $data['contact_button_text'], $data['contact_button_link'], $data['learn_more_button_text'], $data['learn_more_button_link']]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Join section content saved successfully']);
        } elseif ($sectionType === 'school-life') {
            // Handle school life items
            $id = $input['id'] ?? $_POST['id'] ?? null;
            $title = trim($input['title'] ?? $_POST['title'] ?? '');
            $description = trim($input['description'] ?? $_POST['description'] ?? '');
            $linkUrl = trim($input['link_url'] ?? $_POST['link_url'] ?? '');
            $orderIndex = intval($input['order_index'] ?? $_POST['order_index'] ?? 0);
            
            if ($id && is_numeric($id)) {
                // Update existing item
                $db->update(
                    "UPDATE school_life_items SET title = ?, description = ?, link_url = ?, order_index = ?, updated_at = NOW() WHERE id = ? AND is_active = 1",
                    [$title, $description, $linkUrl, $orderIndex, $id]
                );
                echo json_encode(['success' => true, 'message' => 'School life item updated successfully']);
            } else {
                // Insert new item
                $maxOrder = $db->fetchOne("SELECT MAX(order_index) as max_order FROM school_life_items WHERE is_active = 1");
                $newOrderIndex = ($maxOrder && isset($maxOrder['max_order'])) ? intval($maxOrder['max_order']) + 1 : ($orderIndex > 0 ? $orderIndex : 1);
                
                $db->insert(
                    "INSERT INTO school_life_items (title, description, link_url, order_index, is_active) VALUES (?, ?, ?, ?, 1)",
                    [$title, $description, $linkUrl, $newOrderIndex]
                );
                echo json_encode(['success' => true, 'message' => 'School life item created successfully']);
            }
        } elseif ($sectionType === 'sports') {
            // Handle sports items
            $id = $input['id'] ?? $_POST['id'] ?? null;
            $name = trim($input['name'] ?? $_POST['name'] ?? '');
            $iconEmoji = trim($input['icon_emoji'] ?? $_POST['icon_emoji'] ?? '');
            $iconColor = trim($input['icon_color'] ?? $_POST['icon_color'] ?? '#000000');
            $orderIndex = intval($input['order_index'] ?? $_POST['order_index'] ?? 0);
            
            if ($id && is_numeric($id)) {
                // Update existing item
                $db->update(
                    "UPDATE sports_items SET name = ?, icon_emoji = ?, icon_color = ?, order_index = ?, updated_at = NOW() WHERE id = ? AND is_active = 1",
                    [$name, $iconEmoji, $iconColor, $orderIndex, $id]
                );
                echo json_encode(['success' => true, 'message' => 'Sport updated successfully']);
            } else {
                // Insert new item
                $maxOrder = $db->fetchOne("SELECT MAX(order_index) as max_order FROM sports_items WHERE is_active = 1");
                $newOrderIndex = ($maxOrder && isset($maxOrder['max_order'])) ? intval($maxOrder['max_order']) + 1 : ($orderIndex > 0 ? $orderIndex : 1);
                
                $db->insert(
                    "INSERT INTO sports_items (name, icon_emoji, icon_color, order_index, is_active) VALUES (?, ?, ?, ?, 1)",
                    [$name, $iconEmoji, $iconColor, $newOrderIndex]
                );
                echo json_encode(['success' => true, 'message' => 'Sport created successfully']);
            }
        } elseif ($sectionType === 'core-values') {
            // Handle core values items
            $id = $input['id'] ?? $_POST['id'] ?? null;
            $valueName = trim($input['value_name'] ?? $_POST['value_name'] ?? '');
            $orderIndex = intval($input['order_index'] ?? $_POST['order_index'] ?? 0);
            
            if ($id && is_numeric($id)) {
                // Update existing item
                $db->update(
                    "UPDATE core_values_items SET value_name = ?, order_index = ?, updated_at = NOW() WHERE id = ? AND is_active = 1",
                    [$valueName, $orderIndex, $id]
                );
                echo json_encode(['success' => true, 'message' => 'Core value updated successfully']);
            } else {
                // Insert new item
                $maxOrder = $db->fetchOne("SELECT MAX(order_index) as max_order FROM core_values_items WHERE is_active = 1");
                $newOrderIndex = ($maxOrder && isset($maxOrder['max_order'])) ? intval($maxOrder['max_order']) + 1 : ($orderIndex > 0 ? $orderIndex : 1);
                
                $db->insert(
                    "INSERT INTO core_values_items (value_name, order_index, is_active) VALUES (?, ?, 1)",
                    [$valueName, $newOrderIndex]
                );
                echo json_encode(['success' => true, 'message' => 'Core value created successfully']);
            }
        }
        
    } elseif ($method === 'GET') {
        // Handle GET requests for fetching single items
        $type = $_GET['type'] ?? '';
        $id = $_GET['id'] ?? 0;
        
        if ($type === 'school-life' && $id) {
            $item = $db->fetchOne("SELECT * FROM school_life_items WHERE id = ? AND is_active = 1", [$id]);
            echo json_encode(['success' => true, 'data' => $item]);
        } elseif ($type === 'sports' && $id) {
            $item = $db->fetchOne("SELECT * FROM sports_items WHERE id = ? AND is_active = 1", [$id]);
            echo json_encode(['success' => true, 'data' => $item]);
        } elseif ($type === 'core-values' && $id) {
            $item = $db->fetchOne("SELECT * FROM core_values_items WHERE id = ? AND is_active = 1", [$id]);
            echo json_encode(['success' => true, 'data' => $item]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
        }
        
    } elseif ($method === 'DELETE') {
        $type = $_GET['type'] ?? '';
        $id = $_GET['id'] ?? 0;
        
        if ($type === 'school-life') {
            $db->update("UPDATE school_life_items SET is_active = 0 WHERE id = ?", [$id]);
        } elseif ($type === 'sports') {
            $db->update("UPDATE sports_items SET is_active = 0 WHERE id = ?", [$id]);
        } elseif ($type === 'core-values') {
            $db->update("UPDATE core_values_items SET is_active = 0 WHERE id = ?", [$id]);
        } elseif ($type === 'why-choose-points') {
            $db->update("UPDATE why_choose_points SET is_active = 0 WHERE id = ?", [$id]);
        }
        
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
        exit;
    } else {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(405);
            header('Content-Type: application/json');
        }
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
} catch (Exception $e) {
    error_log("Homepage sections API error: " . $e->getMessage());
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
