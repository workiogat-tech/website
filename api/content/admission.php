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
        header('Cache-Control: no-store, no-cache, must-revalidate');
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

function admissionDecode($value, $fallback) {
    if (is_array($value)) {
        return $value;
    }
    if (!is_string($value) || trim($value) === '') {
        return $fallback;
    }
    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : $fallback;
}

function admissionPrepareRow($row) {
    if (!$row) {
        return $row;
    }
    $row['admission_levels'] = admissionDecode($row['admission_levels'] ?? null, []);
    $fee = admissionDecode($row['fee_structure'] ?? null, []);
    if (!is_array($fee)) {
        $fee = ['content' => is_string($row['fee_structure'] ?? null) ? $row['fee_structure'] : ''];
    }
    if (!isset($fee['content']) && isset($fee[0]) && is_string($fee[0])) {
        $fee = ['content' => $fee[0]];
    }
    $row['fee_structure'] = $fee;
    return $row;
}

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
            echo json_encode(admissionPrepareRow($content));
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
        
        $existing = $db->fetchOne("SELECT * FROM admission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: [];
        $existingLevels = admissionDecode($existing['admission_levels'] ?? null, []);
        $existingFee = admissionDecode($existing['fee_structure'] ?? null, []);

        $admissionLevels = $existingLevels;
        $levelsWereSent = array_key_exists('admission_levels', $input) || !empty($input['admission_levels_present']);
        if ($levelsWereSent) {
            $admissionLevels = [];
            $levelInput = is_array($input['admission_levels'] ?? null) ? $input['admission_levels'] : [];
            foreach ($levelInput as $level) {
                if (!is_array($level)) {
                    continue;
                }
                $heading = trim((string)($level['heading'] ?? ''));
                $title = trim((string)($level['title'] ?? ''));
                $description = trim((string)($level['description'] ?? ''));
                $icon = trim((string)($level['icon'] ?? ''));
                if ($heading === '' && $title === '' && $description === '') {
                    continue;
                }
                $admissionLevels[] = [
                    'icon' => $icon !== '' ? $icon : '📚',
                    'heading' => $heading,
                    'title' => $title,
                    'description' => $description
                ];
            }
        }

        $feeStructure = [
            'content' => is_array($existingFee) ? (string)($existingFee['content'] ?? '') : '',
            'image' => $feeImage ?: (is_array($existingFee) ? (string)($existingFee['image'] ?? '') : '')
        ];
        if (array_key_exists('fee_structure', $input)) {
            if (is_array($input['fee_structure'])) {
                if (array_key_exists('content', $input['fee_structure'])) {
                    $feeStructure['content'] = trim((string)$input['fee_structure']['content']);
                }
                if (!empty($input['fee_structure']['image'])) {
                    $feeStructure['image'] = trim((string)$input['fee_structure']['image']);
                }
            } else {
                $feeStructure['content'] = trim((string)$input['fee_structure']);
            }
        }
        if ($feeImage) {
            $feeStructure['image'] = $feeImage;
        }

        if ($heroImage === null && array_key_exists('hero_image', $input)) {
            $heroImage = '';
        } elseif ($heroImage === null) {
            $heroImage = $existing['hero_image'] ?? '';
        }

        $admissionLevelsJson = json_encode(array_values($admissionLevels), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $feeStructureJson = json_encode($feeStructure, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($admissionLevelsJson === false || $feeStructureJson === false) {
            throw new Exception('The admission details could not be saved.');
        }

        $db->beginTransaction();
        try {
            if (!empty($existing['id'])) {
                $db->update(
                    "UPDATE admission_content SET hero_image = ?, admission_levels = ?, fee_structure = ?, is_active = 1, updated_at = NOW() WHERE id = ?",
                    [$heroImage, $admissionLevelsJson, $feeStructureJson, $existing['id']]
                );
                $db->update("UPDATE admission_content SET is_active = 0 WHERE id <> ?", [$existing['id']]);
                $savedId = $existing['id'];
            } else {
                $savedId = $db->insert(
                    "INSERT INTO admission_content (hero_image, admission_levels, fee_structure, is_active, created_at) VALUES (?, ?, ?, 1, NOW())",
                    [$heroImage, $admissionLevelsJson, $feeStructureJson]
                );
            }
            $db->commit();
        } catch (Exception $dbError) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            throw new Exception('Database error: ' . $dbError->getMessage());
        }

        $saved = $db->fetchOne("SELECT * FROM admission_content WHERE id = ?", [$savedId]);
        $saved = admissionPrepareRow($saved ?: []);
        echo json_encode([
            'success' => true,
            'message' => 'Admission content saved successfully',
            'admission' => $saved,
            'admissionLevels' => $saved['admission_levels'] ?? []
        ]);
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
