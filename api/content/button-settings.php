<?php
/**
 * Button Settings API
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

if ($method === 'GET') {
    try {
        $settings = $db->fetchAll("SELECT * FROM button_settings WHERE is_active = 1");
        echo json_encode(['success' => true, 'data' => $settings]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch button settings: ' . $e->getMessage()]);
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
        
        // If input is an object with button settings, process each button
        if (isset($input['button_name'])) {
            // Single button update
            $buttonName = $input['button_name'];
            $text = $input['text'] ?? null;
            $color = $input['color'] ?? null;
            $backgroundColor = $input['background_color'] ?? null;
            
            // Check if button setting exists
            $existing = $db->fetchOne("SELECT id FROM button_settings WHERE button_name = ? AND is_active = 1", [$buttonName]);
            
            if ($existing) {
                // Update existing
                $db->update(
                    "UPDATE button_settings SET text = ?, color = ?, background_color = ?, updated_at = NOW() WHERE button_name = ? AND is_active = 1",
                    [$text, $color, $backgroundColor, $buttonName]
                );
            } else {
                // Create new
                $db->insert(
                    "INSERT INTO button_settings (button_name, text, color, background_color, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())",
                    [$buttonName, $text, $color, $backgroundColor]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Button settings updated successfully']);
        } else {
            // Multiple buttons update - input is the buttonSettings object
            foreach ($input as $buttonName => $buttonData) {
                if (is_array($buttonData) && isset($buttonData['textColorClass']) || isset($buttonData['bgColorClass'])) {
                    $textColorClass = $buttonData['textColorClass'] ?? null;
                    $bgColorClass = $buttonData['bgColorClass'] ?? null;
                    $customTextColor = $buttonData['customTextColor'] ?? null;
                    $customBgColor = $buttonData['customBgColor'] ?? null;
                    
                    // Store as JSON for complex settings
                    $settingsJson = json_encode($buttonData);
                    
                    $existing = $db->fetchOne("SELECT id FROM button_settings WHERE button_name = ? AND is_active = 1", [$buttonName]);
                    
                    if ($existing) {
                        $db->update(
                            "UPDATE button_settings SET text = ?, color = ?, background_color = ?, settings = ?, updated_at = NOW() WHERE button_name = ? AND is_active = 1",
                            [$textColorClass, $customTextColor, $customBgColor, $settingsJson, $buttonName]
                        );
                    } else {
                        $db->insert(
                            "INSERT INTO button_settings (button_name, text, color, background_color, settings, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())",
                            [$buttonName, $textColorClass, $customTextColor, $customBgColor, $settingsJson]
                        );
                    }
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Button settings updated successfully']);
        }
    } catch (Exception $e) {
        error_log("Button Settings API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save button settings: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Clean output buffer and send response
ob_end_flush();
?>
