<?php
/**
 * Admission Content API
 */
// CRITICAL: Suppress error display FIRST
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

ob_start();

// Register shutdown function to catch fatal errors
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
        echo json_encode(['error' => 'Fatal error: ' . $error['message'] . ' in ' . basename($error['file']) . ' on line ' . $error['line']]);
        exit;
    }
});

try {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../includes/image-helper.php';
    
    // Override config.php display_errors setting
    ini_set('display_errors', 0);
    
    ob_clean();
    
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
} catch (Exception $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
} catch (Error $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode(['error' => 'Fatal error: ' . $e->getMessage()]);
    exit;
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

        // Ensure table exists
        try {
            $tableExists = $db->fetchOne("SHOW TABLES LIKE 'admission_content'");
            if (!$tableExists) {
                $db->query("CREATE TABLE IF NOT EXISTS admission_content (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    hero_image VARCHAR(500),
                    admission_levels JSON,
                    olevel_content JSON,
                    alevel_content JSON,
                    fee_structure JSON,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
            } else {
                // Ensure all required columns exist
                $columns = $db->fetchAll("SHOW COLUMNS FROM admission_content");
                $columnNames = array_column($columns, 'Field');
                
                $requiredColumns = [
                    'hero_image' => 'VARCHAR(500)',
                    'admission_levels' => 'JSON',
                    'olevel_content' => 'JSON',
                    'alevel_content' => 'JSON',
                    'fee_structure' => 'JSON'
                ];
                
                foreach ($requiredColumns as $column => $type) {
                    if (!in_array($column, $columnNames)) {
                        $db->query("ALTER TABLE admission_content ADD COLUMN $column $type");
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Admission table check error: " . $e->getMessage());
        }

if ($method === 'GET') {
    try {
        $content = $db->fetchOne(
            "SELECT * FROM admission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            echo json_encode($content);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch admission content: ' . $e->getMessage()]);
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
        
        // Handle images from media library (text inputs) or file uploads
        $heroImage = null;
        $feeImage = null;
        
        // Check if data is coming as JSON (from AJAX) or form data
        $input = $_POST;
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            if ($jsonInput) {
                $input = array_merge($input, $jsonInput);
            }
        }
        
        // Process hero image - check text input first (media library), then file upload
        if (isset($input['hero_image']) && !empty(trim($input['hero_image']))) {
            $heroImage = trim($input['hero_image']);
        } elseif (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
            $heroImage = handleImageUpload($_FILES['hero_image'], 'admission');
        }
        
        // Process fee structure image - check text input first (media library), then file upload
        if (isset($input['fee_structure_image']) && !empty(trim($input['fee_structure_image']))) {
            $feeImage = trim($input['fee_structure_image']);
        } elseif (isset($_FILES['fee_structure_image']) && $_FILES['fee_structure_image']['error'] === UPLOAD_ERR_OK) {
            $feeImage = handleImageUpload($_FILES['fee_structure_image'], 'admission');
        }
        
        // Handle admission levels (new flexible system)
        $admissionLevels = [];
        
        // Handle nested array structure from form
        if (isset($input['admission_levels']) && is_array($input['admission_levels'])) {
            foreach ($input['admission_levels'] as $index => $level) {
                try {
                    if (!is_array($level)) {
                        error_log("Admission level $index is not an array: " . gettype($level));
                        continue;
                    }
                    
                    // Only add if heading or title exists
                    if (empty($level['heading']) && empty($level['title'])) {
                        continue;
                    }
                    
                    // Safely process description - handle special characters and encoding
                    $description = '';
                    if (isset($level['description'])) {
                        if (is_string($level['description'])) {
                            $description = trim($level['description']);
                            // Clean and validate description
                            $description = mb_convert_encoding($description, 'UTF-8', 'UTF-8');
                            // Remove any null bytes or invalid UTF-8 sequences
                            $description = str_replace("\0", '', $description);
                            // Ensure valid UTF-8
                            if (!mb_check_encoding($description, 'UTF-8')) {
                                $description = mb_convert_encoding($description, 'UTF-8', mb_detect_encoding($description));
                            }
                        } elseif (is_array($level['description'])) {
                            // If it's an array, convert to string
                            $description = '';
                            error_log("Warning: Description for level $index is an array, converting to empty string");
                        } else {
                            $description = (string)$level['description'];
                        }
                    }
                    
                    $admissionLevels[] = [
                        'icon' => trim($level['icon'] ?? '📚'),
                        'heading' => trim($level['heading'] ?? ''),
                        'title' => trim($level['title'] ?? ''),
                        'description' => $description
                    ];
                } catch (Exception $levelError) {
                    error_log("Error processing admission level $index: " . $levelError->getMessage());
                    // Continue with next level instead of failing completely
                    continue;
                }
            }
        }
        
        // If no levels found, check for old format (backward compatibility)
        if (empty($admissionLevels)) {
            $olevelContent = [
                'title' => $input['olevel_title'] ?? "O'LEVEL",
                'description' => $input['olevel_description'] ?? ''
            ];
            
            $alevelContent = [
                'title' => $input['alevel_title'] ?? "A'LEVEL",
                'description' => $input['alevel_description'] ?? ''
            ];
            
            // Convert to new format
            if (!empty($olevelContent['title']) || !empty($olevelContent['description'])) {
                $admissionLevels[] = [
                    'icon' => '📚',
                    'heading' => "O'LEVEL Information",
                    'title' => $olevelContent['title'],
                    'description' => $olevelContent['description']
                ];
            }
            
            if (!empty($alevelContent['title']) || !empty($alevelContent['description'])) {
                $admissionLevels[] = [
                    'icon' => '📚',
                    'heading' => "A'LEVEL Information",
                    'title' => $alevelContent['title'],
                    'description' => $alevelContent['description']
                ];
            }
        }
        
        // Ensure we have at least empty array
        if (empty($admissionLevels)) {
            $admissionLevels = [];
        }
        
        // Prepare fee structure
        $feeStructure = [];
        if ($feeImage) {
            $feeStructure['image'] = $feeImage;
        }
        
        // Deactivate all existing records
        $db->update("UPDATE admission_content SET is_active = 0");
        
        // Clear old format fields when using new format (to prevent fallback)
        $olevelContentForDB = empty($admissionLevels) ? (isset($olevelContent) ? json_encode($olevelContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null) : null;
        $alevelContentForDB = empty($admissionLevels) ? (isset($alevelContent) ? json_encode($alevelContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null) : null;
        
        // Encode admission levels with proper flags to handle special characters
        $admissionLevelsJson = json_encode($admissionLevels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($admissionLevelsJson === false) {
            $errorMsg = 'Failed to encode admission levels: ' . json_last_error_msg();
            error_log("Admission JSON encode error: " . $errorMsg);
            throw new Exception($errorMsg);
        }
        
        // Encode fee structure
        $feeStructureJson = json_encode($feeStructure, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($feeStructureJson === false) {
            $errorMsg = 'Failed to encode fee structure: ' . json_last_error_msg();
            error_log("Admission fee structure JSON encode error: " . $errorMsg);
            throw new Exception($errorMsg);
        }
        
        // Insert new record
        try {
            $db->insert(
                "INSERT INTO admission_content (hero_image, admission_levels, olevel_content, alevel_content, fee_structure, is_active) VALUES (?, ?, ?, ?, ?, 1)",
                [
                    $heroImage,
                    $admissionLevelsJson,
                    $olevelContentForDB,
                    $alevelContentForDB,
                    $feeStructureJson
                ]
            );
        } catch (Exception $dbError) {
            error_log("Admission database insert error: " . $dbError->getMessage());
            error_log("Admission levels data: " . substr($admissionLevelsJson, 0, 500));
            throw new Exception('Database error: ' . $dbError->getMessage());
        }
        
        echo json_encode(['success' => true, 'message' => 'Admission content saved successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        $errorMessage = $e->getMessage();
        error_log("Admission save error: " . $errorMessage);
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Provide user-friendly error message
        $userMessage = 'Failed to save admission content';
        if (strpos($errorMessage, 'JSON') !== false) {
            $userMessage = 'Error processing description. Please check for special characters and try again.';
        } elseif (strpos($errorMessage, 'Database') !== false) {
            $userMessage = 'Database error. Please check PHP error logs for details.';
        }
        
        echo json_encode(['error' => $userMessage . ': ' . $errorMessage]);
    } catch (Error $e) {
        // Catch PHP 7+ fatal errors
        http_response_code(500);
        $errorMessage = $e->getMessage();
        error_log("Admission save fatal error: " . $errorMessage);
        error_log("Stack trace: " . $e->getTraceAsString());
        echo json_encode(['error' => 'Fatal error: ' . $errorMessage]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
