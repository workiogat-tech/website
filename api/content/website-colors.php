<?php
/**
 * Website Colors API
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

// Check if table exists, if not create it, and ensure it has the correct structure
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'website_colors'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS website_colors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            section_name VARCHAR(100) NOT NULL UNIQUE,
            primary_color VARCHAR(7),
            secondary_color VARCHAR(7),
            background_color VARCHAR(7),
            text_color VARCHAR(7),
            accent_color VARCHAR(7),
            hover_color VARCHAR(7),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Insert default color settings
        $defaultSections = [
            ['header', '#800020', '#6B0000', '#ffffff', '#800020', '#6B0000', '#ffffff'],
            ['hero', '#800020', '#6B0000', '#ffffff', '#800020', '#6B0000', '#ffffff'],
            ['about', '#f8fafc', '#e2e8f0', '#1e293b', '#800020', '#cbd5e1', '#ffffff'],
            ['activities', '#ffffff', '#f1f5f9', '#1e293b', '#800020', '#e2e8f0', '#ffffff'],
            ['teachers', '#ffffff', '#f8fafc', '#1e293b', '#800020', '#f1f5f9', '#ffffff'],
            ['facilities', '#f8fafc', '#e2e8f0', '#1e293b', '#800020', '#cbd5e1', '#ffffff'],
            ['testimonials', '#ffffff', '#f1f5f9', '#1e293b', '#800020', '#e2e8f0', '#ffffff'],
            ['contact', '#800020', '#6B0000', '#ffffff', '#800020', '#6B0000', '#ffffff'],
            ['footer', '#800020', '#6B0000', '#ffffff', '#800020', '#6B0000', '#ffffff'],
            ['buttons', '#800020', '#6B0000', '#ffffff', '#800020', '#6B0000', '#ffffff'],
        ];
        
        foreach ($defaultSections as $section) {
            $db->insert(
                "INSERT INTO website_colors (section_name, primary_color, secondary_color, text_color, accent_color, hover_color, background_color, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
                $section
            );
        }
    } else {
        // Table exists - check and add missing columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM website_colors");
        $columnNames = array_column($columns, 'Field');
        
        // Required columns with their definitions
        $requiredColumns = [
            'section_name' => "ALTER TABLE website_colors ADD COLUMN section_name VARCHAR(100) NOT NULL UNIQUE AFTER id",
            'primary_color' => "ALTER TABLE website_colors ADD COLUMN primary_color VARCHAR(7) AFTER section_name",
            'secondary_color' => "ALTER TABLE website_colors ADD COLUMN secondary_color VARCHAR(7) AFTER primary_color",
            'background_color' => "ALTER TABLE website_colors ADD COLUMN background_color VARCHAR(7) AFTER secondary_color",
            'text_color' => "ALTER TABLE website_colors ADD COLUMN text_color VARCHAR(7) AFTER background_color",
            'accent_color' => "ALTER TABLE website_colors ADD COLUMN accent_color VARCHAR(7) AFTER text_color",
            'hover_color' => "ALTER TABLE website_colors ADD COLUMN hover_color VARCHAR(7) AFTER accent_color",
            'is_active' => "ALTER TABLE website_colors ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER hover_color",
            'created_at' => "ALTER TABLE website_colors ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active",
            'updated_at' => "ALTER TABLE website_colors ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
        ];
        
        // Add missing columns
        foreach ($requiredColumns as $colName => $alterSql) {
            if (!in_array($colName, $columnNames)) {
                try {
                    $db->query($alterSql);
                    error_log("Added missing column: $colName to website_colors table");
                } catch (Exception $e) {
                    error_log("Error adding column $colName: " . $e->getMessage());
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Website colors table check error: " . $e->getMessage());
}

if ($method === 'GET') {
    try {
        $section = $_GET['section'] ?? null;
        if ($section) {
            // Get single section
            $color = $db->fetchOne("SELECT * FROM website_colors WHERE section_name = ? AND is_active = 1", [$section]);
            if ($color) {
                echo json_encode($color);
            } else {
                echo json_encode([]);
            }
        } else {
            // Get all sections
            $colors = $db->fetchAll("SELECT * FROM website_colors WHERE is_active = 1");
            echo json_encode(['success' => true, 'data' => $colors]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch website colors: ' . $e->getMessage()]);
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
        
        // Check if updating multiple sections (colors array) or single section
        // Handle form data structure: colors[buttons][sectionName], colors[buttons][primaryColor], etc.
        if (isset($input['colors']) && is_array($input['colors'])) {
            // Convert form data structure to proper array format
            // PHP parses colors[buttons][sectionName] as $input['colors']['buttons']['sectionName']
            $colorsArray = [];
            foreach ($input['colors'] as $sectionKey => $sectionData) {
                if (is_array($sectionData)) {
                    // Get section name from the data or use the key
                    $sectionName = $sectionData['sectionName'] ?? $sectionKey;
                    $colorsArray[] = [
                        'sectionName' => $sectionName,
                        'primaryColor' => $sectionData['primaryColor'] ?? null,
                        'secondaryColor' => $sectionData['secondaryColor'] ?? null,
                        'backgroundColor' => $sectionData['backgroundColor'] ?? null,
                        'textColor' => $sectionData['textColor'] ?? null,
                        'accentColor' => $sectionData['accentColor'] ?? null,
                        'hoverColor' => $sectionData['hoverColor'] ?? null,
                    ];
                }
            }
            
            // Update multiple sections
            foreach ($colorsArray as $colorData) {
                $sectionName = $colorData['sectionName'] ?? null;
                if (!$sectionName || empty($sectionName)) {
                    error_log("Skipping color data - missing section name: " . json_encode($colorData));
                    continue;
                }
                
                $primaryColor = $colorData['primaryColor'] ?? $colorData['primary_color'] ?? null;
                $secondaryColor = $colorData['secondaryColor'] ?? $colorData['secondary_color'] ?? null;
                $backgroundColor = $colorData['backgroundColor'] ?? $colorData['background_color'] ?? null;
                $textColor = $colorData['textColor'] ?? $colorData['text_color'] ?? null;
                $accentColor = $colorData['accentColor'] ?? $colorData['accent_color'] ?? null;
                $hoverColor = $colorData['hoverColor'] ?? $colorData['hover_color'] ?? null;
                
                // Ensure all required columns exist before querying
                try {
                    $columns = $db->fetchAll("SHOW COLUMNS FROM website_colors");
                    $columnNames = array_column($columns, 'Field');
                    
                    $requiredCols = ['section_name', 'primary_color', 'secondary_color', 'background_color', 'text_color', 'accent_color', 'hover_color'];
                    foreach ($requiredCols as $col) {
                        if (!in_array($col, $columnNames)) {
                            if ($col === 'section_name') {
                                $db->query("ALTER TABLE website_colors ADD COLUMN section_name VARCHAR(100) NOT NULL UNIQUE AFTER id");
                            } elseif ($col === 'primary_color') {
                                $db->query("ALTER TABLE website_colors ADD COLUMN primary_color VARCHAR(7) AFTER section_name");
                            } elseif ($col === 'secondary_color') {
                                $db->query("ALTER TABLE website_colors ADD COLUMN secondary_color VARCHAR(7) AFTER primary_color");
                            } elseif ($col === 'background_color') {
                                $db->query("ALTER TABLE website_colors ADD COLUMN background_color VARCHAR(7) AFTER secondary_color");
                            } elseif ($col === 'text_color') {
                                $db->query("ALTER TABLE website_colors ADD COLUMN text_color VARCHAR(7) AFTER background_color");
                            } elseif ($col === 'accent_color') {
                                $db->query("ALTER TABLE website_colors ADD COLUMN accent_color VARCHAR(7) AFTER text_color");
                            } elseif ($col === 'hover_color') {
                                $db->query("ALTER TABLE website_colors ADD COLUMN hover_color VARCHAR(7) AFTER accent_color");
                            }
                        }
                    }
                } catch (Exception $colError) {
                    error_log("Column check error (may already exist): " . $colError->getMessage());
                }
                
                $existing = $db->fetchOne("SELECT id FROM website_colors WHERE section_name = ? AND is_active = 1", [$sectionName]);
                
                if ($existing && isset($existing['id'])) {
                    $db->update(
                        "UPDATE website_colors SET primary_color = ?, secondary_color = ?, background_color = ?, text_color = ?, accent_color = ?, hover_color = ?, updated_at = NOW() WHERE section_name = ? AND is_active = 1",
                        [$primaryColor, $secondaryColor, $backgroundColor, $textColor, $accentColor, $hoverColor, $sectionName]
                    );
                } else {
                    $db->insert(
                        "INSERT INTO website_colors (section_name, primary_color, secondary_color, background_color, text_color, accent_color, hover_color, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
                        [$sectionName, $primaryColor, $secondaryColor, $backgroundColor, $textColor, $accentColor, $hoverColor]
                    );
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Website colors updated successfully']);
        } else {
            // Single section update
            $sectionName = $_GET['section'] ?? $input['section_name'] ?? $input['sectionName'] ?? null;
            if (!$sectionName) {
                throw new Exception('Section name is required');
            }
            
            $primaryColor = $input['primaryColor'] ?? $input['primary_color'] ?? null;
            $secondaryColor = $input['secondaryColor'] ?? $input['secondary_color'] ?? null;
            $backgroundColor = $input['backgroundColor'] ?? $input['background_color'] ?? null;
            $textColor = $input['textColor'] ?? $input['text_color'] ?? null;
            $accentColor = $input['accentColor'] ?? $input['accent_color'] ?? null;
            $hoverColor = $input['hoverColor'] ?? $input['hover_color'] ?? null;
            
            // Ensure all required columns exist before querying
            try {
                $columns = $db->fetchAll("SHOW COLUMNS FROM website_colors");
                $columnNames = array_column($columns, 'Field');
                
                $requiredCols = ['section_name', 'primary_color', 'secondary_color', 'background_color', 'text_color', 'accent_color', 'hover_color'];
                foreach ($requiredCols as $col) {
                    if (!in_array($col, $columnNames)) {
                        if ($col === 'section_name') {
                            $db->query("ALTER TABLE website_colors ADD COLUMN section_name VARCHAR(100) NOT NULL UNIQUE AFTER id");
                        } elseif ($col === 'primary_color') {
                            $db->query("ALTER TABLE website_colors ADD COLUMN primary_color VARCHAR(7) AFTER section_name");
                        } elseif ($col === 'secondary_color') {
                            $db->query("ALTER TABLE website_colors ADD COLUMN secondary_color VARCHAR(7) AFTER primary_color");
                        } elseif ($col === 'background_color') {
                            $db->query("ALTER TABLE website_colors ADD COLUMN background_color VARCHAR(7) AFTER secondary_color");
                        } elseif ($col === 'text_color') {
                            $db->query("ALTER TABLE website_colors ADD COLUMN text_color VARCHAR(7) AFTER background_color");
                        } elseif ($col === 'accent_color') {
                            $db->query("ALTER TABLE website_colors ADD COLUMN accent_color VARCHAR(7) AFTER text_color");
                        } elseif ($col === 'hover_color') {
                            $db->query("ALTER TABLE website_colors ADD COLUMN hover_color VARCHAR(7) AFTER accent_color");
                        }
                    }
                }
            } catch (Exception $colError) {
                error_log("Column check error (may already exist): " . $colError->getMessage());
            }
            
            $existing = $db->fetchOne("SELECT id FROM website_colors WHERE section_name = ? AND is_active = 1", [$sectionName]);
            
            if ($existing && isset($existing['id'])) {
                $db->update(
                    "UPDATE website_colors SET primary_color = ?, secondary_color = ?, background_color = ?, text_color = ?, accent_color = ?, hover_color = ?, updated_at = NOW() WHERE section_name = ? AND is_active = 1",
                    [$primaryColor, $secondaryColor, $backgroundColor, $textColor, $accentColor, $hoverColor, $sectionName]
                );
            } else {
                $db->insert(
                    "INSERT INTO website_colors (section_name, primary_color, secondary_color, background_color, text_color, accent_color, hover_color, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
                    [$sectionName, $primaryColor, $secondaryColor, $backgroundColor, $textColor, $accentColor, $hoverColor]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Website colors updated successfully']);
        }
    } catch (Exception $e) {
        error_log("Website Colors API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save website colors: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
