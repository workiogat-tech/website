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
require_once __DIR__ . '/../../includes/storage.php';

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

function heroSlideUrl($slide): string {
    if (is_string($slide)) {
        return trim($slide);
    }
    if (is_array($slide)) {
        foreach (['url', 'image', 'src', 'file_path', 'file_url', 'background_image'] as $key) {
            if (!empty($slide[$key]) && is_string($slide[$key])) {
                return trim($slide[$key]);
            }
        }
    }
    return '';
}

function decodeHeroSlides($value): array {
    if (is_array($value)) {
        $list = $value;
    } elseif (is_string($value)) {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return [];
        }
        $decoded = json_decode($trimmed, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        $list = is_array($decoded) ? $decoded : [$trimmed];
    } else {
        return [];
    }

    $slides = [];
    foreach ($list as $slide) {
        $url = heroSlideUrl($slide);
        if ($url === '' || stripos($url, 'data:') === 0) {
            continue;
        }
        if (!in_array($url, $slides, true)) {
            $slides[] = $url;
        }
    }
    return $slides;
}

function widenHeroImageColumns($db): void {
    foreach (['slides', 'background_image'] as $column) {
        try {
            $db->query("ALTER TABLE hero_content ALTER COLUMN {$column} TYPE TEXT USING {$column}::text");
        } catch (Exception $e) {
            error_log('Hero column widen skipped: ' . $e->getMessage());
        }
    }
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
            $content['slides'] = decodeHeroSlides($content['slides'] ?? null);
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
                $columnNames = [];
                foreach ($columns as $column) {
                    $name = $column['Field'] ?? $column['field'] ?? $column['column_name'] ?? '';
                    if ($name !== '') {
                        $columnNames[] = strtolower($name);
                    }
                }
                
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
        
        // Missing slides means "leave the saved images alone". An empty array clears them.
        $slidesProvided = is_array($input) && array_key_exists('slides', $input);
        $slides = $slidesProvided ? decodeHeroSlides($input['slides']) : [];
        
        // Prepare data for database
        $data = ['is_active' => 1];
        
        // Check which columns exist in the table
        try {
            $columns = $db->fetchAll("SHOW COLUMNS FROM hero_content");
            $existingColumns = [];
            foreach ($columns as $column) {
                $name = $column['Field'] ?? $column['field'] ?? $column['column_name'] ?? '';
                if ($name !== '') {
                    $existingColumns[] = strtolower($name);
                }
            }
        } catch (Exception $e) {
            error_log("Error checking hero_content columns: " . $e->getMessage());
            $existingColumns = [];
        }

        $requiredHeroColumns = ['headline', 'subheadline', 'slide_interval_ms', 'slide_direction', 'slide_mode', 'background_size', 'background_position', 'image_scale', 'headline_color', 'subheadline_color', 'description_color', 'slides'];
        $missingHeroColumns = array_values(array_diff($requiredHeroColumns, $existingColumns));
        if (!empty($missingHeroColumns)) {
            throw new Exception('Hero settings are not in the database yet: ' . implode(', ', $missingHeroColumns));
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
            if (!in_array(strtolower($dbField), $existingColumns, true)) {
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
        if (in_array('headline_color', $existingColumns, true)) {
            $data['headline_color'] = $input['headline_color'] ?? $data['headline_color'] ?? '#ffffff';
        }
        if (in_array('subheadline_color', $existingColumns, true)) {
            $data['subheadline_color'] = $input['subheadline_color'] ?? $data['subheadline_color'] ?? '#ffffff';
        }
        if (in_array('description_color', $existingColumns, true)) {
            $data['description_color'] = $input['description_color'] ?? $data['description_color'] ?? '#ffffff';
        }

        if (array_key_exists('headline', $input) || array_key_exists('title', $input)) {
            $headline = $input['headline'] ?? $input['title'] ?? '';
            if (in_array('headline', $existingColumns, true)) {
                $data['headline'] = $headline;
            }
            if (in_array('title', $existingColumns, true)) {
                $data['title'] = $headline;
            }
        }
        if (array_key_exists('subheadline', $input) || array_key_exists('subtitle', $input)) {
            $subheadline = $input['subheadline'] ?? $input['subtitle'] ?? '';
            if (in_array('subheadline', $existingColumns, true)) {
                $data['subheadline'] = $subheadline;
            }
            if (in_array('subtitle', $existingColumns, true)) {
                $data['subtitle'] = $subheadline;
            }
        }

        $choices = [
            'slide_direction' => ['left', 'right'],
            'slide_mode' => ['marquee', 'fade'],
            'background_size' => ['contain', 'cover', 'auto'],
            'background_position' => ['center', 'top', 'bottom', 'left', 'right'],
        ];
        foreach ($choices as $field => $options) {
            if (!array_key_exists($field, $input) || !in_array($field, $existingColumns, true)) {
                continue;
            }
            $value = strtolower(trim((string) $input[$field]));
            $data[$field] = in_array($value, $options, true) ? $value : $options[0];
        }
        if (array_key_exists('slide_interval_ms', $input) && in_array('slide_interval_ms', $existingColumns, true)) {
            $interval = (int) $input['slide_interval_ms'];
            $data['slide_interval_ms'] = $interval >= 1000 ? $interval : 5000;
        }
        if (array_key_exists('image_scale', $input) && in_array('image_scale', $existingColumns, true)) {
            $scale = (float) $input['image_scale'];
            if ($scale < 0.5 || $scale > 2) {
                $scale = 1;
            }
            $data['image_scale'] = round($scale, 1);
        }

        // Add slides as JSON only when the form actually sent them.
        if ($slidesProvided && in_array('slides', $existingColumns, true)) {
            $encodedSlides = json_encode(array_values($slides), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($encodedSlides === false) {
                throw new Exception('Slide images could not be saved.');
            }
            $data['slides'] = $encodedSlides;
        }

        ensureImageColumns($db);
        widenHeroImageColumns($db);

        // Keep the current row. Replacing it used to hide the previous photo when the new save failed.
        $existing = $db->fetchOne("SELECT * FROM hero_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
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
            throw new Exception('No valid fields to save');
        }
        
        try {
            if ($existing) {
                $assignments = [];
                foreach ($fields as $field) {
                    $assignments[] = $field . ' = ?';
                }
                if (in_array('updated_at', $existingColumns, true)) {
                    $assignments[] = 'updated_at = NOW()';
                }
                $updateValues = $values;
                $updateValues[] = $existing['id'];
                $db->update(
                    "UPDATE hero_content SET " . implode(', ', $assignments) . " WHERE id = ?",
                    $updateValues
                );
                $id = $existing['id'];
                try {
                    $db->update("UPDATE hero_content SET is_active = 0 WHERE id <> ? AND is_active = 1", [$id]);
                } catch (Exception $ignore) {
                    error_log('Could not deactivate older hero rows: ' . $ignore->getMessage());
                }
            } else {
                $sql = "INSERT INTO hero_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
                $id = $db->insert($sql, $values);
                if (!$id || $id <= 0) {
                    throw new Exception('Insert failed - no ID returned');
                }
            }
        } catch (Exception $e) {
            error_log("Hero content save error: " . $e->getMessage());
            throw new Exception('Failed to save hero content: ' . $e->getMessage());
        }
        
        // Confirm the slider settings are actually in the row that the admin page reads back.
        $saved = $id ? $db->fetchOne("SELECT * FROM hero_content WHERE id = ?", [$id]) : null;
        if (!$saved) {
            throw new Exception('Hero content was not stored.');
        }
        $savedRow = array_change_key_case($saved, CASE_LOWER);
        foreach (['slide_interval_ms', 'slide_direction', 'slide_mode', 'background_size', 'background_position'] as $field) {
            if (!isset($data[$field])) {
                continue;
            }
            if (!array_key_exists($field, $savedRow) || (string) $savedRow[$field] !== (string) $data[$field]) {
                throw new Exception('Hero setting "' . $field . '" was not saved to the database.');
            }
        }
        if (isset($data['image_scale'])) {
            if (!array_key_exists('image_scale', $savedRow) || abs((float) $savedRow['image_scale'] - (float) $data['image_scale']) > 0.05) {
                throw new Exception('Image scale was not saved to the database.');
            }
        }
        $savedSlides = [];
        if ($slidesProvided) {
            $savedSlides = decodeHeroSlides($savedRow['slides'] ?? null);
            if ($savedSlides !== array_values($slides)) {
                throw new Exception('Slide images were not saved to the database.');
            }
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
            'slides' => $slidesProvided ? $savedSlides : decodeHeroSlides($savedRow['slides'] ?? null),
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
