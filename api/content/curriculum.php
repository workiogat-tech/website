<?php
/**
 * Curriculum Content API
 */
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';

ob_clean();
header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Ensure tables exist and have correct columns
try {
    // Check if table exists
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'curriculum_content'");
    
    if (!$tableExists) {
        // Create table with new schema
        $db->query("CREATE TABLE curriculum_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_title VARCHAR(200),
            page_subtitle VARCHAR(200),
            intro_text TEXT,
            hero_image VARCHAR(500),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Table exists - check and add missing columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM curriculum_content");
        $columnNames = array_column($columns, 'Field');
        
        // Add page_title if it doesn't exist (might be named 'title')
        if (!in_array('page_title', $columnNames)) {
            if (in_array('title', $columnNames)) {
                // Rename title to page_title
                $db->query("ALTER TABLE curriculum_content CHANGE COLUMN title page_title VARCHAR(200)");
            } else {
                $db->query("ALTER TABLE curriculum_content ADD COLUMN page_title VARCHAR(200)");
            }
        }
        
        // Add page_subtitle if it doesn't exist (might be named 'subtitle')
        if (!in_array('page_subtitle', $columnNames)) {
            if (in_array('subtitle', $columnNames)) {
                // Rename subtitle to page_subtitle
                $db->query("ALTER TABLE curriculum_content CHANGE COLUMN subtitle page_subtitle VARCHAR(200)");
            } else {
                $db->query("ALTER TABLE curriculum_content ADD COLUMN page_subtitle VARCHAR(200)");
            }
        }
        
        // Add intro_text if it doesn't exist (might be named 'content')
        if (!in_array('intro_text', $columnNames)) {
            if (in_array('content', $columnNames)) {
                // Rename content to intro_text
                $db->query("ALTER TABLE curriculum_content CHANGE COLUMN content intro_text TEXT");
            } else {
                $db->query("ALTER TABLE curriculum_content ADD COLUMN intro_text TEXT");
            }
        }
        
        // Ensure hero_image exists
        if (!in_array('hero_image', $columnNames)) {
            $db->query("ALTER TABLE curriculum_content ADD COLUMN hero_image VARCHAR(500)");
        }
        
        // Ensure is_active exists
        if (!in_array('is_active', $columnNames)) {
            $db->query("ALTER TABLE curriculum_content ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
        }
    }
    
    
    // Create curriculum_programs table
    $programsTableExists = $db->fetchOne("SHOW TABLES LIKE 'curriculum_programs'");
    if (!$programsTableExists) {
        $db->query("CREATE TABLE curriculum_programs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            program_title VARCHAR(200),
            description TEXT,
            subjects TEXT,
            order_index INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Check and add missing columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM curriculum_programs");
        $columnNames = array_column($columns, 'Field');
        if (!in_array('program_title', $columnNames)) {
            $db->query("ALTER TABLE curriculum_programs ADD COLUMN program_title VARCHAR(200)");
        }
        if (!in_array('subjects', $columnNames)) {
            $db->query("ALTER TABLE curriculum_programs ADD COLUMN subjects TEXT");
        }
        if (!in_array('order_index', $columnNames)) {
            $db->query("ALTER TABLE curriculum_programs ADD COLUMN order_index INT DEFAULT 0");
        }
    }
    
    // Create curriculum_why_choose table
    $whyChooseTableExists = $db->fetchOne("SHOW TABLES LIKE 'curriculum_why_choose'");
    if (!$whyChooseTableExists) {
        $db->query("CREATE TABLE curriculum_why_choose (
            id INT AUTO_INCREMENT PRIMARY KEY,
            section_title VARCHAR(200),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    
    // Create curriculum_why_choose_items table
    $whyChooseItemsTableExists = $db->fetchOne("SHOW TABLES LIKE 'curriculum_why_choose_items'");
    if (!$whyChooseItemsTableExists) {
        $db->query("CREATE TABLE curriculum_why_choose_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            icon_name VARCHAR(100),
            title VARCHAR(200),
            description TEXT,
            order_index INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Check and add missing columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM curriculum_why_choose_items");
        $columnNames = array_column($columns, 'Field');
        if (!in_array('icon_name', $columnNames)) {
            $db->query("ALTER TABLE curriculum_why_choose_items ADD COLUMN icon_name VARCHAR(100)");
        }
        if (!in_array('order_index', $columnNames)) {
            $db->query("ALTER TABLE curriculum_why_choose_items ADD COLUMN order_index INT DEFAULT 0");
        }
    }
} catch (Exception $e) {
    error_log("Curriculum tables creation error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $content = $db->fetchOne("SELECT * FROM curriculum_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        if (!$content) $content = [];
        
        $programs = $db->fetchAll("SELECT * FROM curriculum_programs WHERE is_active = 1 ORDER BY order_index ASC");
        if (!$programs) $programs = [];
        
        $whyChoose = $db->fetchOne("SELECT * FROM curriculum_why_choose WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        if (!$whyChoose) $whyChoose = [];
        
        $whyChooseItems = $db->fetchAll("SELECT * FROM curriculum_why_choose_items WHERE is_active = 1 ORDER BY order_index ASC");
        if (!$whyChooseItems) $whyChooseItems = [];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'basicContent' => $content,
                'programs' => $programs,
                'whyChoose' => array_merge($whyChoose, ['items' => $whyChooseItems])
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch curriculum content: ' . $e->getMessage()]);
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
        
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }
        } else {
            $input = $_POST;
        }
        
        if (empty($input)) {
            throw new Exception('No data provided');
        }
        
        // Save basic content
        try {
            $existing = $db->fetchOne("SELECT id FROM curriculum_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            if ($existing !== false && is_array($existing) && isset($existing['id'])) {
                $db->update("UPDATE curriculum_content SET page_title = ?, page_subtitle = ?, intro_text = ?, hero_image = ?, updated_at = NOW() WHERE id = ?",
                    [$input['page_title'] ?? '', $input['page_subtitle'] ?? '', $input['intro_text'] ?? '', $input['hero_image'] ?? '', $existing['id']]);
            } else {
                $db->insert("INSERT INTO curriculum_content (page_title, page_subtitle, intro_text, hero_image, is_active) VALUES (?, ?, ?, ?, 1)",
                    [$input['page_title'] ?? '', $input['page_subtitle'] ?? '', $input['intro_text'] ?? '', $input['hero_image'] ?? '']);
            }
        } catch (Exception $e) {
            error_log("Error saving basic content: " . $e->getMessage());
            throw new Exception("Failed to save basic content: " . $e->getMessage());
        }
        
        // Save why choose section
        try {
            $whyChooseExisting = $db->fetchOne("SELECT id FROM curriculum_why_choose WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            if ($whyChooseExisting !== false && is_array($whyChooseExisting) && isset($whyChooseExisting['id'])) {
                $db->update("UPDATE curriculum_why_choose SET section_title = ?, updated_at = NOW() WHERE id = ?",
                    [$input['why_choose_title'] ?? '', $whyChooseExisting['id']]);
            } else {
                $db->insert("INSERT INTO curriculum_why_choose (section_title, is_active) VALUES (?, 1)",
                    [$input['why_choose_title'] ?? '']);
            }
        } catch (Exception $e) {
            error_log("Error saving why choose section: " . $e->getMessage());
            throw new Exception("Failed to save why choose section: " . $e->getMessage());
        }
        
        // Handle programs
        if (isset($input['programs']) && is_array($input['programs'])) {
            try {
                error_log("Processing " . count($input['programs']) . " programs");
                $updatedProgramIds = [];
                $orderIndex = 1;
                foreach ($input['programs'] as $index => $program) {
                    error_log("Program $index: " . json_encode($program));
                    
                    // Skip if completely empty
                    if (empty($program['program_title']) && empty($program['description']) && empty($program['subjects'])) {
                        error_log("Skipping empty program $index");
                        continue;
                    }
                    
                    if (isset($program['id']) && $program['id'] !== null && $program['id'] !== '' && is_numeric($program['id']) && intval($program['id']) > 0) {
                        // Update existing
                        error_log("Updating existing program ID: " . $program['id']);
                        $db->update("UPDATE curriculum_programs SET program_title = ?, description = ?, subjects = ?, order_index = ?, updated_at = NOW() WHERE id = ?",
                            [$program['program_title'] ?? '', $program['description'] ?? '', $program['subjects'] ?? '', $orderIndex, intval($program['id'])]);
                        $updatedProgramIds[] = intval($program['id']);
                    } else {
                        // Insert new
                        error_log("Inserting new program");
                        $newId = $db->insert("INSERT INTO curriculum_programs (program_title, description, subjects, order_index, is_active) VALUES (?, ?, ?, ?, 1)",
                            [$program['program_title'] ?? '', $program['description'] ?? '', $program['subjects'] ?? '', $orderIndex]);
                        error_log("New program inserted with ID: " . $newId);
                        if ($newId) {
                            $updatedProgramIds[] = $newId;
                        }
                    }
                    $orderIndex++;
                }
                
                // Soft delete removed programs - only if we have existing programs
                if (!empty($updatedProgramIds)) {
                    $placeholders = implode(',', array_fill(0, count($updatedProgramIds), '?'));
                    $db->update("UPDATE curriculum_programs SET is_active = 0 WHERE id NOT IN ($placeholders) AND is_active = 1", $updatedProgramIds);
                }
            } catch (Exception $e) {
                error_log("Error saving programs: " . $e->getMessage());
                throw new Exception("Failed to save programs: " . $e->getMessage());
            }
        }
        
        // Handle why choose items
        if (isset($input['why_choose_items']) && is_array($input['why_choose_items'])) {
            try {
                error_log("Processing " . count($input['why_choose_items']) . " why choose items");
                $updatedItemIds = [];
                $orderIndex = 1;
                foreach ($input['why_choose_items'] as $index => $item) {
                    error_log("Why Choose Item $index: " . json_encode($item));
                    
                    // Skip if completely empty
                    if (empty($item['title']) && empty($item['description']) && empty($item['icon_name'])) {
                        error_log("Skipping empty why choose item $index");
                        continue;
                    }
                    
                    if (isset($item['id']) && $item['id'] !== null && $item['id'] !== '' && is_numeric($item['id']) && intval($item['id']) > 0) {
                        // Update existing
                        error_log("Updating existing why choose item ID: " . $item['id']);
                        $db->update("UPDATE curriculum_why_choose_items SET icon_name = ?, title = ?, description = ?, order_index = ?, updated_at = NOW() WHERE id = ?",
                            [$item['icon_name'] ?? '', $item['title'] ?? '', $item['description'] ?? '', $orderIndex, intval($item['id'])]);
                        $updatedItemIds[] = intval($item['id']);
                    } else {
                        // Insert new
                        error_log("Inserting new why choose item");
                        $newId = $db->insert("INSERT INTO curriculum_why_choose_items (icon_name, title, description, order_index, is_active) VALUES (?, ?, ?, ?, 1)",
                            [$item['icon_name'] ?? '', $item['title'] ?? '', $item['description'] ?? '', $orderIndex]);
                        error_log("New why choose item inserted with ID: " . $newId);
                        if ($newId) {
                            $updatedItemIds[] = $newId;
                        }
                    }
                    $orderIndex++;
                }
                
                // Soft delete removed items - only if we have existing items
                if (!empty($updatedItemIds)) {
                    $placeholders = implode(',', array_fill(0, count($updatedItemIds), '?'));
                    $db->update("UPDATE curriculum_why_choose_items SET is_active = 0 WHERE id NOT IN ($placeholders) AND is_active = 1", $updatedItemIds);
                }
            } catch (Exception $e) {
                error_log("Error saving why choose items: " . $e->getMessage());
                throw new Exception("Failed to save why choose items: " . $e->getMessage());
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Curriculum content saved successfully']);
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        error_log("Curriculum API Error: " . $errorMessage);
        error_log("Stack trace: " . $e->getTraceAsString());
        error_log("Input data: " . json_encode($input ?? []));
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save curriculum content: ' . $errorMessage]);
    } catch (Error $e) {
        $errorMessage = $e->getMessage();
        error_log("Curriculum API Fatal Error: " . $errorMessage);
        error_log("Stack trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save curriculum content: ' . $errorMessage]);
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
    error_log("Unexpected output in curriculum.php: " . substr($output, 0, 200));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
}
?>
