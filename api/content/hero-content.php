<?php
/**
 * Hero Content API
 */
// CRITICAL: Disable ALL output before anything else
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Start output buffering IMMEDIATELY - before any includes
ob_start();

// Suppress session warnings by using @ operator when starting session
// We'll handle session in config.php with suppression

// Set error handler to suppress all output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Log but never display
    error_log("PHP Error in hero-content.php [$errno]: $errstr in $errfile:$errline");
    // Suppress all error output
    return true; // Return true to suppress default error handler
}, E_ALL);

// Catch fatal errors and output JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Fatal error: ' . $error['message']]);
        exit;
    }
    
    // Check if there's any unexpected output
    $output = ob_get_contents();
    if (!empty($output) && trim($output) !== '') {
        // If output doesn't look like JSON, log it and return error
        $trimmed = trim($output);
        if (!preg_match('/^[\s\n\r]*[\{\[]/', $trimmed)) {
            error_log("Unexpected non-JSON output in hero-content.php: " . substr($output, 0, 500));
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Server returned invalid response. Check error logs.']);
            exit;
        }
    }
});

// Temporarily override session_start to suppress warnings
if (!function_exists('session_start_safe')) {
    function session_start_safe() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }
}

// Include config - it will call session_start, but we've suppressed errors
require_once __DIR__ . '/../../config/config.php';

// Re-disable error display after config.php (in case it re-enabled it)
ini_set('display_errors', 0);

// Clear any output that might have been generated (from config.php, session_start, etc.)
$output = ob_get_clean();
if (!empty($output) && trim($output) !== '') {
    // Check if it's HTML (error page) or just whitespace
    $trimmed = trim($output);
    if (preg_match('/<html|<body|<div|<p|<br/i', $trimmed) || strlen($trimmed) > 50) {
        // It's HTML - log it and return error
        error_log("HTML output detected in hero-content.php: " . substr($output, 0, 500));
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Server returned HTML instead of JSON. Check PHP error logs.']);
        exit;
    } else {
        // Just whitespace or minor output - log and continue
        error_log("Minor output before JSON in hero-content.php: " . substr($output, 0, 100));
    }
}
ob_start(); // Start fresh buffer for JSON output

// Set JSON header
header('Content-Type: application/json');

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
} catch (Exception $e) {
    $output = ob_get_clean();
    if (!empty($output) && trim($output) !== '') {
        error_log("Unexpected output in database connection error: " . substr($output, 0, 200));
    }
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

if ($method === 'GET') {
    try {
        // Check if table exists first
        $tableExists = $db->fetchOne("SHOW TABLES LIKE 'hero_content'");
        if (!$tableExists) {
            $output = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
        
        $content = $db->fetchOne(
            "SELECT * FROM hero_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($content) {
            // Decode JSON fields
            if (isset($content['slides'])) {
                $content['slides'] = json_decode($content['slides'], true) ?: [];
            }
            // Map title/subtitle to headline/subheadline for backward compatibility
            if (isset($content['title']) && !isset($content['headline'])) {
                $content['headline'] = $content['title'];
            }
            if (isset($content['subtitle']) && !isset($content['subheadline'])) {
                $content['subheadline'] = $content['subtitle'];
            }
        }
        
        $output = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode($content ?: []);
        exit;
    } catch (Exception $e) {
        error_log("Hero content GET error: " . $e->getMessage());
        $output = ob_get_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to fetch hero content: ' . $e->getMessage()]);
        exit;
    }
} elseif ($method === 'POST' || $method === 'PUT') {
    try {
        require_once __DIR__ . '/../../includes/auth.php';
        $auth = getAuth();
        $auth->requireAdmin();
        
        // Check if table exists
        $tableExists = $db->fetchOne("SHOW TABLES LIKE 'hero_content'");
        if (!$tableExists) {
            // Try to create table if it doesn't exist
            try {
                $db->query("CREATE TABLE IF NOT EXISTS hero_content (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title TEXT,
                    subtitle TEXT,
                    headline TEXT,
                    subheadline TEXT,
                    description TEXT,
                    background_image VARCHAR(500),
                    slides JSON,
                    slide_interval_ms INT DEFAULT 5000,
                    slide_direction VARCHAR(10) DEFAULT 'left',
                    slide_mode VARCHAR(20) DEFAULT 'marquee',
                    background_size VARCHAR(20) DEFAULT 'contain',
                    background_position VARCHAR(20) DEFAULT 'center',
                    image_scale DECIMAL(3,1) DEFAULT 1.0,
                    headline_color VARCHAR(50) DEFAULT '#ffffff',
                    subheadline_color VARCHAR(50) DEFAULT '#ffffff',
                    description_color VARCHAR(50) DEFAULT '#ffffff',
                    cta_text VARCHAR(100),
                    cta_link VARCHAR(500),
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } catch (Exception $e) {
                error_log("Failed to create hero_content table: " . $e->getMessage());
            }
        } else {
            // Ensure all required columns exist
            try {
                $columns = $db->fetchAll("SHOW COLUMNS FROM hero_content");
                $columnNames = array_column($columns, 'Field');
                
                $requiredColumns = [
                    'headline' => "ALTER TABLE hero_content ADD COLUMN headline TEXT AFTER subtitle",
                    'subheadline' => "ALTER TABLE hero_content ADD COLUMN subheadline TEXT AFTER headline",
                    'background_size' => "ALTER TABLE hero_content ADD COLUMN background_size VARCHAR(20) DEFAULT 'contain' AFTER slide_mode",
                    'background_position' => "ALTER TABLE hero_content ADD COLUMN background_position VARCHAR(20) DEFAULT 'center' AFTER background_size",
                    'image_scale' => "ALTER TABLE hero_content ADD COLUMN image_scale DECIMAL(3,1) DEFAULT 1.0 AFTER background_position",
                    'headline_color' => "ALTER TABLE hero_content ADD COLUMN headline_color VARCHAR(50) DEFAULT '#ffffff' AFTER image_scale",
                    'subheadline_color' => "ALTER TABLE hero_content ADD COLUMN subheadline_color VARCHAR(50) DEFAULT '#ffffff' AFTER headline_color",
                    'description_color' => "ALTER TABLE hero_content ADD COLUMN description_color VARCHAR(50) DEFAULT '#ffffff' AFTER subheadline_color"
                ];
                
                foreach ($requiredColumns as $colName => $sql) {
                    if (!in_array($colName, $columnNames)) {
                        try {
                            $db->query($sql);
                            error_log("Added missing column '{$colName}' to hero_content table");
                        } catch (Exception $e) {
                            error_log("Failed to add column '{$colName}': " . $e->getMessage());
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error checking hero_content columns: " . $e->getMessage());
            }
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
        
        // Map form field names to database column names
        $fieldMapping = [
            'headline' => 'headline',
            'title' => 'headline', // Also accept 'title' for backward compatibility
            'subheadline' => 'subheadline',
            'subtitle' => 'subheadline', // Also accept 'subtitle'
            'description' => 'description',
            'background_image' => 'background_image',
            'slide_interval_ms' => 'slide_interval_ms',
            'slideIntervalMs' => 'slide_interval_ms',
            'slide_direction' => 'slide_direction',
            'slideDirection' => 'slide_direction',
            'slide_mode' => 'slide_mode',
            'slideMode' => 'slide_mode',
            'background_size' => 'background_size',
            'backgroundSize' => 'background_size',
            'background_position' => 'background_position',
            'backgroundPosition' => 'background_position',
            'image_scale' => 'image_scale',
            'imageScale' => 'image_scale',
            'headline_color' => 'headline_color',
            'headlineColor' => 'headline_color',
            'subheadline_color' => 'subheadline_color',
            'subHeadlineColor' => 'subheadline_color',
            'description_color' => 'description_color',
            'descriptionColor' => 'description_color',
            'cta_text' => 'cta_text',
            'ctaText' => 'cta_text',
            'cta_link' => 'cta_link',
            'ctaLink' => 'cta_link',
        ];
        
        // Handle slides array from form
        // Store ONLY unique slides - frontend will create exactly 2 copies (1 original + 1 duplicate)
        $slides = [];
        if (isset($input['slides']) && is_array($input['slides'])) {
            // Filter out empty slides
            $filtered = array_filter($input['slides'], function($slide) {
                return !empty(trim($slide));
            });
            // Get unique slides only (preserve order by using first occurrence)
            $seen = [];
            foreach ($filtered as $slide) {
                $trimmed = trim($slide);
                if (!empty($trimmed) && !in_array($trimmed, $seen)) {
                    $slides[] = $trimmed;
                    $seen[] = $trimmed;
                }
            }
            $slides = array_values($slides); // Re-index
        } elseif (isset($input['slides']) && is_string($input['slides'])) {
            // Already JSON string, decode it
            $decoded = json_decode($input['slides'], true);
            if (is_array($decoded)) {
                $filtered = array_filter($decoded, function($slide) {
                    return !empty(trim($slide));
                });
                // Get unique slides only (preserve order by using first occurrence)
                $seen = [];
                foreach ($filtered as $slide) {
                    $trimmed = trim($slide);
                    if (!empty($trimmed) && !in_array($trimmed, $seen)) {
                        $slides[] = $trimmed;
                        $seen[] = $trimmed;
                    }
                }
                $slides = array_values($slides); // Re-index
            }
        }
        
        // Prepare data for database
        $data = ['is_active' => 1];
        
        // Check which columns exist in the table
        try {
            $columns = $db->fetchAll("SHOW COLUMNS FROM hero_content");
            $existingColumns = array_column($columns, 'Field');
        } catch (Exception $e) {
            error_log("Error checking hero_content columns: " . $e->getMessage());
            // If we can't check columns, use default set
            $existingColumns = ['id', 'title', 'subtitle', 'headline', 'subheadline', 'description', 'background_image', 'slides', 'slide_interval_ms', 'slide_direction', 'slide_mode', 'background_size', 'background_position', 'image_scale', 'headline_color', 'subheadline_color', 'description_color', 'cta_text', 'cta_link', 'is_active', 'created_at', 'updated_at'];
        }
        
        // Handle color fields - use text field if color picker is empty
        // CRITICAL: Always ensure color values are set, even if empty use defaults
        if (empty($input['headline_color']) && !empty($input['headline_color_text'])) {
            $input['headline_color'] = $input['headline_color_text'];
        }
        if (empty($input['headline_color'])) {
            $input['headline_color'] = '#ffffff'; // Default white
        }
        
        if (empty($input['subheadline_color']) && !empty($input['subheadline_color_text'])) {
            $input['subheadline_color'] = $input['subheadline_color_text'];
        }
        if (empty($input['subheadline_color'])) {
            $input['subheadline_color'] = '#ffffff'; // Default white
        }
        
        if (empty($input['description_color']) && !empty($input['description_color_text'])) {
            $input['description_color'] = $input['description_color_text'];
        }
        if (empty($input['description_color'])) {
            $input['description_color'] = '#ffffff'; // Default white
        }
        
        // Log color values for debugging
        error_log("Hero colors received: headline=" . ($input['headline_color'] ?? 'missing') . ", subheadline=" . ($input['subheadline_color'] ?? 'missing') . ", description=" . ($input['description_color'] ?? 'missing'));
        
        // Map and add all fields (only if column exists)
        foreach ($input as $key => $value) {
            if ($key === 'id' || $key === 'is_active' || $key === 'slides') {
                continue; // Skip these, handled separately
            }
            
            // Map field name if needed
            $dbField = $fieldMapping[$key] ?? $key;
            
            // Only add if column exists in table
            if (!in_array($dbField, $existingColumns)) {
                continue;
            }
            
            // Handle color fields (skip _text versions, use main color field)
            if (strpos($key, '_text') !== false || strpos($key, '_picker') !== false) {
                continue;
            }
            
            $data[$dbField] = $value;
        }
        
        // CRITICAL: Always set color fields AFTER processing other fields
        // This ensures colors are ALWAYS saved, even if they were missing from input
        if (in_array('headline_color', $existingColumns)) {
            $data['headline_color'] = $input['headline_color'] ?? $data['headline_color'] ?? '#ffffff';
        }
        if (in_array('subheadline_color', $existingColumns)) {
            $data['subheadline_color'] = $input['subheadline_color'] ?? $data['subheadline_color'] ?? '#ffffff';
        }
        if (in_array('description_color', $existingColumns)) {
            $data['description_color'] = $input['description_color'] ?? $data['description_color'] ?? '#ffffff';
        }
        
        // Add slides as JSON (if column exists)
        if (in_array('slides', $existingColumns)) {
            if (!empty($slides)) {
                $data['slides'] = json_encode($slides);
            } else {
                $data['slides'] = null;
            }
        }
        
        // Get existing content to update or create new
        $existing = $db->fetchOne("SELECT * FROM hero_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing) {
            // Update existing - deactivate old, create new active one
            $db->update("UPDATE hero_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Prepare fields for insert (only include existing columns)
        $fields = [];
        $values = [];
        $placeholders = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $existingColumns)) {
                $fields[] = $field;
                $values[] = $value;
                $placeholders[] = '?';
            }
        }
        
        if (empty($fields)) {
            throw new Exception('No valid fields to insert');
        }
        
        $sql = "INSERT INTO hero_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        
        // Log the SQL and values for debugging
        error_log("Hero content INSERT SQL: " . $sql);
        error_log("Hero content values: " . json_encode($values));
        error_log("Hero content data being saved: " . json_encode($data));
        
        try {
            $id = $db->insert($sql, $values);
            if (!$id || $id <= 0) {
                throw new Exception('Insert failed - no ID returned');
            }
        } catch (Exception $e) {
            error_log("Hero content insert error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Values: " . json_encode($values));
            throw new Exception('Failed to save hero content: ' . $e->getMessage());
        }
        
        // Verify the colors were saved by fetching the record
        if ($id) {
            $saved = $db->fetchOne("SELECT headline_color, subheadline_color, description_color FROM hero_content WHERE id = ?", [$id]);
            error_log("Hero colors after save: " . json_encode($saved));
        }
        
        // Ensure we output valid JSON - clear any previous output
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output before JSON response in hero-content.php: " . substr($output, 0, 200));
        }
        // Start fresh buffer for JSON
        ob_start();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id' => $id, 
            'message' => 'Hero content saved successfully',
            'colors_saved' => [
                'headline_color' => $data['headline_color'] ?? null,
                'subheadline_color' => $data['subheadline_color'] ?? null,
                'description_color' => $data['description_color'] ?? null
            ]
        ]);
        ob_end_flush();
        exit;
        
    } catch (Exception $e) {
        error_log("Hero content save error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $output = ob_get_clean();
        if (!empty($output) && trim($output) !== '') {
            error_log("Unexpected output in error handler: " . substr($output, 0, 200));
        }
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Failed to save hero content: ' . $e->getMessage()
        ]);
        exit;
    }
} else {
    $output = ob_get_clean();
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
