<?php
/**
 * People Behind School Content API
 * SIMPLIFIED VERSION - Guaranteed to return JSON
 */
// NO WHITESPACE OR OUTPUT BEFORE THIS POINT

// CRITICAL: Set error handling FIRST - before ANY includes
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if headers were already sent (fatal error indicator)
if (headers_sent($file, $line)) {
    // Headers already sent - this means output happened before we could set JSON header
    // Try to send JSON anyway
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8', true);
    echo json_encode(['error' => 'Fatal error: Output started before JSON header in ' . basename($file) . ':' . $line]);
    exit;
}

// Start output buffering IMMEDIATELY
if (ob_get_level() > 0) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}
ob_start();

// Set JSON header IMMEDIATELY - before any includes
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
}

// Shutdown function to catch ANY errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {
        // Clean all output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Force JSON header
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
        }
        
        // Clean error message - remove any special characters that might break JSON
        $errorMsg = preg_replace('/[^\x20-\x7E]/', '', $error['message']);
        $errorMsg = substr($errorMsg, 0, 200); // Limit length
        
        echo json_encode(['error' => 'Fatal error: ' . $errorMsg . ' in ' . basename($error['file']) . ':' . $error['line']]);
        exit;
    }
    
    // Also check if output buffer contains HTML (error page)
    $output = ob_get_contents();
    if ($output && (stripos($output, '<!DOCTYPE') !== false || stripos($output, '<html') !== false)) {
        ob_end_clean();
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        // Try to extract error message from HTML
        if (preg_match('/Fatal error[^<]*:?\s*([^<\n]+)/i', $output, $matches)) {
            $errorMsg = trim($matches[1]);
        } else {
            $errorMsg = 'Server returned HTML error page. Check PHP error logs.';
        }
        echo json_encode(['error' => 'Fatal error: ' . substr($errorMsg, 0, 200)]);
        exit;
    }
});

try {
    // Include config with output buffering and error suppression
    ob_start();
    $oldErrorReporting = error_reporting(0);
    $oldDisplayErrors = ini_set('display_errors', 0);
    
    require_once __DIR__ . '/../../config/config.php';
    
    // Override config.php error settings
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    
    $configOutput = ob_get_clean();
    error_reporting($oldErrorReporting);
    
    // Clear any config output
    if (!empty($configOutput) && trim($configOutput) !== '') {
        error_log("Config output detected: " . substr($configOutput, 0, 500));
        // Clear it
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
    }
    
    // Get database connection with error handling
    try {
        $db = getDB();
    } catch (Exception $e) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // GET request - return current data
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        try {
            $tableExists = $db->fetchOne("SHOW TABLES LIKE 'people_behind_school_content'");
            if ($tableExists === false || $tableExists === null) {
                echo json_encode([]);
                exit;
            }
            
            $content = $db->fetchOne("SELECT * FROM people_behind_school_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            if ($content !== false && $content !== null) {
                echo json_encode($content);
            } else {
                echo json_encode([]);
            }
            exit;
        } catch (Exception $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            http_response_code(500);
            echo json_encode(['error' => 'GET error: ' . $e->getMessage()]);
            exit;
        }
    } elseif ($method === 'POST' || $method === 'PUT') {
        // POST/PUT request - save data
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        // Check authentication
        try {
            // Clean any existing output before including auth
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start();
            
            // Suppress errors during auth include
            $oldErrorReporting = error_reporting(0);
            $oldDisplayErrors = ini_set('display_errors', 0);
            
            require_once __DIR__ . '/../../includes/auth.php';
            
            // Restore error settings
            error_reporting($oldErrorReporting);
            ini_set('display_errors', $oldDisplayErrors);
            
            $authOutput = ob_get_clean();
            
            // Check if auth.php outputted HTML (error page)
            if (!empty($authOutput) && (stripos($authOutput, '<!DOCTYPE') !== false || stripos($authOutput, '<html') !== false || stripos($authOutput, 'Fatal error') !== false)) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    http_response_code(500);
                    header('Content-Type: application/json; charset=utf-8');
                }
                // Try to extract error message
                $errorMsg = 'Authentication system error. Check PHP error logs.';
                if (preg_match('/Fatal error[^<]*:?\s*([^<\n]+)/i', $authOutput, $matches)) {
                    $errorMsg = 'Fatal error in auth: ' . trim(substr($matches[1], 0, 200));
                }
                echo json_encode(['error' => $errorMsg]);
                exit;
            }
            
            // Clear any non-HTML output from auth.php
            if (!empty($authOutput) && trim($authOutput) !== '') {
                error_log("Auth.php output detected: " . substr($authOutput, 0, 200));
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                ob_start();
            }
            
            $auth = getAuth();
            if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
        } catch (Exception $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['error' => 'Auth error: ' . $e->getMessage()]);
            exit;
        } catch (Error $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['error' => 'Auth fatal error: ' . $e->getMessage()]);
            exit;
        }
        
        // Ensure table exists with correct structure - CRITICAL
        try {
            $tableExists = $db->fetchOne("SHOW TABLES LIKE 'people_behind_school_content'");
            
            if ($tableExists === false || $tableExists === null) {
                // Create table from scratch
                $createSql = "CREATE TABLE people_behind_school_content (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title TEXT,
                    subtitle TEXT,
                    hero_image VARCHAR(500),
                    content TEXT,
                    video_url VARCHAR(500),
                    video_embed_code TEXT,
                    people TEXT,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $db->query($createSql);
                error_log("People Behind School table created successfully");
            } else {
                // Table exists - verify structure
                $columns = $db->fetchAll("SHOW COLUMNS FROM people_behind_school_content");
                $columnNames = array_column($columns, 'Field');
                
                // Ensure people column exists (most critical)
                if (!in_array('people', $columnNames)) {
                    try {
                        $db->query("ALTER TABLE people_behind_school_content ADD COLUMN people TEXT AFTER video_embed_code");
                        error_log("Added 'people' column to table");
                    } catch (Exception $e) {
                        error_log("Error adding people column: " . $e->getMessage());
                        // If column already exists with different type, try to modify it
                        if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                            try {
                                // Try to modify existing column to TEXT if it's JSON
                                $db->query("ALTER TABLE people_behind_school_content MODIFY COLUMN people TEXT");
                                error_log("Modified 'people' column to TEXT type");
                            } catch (Exception $e2) {
                                error_log("Error modifying people column: " . $e2->getMessage());
                            }
                        }
                    }
                } else {
                    // Column exists - check if it's the right type (TEXT, not JSON)
                    $peopleColumn = array_filter($columns, function($col) { return $col['Field'] === 'people'; });
                    if (!empty($peopleColumn)) {
                        $peopleCol = reset($peopleColumn);
                        if (stripos($peopleCol['Type'], 'json') !== false) {
                            // Column is JSON type, but we need TEXT for compatibility
                            try {
                                $db->query("ALTER TABLE people_behind_school_content MODIFY COLUMN people TEXT");
                                error_log("Modified 'people' column from JSON to TEXT type");
                            } catch (Exception $e) {
                                error_log("Error modifying people column type: " . $e->getMessage());
                                // Continue anyway - JSON columns can store text too
                            }
                        }
                    }
                }
                
                // Ensure other critical columns exist
                $criticalColumns = [
                    'is_active' => "ALTER TABLE people_behind_school_content ADD COLUMN is_active TINYINT(1) DEFAULT 1",
                    'created_at' => "ALTER TABLE people_behind_school_content ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
                    'updated_at' => "ALTER TABLE people_behind_school_content ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
                ];
                
                foreach ($criticalColumns as $colName => $alterSql) {
                    if (!in_array($colName, $columnNames)) {
                        try {
                            $db->query($alterSql);
                            error_log("Added column: " . $colName);
                        } catch (Exception $e) {
                            error_log("Error adding " . $colName . " column: " . $e->getMessage());
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("CRITICAL: Table setup failed: " . $e->getMessage());
            // Return error instead of silently failing
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            http_response_code(500);
            echo json_encode(['error' => 'Database setup error: ' . $e->getMessage()]);
            exit;
        }
        
        // Get input data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = [];
        
        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                http_response_code(400);
                echo json_encode(['error' => 'Empty JSON input']);
                exit;
            }
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
                exit;
            }
        } else {
            $input = $_POST;
        }
        
        if (empty($input)) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            http_response_code(400);
            echo json_encode(['error' => 'No data provided']);
            exit;
        }
        
        // Process people_json with validation
        $peopleJson = '[]';
        if (isset($input['people_json']) && !empty($input['people_json'])) {
            if (is_string($input['people_json'])) {
                $decoded = json_decode($input['people_json'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // Validate each person has required fields
                    $validatedPeople = [];
                    foreach ($decoded as $index => $person) {
                        $fullName = trim($person['full_name'] ?? '');
                        $title = trim($person['title'] ?? '');
                        $biography = trim($person['biography'] ?? '');
                        
                        if (empty($fullName) || empty($title) || empty($biography)) {
                            error_log("Person at index $index missing required fields: full_name=" . (!empty($fullName) ? 'set' : 'empty') . ", title=" . (!empty($title) ? 'set' : 'empty') . ", biography=" . (!empty($biography) ? 'set' : 'empty'));
                            continue; // Skip invalid person
                        }
                        
                        $validatedPeople[] = $person;
                    }
                    
                    if (empty($validatedPeople) && !empty($decoded)) {
                        while (ob_get_level() > 0) {
                            ob_end_clean();
                        }
                        http_response_code(400);
                        echo json_encode(['error' => 'All people entries are missing required fields (Full Name, Title/Role, or Biography). Please fill in all required fields.']);
                        exit;
                    }
                    
                    $peopleJson = json_encode($validatedPeople);
                } else {
                    error_log("Invalid JSON in people_json: " . json_last_error_msg());
                    while (ob_get_level() > 0) {
                        ob_end_clean();
                    }
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid people data format. Please try refreshing the page and adding people again.']);
                    exit;
                }
            } else {
                $peopleJson = json_encode($input['people_json']);
            }
        } else {
            // No people_json provided - this is okay, just save empty array
            $peopleJson = '[]';
        }
        
        // Build fields and values
        $allowedFields = ['title', 'subtitle', 'content', 'hero_image', 'video_url', 'video_embed_code'];
        $fields = ['is_active', 'people'];
        $values = [1, $peopleJson];
        
        foreach ($input as $key => $value) {
            if ($key !== 'people_json' && $key !== 'people' && $key !== 'id' && $key !== 'is_active' && in_array($key, $allowedFields)) {
                $fields[] = $key;
                $values[] = ($value === '' || $value === null) ? null : $value;
            }
        }
        
        // Deactivate old records
        try {
            $existing = $db->fetchOne("SELECT id FROM people_behind_school_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
            if ($existing !== false && $existing !== null && isset($existing['id'])) {
                $db->update("UPDATE people_behind_school_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
            }
        } catch (Exception $e) {
            error_log("Deactivate error: " . $e->getMessage());
        }
        
        // Insert new record
        $placeholders = array_fill(0, count($values), '?');
        $sql = "INSERT INTO people_behind_school_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
        
        try {
            // Clean output buffers before insert
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start();
            
            // Validate we have the required fields
            if (count($fields) !== count($values)) {
                throw new Exception('Field count (' . count($fields) . ') does not match value count (' . count($values) . ')');
            }
            
            // Log the SQL and values for debugging (without sensitive data)
            error_log("People Behind School Insert - Fields: " . implode(', ', $fields));
            error_log("People Behind School Insert - Values count: " . count($values));
            error_log("People Behind School Insert - People JSON length: " . strlen($peopleJson));
            
            // Ensure people_json is always a string
            if (!is_string($peopleJson)) {
                $peopleJson = json_encode($peopleJson);
            }
            
            // Find the index of 'people' in fields array to update the value
            $peopleIndex = array_search('people', $fields);
            if ($peopleIndex !== false) {
                $values[$peopleIndex] = $peopleJson;
            }
            
            $id = $db->insert($sql, $values);
            
            if (!$id || $id === false || $id === 0) {
                // Check if it's actually a valid insert (sometimes 0 is returned for updates)
                // But for inserts, we should get a positive ID
                error_log("Insert returned invalid ID: " . var_export($id, true));
                throw new Exception('Insert failed - no valid ID returned. Returned: ' . var_export($id, true));
            }
            
            // Clean all output buffers before sending JSON
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Ensure headers are set
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'People behind school content saved successfully']);
            exit;
            
        } catch (PDOException $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            error_log("People Behind School PDO Error: " . $e->getMessage() . " | SQL: " . $sql);
            http_response_code(500);
            $errorMsg = 'Database error: ' . $e->getMessage();
            // Provide more helpful error messages
            if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
                if (strpos($e->getMessage(), '42S22') !== false) {
                    $errorMsg = 'Database column error. The table structure may be incorrect. Please check the database.';
                } elseif (strpos($e->getMessage(), '42S21') !== false) {
                    $errorMsg = 'Database column duplicate error. Please check the table structure.';
                } elseif (strpos($e->getMessage(), 'HY000') !== false) {
                    $errorMsg = 'Database constraint error: ' . $e->getMessage();
                }
            }
            echo json_encode(['error' => $errorMsg]);
            exit;
        } catch (Exception $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            error_log("People Behind School Exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
            exit;
        }
        
    } else {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
} catch (Exception $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    exit;
} catch (Error $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    $errorMsg = preg_replace('/[^\x20-\x7E]/', '', $e->getMessage());
    echo json_encode(['error' => 'Fatal error: ' . substr($errorMsg, 0, 200)]);
    exit;
}

// Final safety check - if we reach here without exiting, something went wrong
while (ob_get_level() > 0) {
    ob_end_clean();
}
if (!headers_sent()) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
}
echo json_encode(['error' => 'Unexpected script termination. No response was sent.']);
exit;
