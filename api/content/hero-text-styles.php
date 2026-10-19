<?php
/**
 * Hero Text Styles API
 * Manages global hero section text colors and styles
 */
// Start output buffering
ob_start();

// Suppress error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';

// Clear any output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Ensure table exists with correct structure
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'hero_text_styles'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS hero_text_styles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title_color VARCHAR(7) DEFAULT '#ffffff',
            title_font_size INT DEFAULT 64,
            title_font_weight VARCHAR(20) DEFAULT 'bold',
            title_text_shadow BOOLEAN DEFAULT TRUE,
            subtitle_color VARCHAR(7) DEFAULT '#ffffff',
            subtitle_font_size INT DEFAULT 28,
            subtitle_font_weight VARCHAR(20) DEFAULT '300',
            subtitle_text_shadow BOOLEAN DEFAULT TRUE,
            caption_color VARCHAR(50) DEFAULT 'rgba(255, 255, 255, 0.9)',
            caption_font_size INT DEFAULT 21,
            caption_font_weight VARCHAR(20) DEFAULT '400',
            caption_text_shadow BOOLEAN DEFAULT FALSE,
            description_color VARCHAR(7) DEFAULT '#ffffff',
            description_font_size INT DEFAULT 20,
            description_font_weight VARCHAR(20) DEFAULT '400',
            description_text_shadow BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Insert default settings
        $db->insert(
            "INSERT INTO hero_text_styles (is_active) VALUES (1)"
        );
    } else {
        // Check and add missing columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM hero_text_styles");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'title_color' => "ALTER TABLE hero_text_styles ADD COLUMN title_color VARCHAR(7) DEFAULT '#ffffff' AFTER id",
            'title_font_size' => "ALTER TABLE hero_text_styles ADD COLUMN title_font_size INT DEFAULT 64 AFTER title_color",
            'title_font_weight' => "ALTER TABLE hero_text_styles ADD COLUMN title_font_weight VARCHAR(20) DEFAULT 'bold' AFTER title_font_size",
            'title_text_shadow' => "ALTER TABLE hero_text_styles ADD COLUMN title_text_shadow BOOLEAN DEFAULT TRUE AFTER title_font_weight",
            'subtitle_color' => "ALTER TABLE hero_text_styles ADD COLUMN subtitle_color VARCHAR(7) DEFAULT '#ffffff' AFTER title_text_shadow",
            'subtitle_font_size' => "ALTER TABLE hero_text_styles ADD COLUMN subtitle_font_size INT DEFAULT 28 AFTER subtitle_color",
            'subtitle_font_weight' => "ALTER TABLE hero_text_styles ADD COLUMN subtitle_font_weight VARCHAR(20) DEFAULT '300' AFTER subtitle_font_size",
            'subtitle_text_shadow' => "ALTER TABLE hero_text_styles ADD COLUMN subtitle_text_shadow BOOLEAN DEFAULT TRUE AFTER subtitle_font_weight",
            'caption_color' => "ALTER TABLE hero_text_styles ADD COLUMN caption_color VARCHAR(50) DEFAULT 'rgba(255, 255, 255, 0.9)' AFTER subtitle_text_shadow",
            'caption_font_size' => "ALTER TABLE hero_text_styles ADD COLUMN caption_font_size INT DEFAULT 21 AFTER caption_color",
            'caption_font_weight' => "ALTER TABLE hero_text_styles ADD COLUMN caption_font_weight VARCHAR(20) DEFAULT '400' AFTER caption_font_size",
            'caption_text_shadow' => "ALTER TABLE hero_text_styles ADD COLUMN caption_text_shadow BOOLEAN DEFAULT FALSE AFTER caption_font_weight",
            'description_color' => "ALTER TABLE hero_text_styles ADD COLUMN description_color VARCHAR(7) DEFAULT '#ffffff' AFTER caption_text_shadow",
            'description_font_size' => "ALTER TABLE hero_text_styles ADD COLUMN description_font_size INT DEFAULT 20 AFTER description_color",
            'description_font_weight' => "ALTER TABLE hero_text_styles ADD COLUMN description_font_weight VARCHAR(20) DEFAULT '400' AFTER description_font_size",
            'description_text_shadow' => "ALTER TABLE hero_text_styles ADD COLUMN description_text_shadow BOOLEAN DEFAULT TRUE AFTER description_font_weight",
        ];
        
        foreach ($requiredColumns as $colName => $alterSql) {
            if (!in_array($colName, $columnNames)) {
                try {
                    $db->query($alterSql);
                    error_log("Added missing column: $colName to hero_text_styles table");
                } catch (Exception $e) {
                    error_log("Error adding column $colName: " . $e->getMessage());
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Hero text styles table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $styles = $db->fetchOne("SELECT * FROM hero_text_styles WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        if ($styles) {
            echo json_encode($styles);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch hero text styles: ' . $e->getMessage()]);
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
        
        // Handle color text fields - use text field if color picker is empty
        if (empty($input['title_color']) && !empty($input['title_color_text'])) {
            $input['title_color'] = $input['title_color_text'];
        }
        if (empty($input['subtitle_color']) && !empty($input['subtitle_color_text'])) {
            $input['subtitle_color'] = $input['subtitle_color_text'];
        }
        if (empty($input['caption_color']) && !empty($input['caption_color_text'])) {
            $input['caption_color'] = $input['caption_color_text'];
        }
        if (empty($input['description_color']) && !empty($input['description_color_text'])) {
            $input['description_color'] = $input['description_color_text'];
        }
        
        // Prepare data
        $titleColor = $input['title_color'] ?? '#ffffff';
        $titleFontSize = intval($input['title_font_size'] ?? 64);
        $titleFontWeight = $input['title_font_weight'] ?? 'bold';
        $titleTextShadow = isset($input['title_text_shadow']) ? ($input['title_text_shadow'] == '1' || $input['title_text_shadow'] === true ? 1 : 0) : 1;
        
        $subtitleColor = $input['subtitle_color'] ?? '#ffffff';
        $subtitleFontSize = intval($input['subtitle_font_size'] ?? 28);
        $subtitleFontWeight = $input['subtitle_font_weight'] ?? '300';
        $subtitleTextShadow = isset($input['subtitle_text_shadow']) ? ($input['subtitle_text_shadow'] == '1' || $input['subtitle_text_shadow'] === true ? 1 : 0) : 1;
        
        $captionColor = $input['caption_color'] ?? 'rgba(255, 255, 255, 0.9)';
        $captionFontSize = intval($input['caption_font_size'] ?? 21);
        $captionFontWeight = $input['caption_font_weight'] ?? '400';
        $captionTextShadow = isset($input['caption_text_shadow']) ? ($input['caption_text_shadow'] == '1' || $input['caption_text_shadow'] === true ? 1 : 0) : 0;
        
        $descriptionColor = $input['description_color'] ?? '#ffffff';
        $descriptionFontSize = intval($input['description_font_size'] ?? 20);
        $descriptionFontWeight = $input['description_font_weight'] ?? '400';
        $descriptionTextShadow = isset($input['description_text_shadow']) ? ($input['description_text_shadow'] == '1' || $input['description_text_shadow'] === true ? 1 : 0) : 1;
        
        // Check if record exists
        $existing = $db->fetchOne("SELECT id FROM hero_text_styles WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($existing && isset($existing['id'])) {
            // Deactivate old record
            $db->update("UPDATE hero_text_styles SET is_active = 0 WHERE id = ?", [$existing['id']]);
        }
        
        // Insert new record
        $id = $db->insert(
            "INSERT INTO hero_text_styles (
                title_color, title_font_size, title_font_weight, title_text_shadow,
                subtitle_color, subtitle_font_size, subtitle_font_weight, subtitle_text_shadow,
                caption_color, caption_font_size, caption_font_weight, caption_text_shadow,
                description_color, description_font_size, description_font_weight, description_text_shadow,
                is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
            [
                $titleColor, $titleFontSize, $titleFontWeight, $titleTextShadow,
                $subtitleColor, $subtitleFontSize, $subtitleFontWeight, $subtitleTextShadow,
                $captionColor, $captionFontSize, $captionFontWeight, $captionTextShadow,
                $descriptionColor, $descriptionFontSize, $descriptionFontWeight, $descriptionTextShadow
            ]
        );
        
        if (!$id) {
            throw new Exception('Failed to save hero text styles');
        }
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Hero text styles saved successfully']);
    } catch (Exception $e) {
        error_log("Hero Text Styles API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save hero text styles: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
