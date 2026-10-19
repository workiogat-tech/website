<?php
/**
 * Complete Database Setup Script
 * Creates all required tables with proper structure
 * Run via browser or CLI: php setup-all-tables.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Database Setup</title>';
    echo '<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}';
    echo '.success{color:#10b981;}.error{color:#ef4444;}.warning{color:#f59e0b;}.info{color:#3b82f6;}';
    echo 'pre{background:#fff;padding:10px;border-radius:5px;border:1px solid #ddd;}</style></head><body>';
    echo '<h1>🔧 Database Setup</h1><pre>';
}

function output($message, $type = 'info') {
    global $isCli;
    $prefix = $isCli ? '' : "<span class='$type'>";
    $suffix = $isCli ? '' : '</span>';
    $symbol = '';
    switch($type) {
        case 'success': $symbol = '✅'; break;
        case 'error': $symbol = '❌'; break;
        case 'warning': $symbol = '⚠️'; break;
        default: $symbol = 'ℹ️';
    }
    echo "$prefix$symbol $message$suffix\n";
}

try {
    require_once __DIR__ . '/config/database.php';
    
    output("Connecting to database...", 'info');
    $db = getDB();
    output("Connected successfully!", 'success');
    
    $conn = $db->getConnection();
    
    // Read and execute main schema
    output("\nReading schema file...", 'info');
    $schemaFile = __DIR__ . '/database/schema-sjs.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    // Remove USE statement as we're already connected to the database
    $schema = preg_replace('/^USE\s+\w+;/mi', '', $schema);
    
    // Split by semicolons, but be careful with JSON and strings
    $statements = [];
    $current = '';
    $inString = false;
    $stringChar = '';
    
    for ($i = 0; $i < strlen($schema); $i++) {
        $char = $schema[$i];
        $current .= $char;
        
        if (!$inString && ($char === '"' || $char === "'")) {
            $inString = true;
            $stringChar = $char;
        } elseif ($inString && $char === $stringChar && $schema[$i-1] !== '\\') {
            $inString = false;
        } elseif (!$inString && $char === ';') {
            $stmt = trim($current);
            if (!empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt)) {
                $statements[] = $stmt;
            }
            $current = '';
        }
    }
    
    output("Found " . count($statements) . " SQL statements", 'info');
    
    $successCount = 0;
    $errorCount = 0;
    $skipCount = 0;
    
    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $conn->exec($statement);
            $successCount++;
            if (preg_match('/CREATE TABLE/i', $statement)) {
                preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                output("Created table: $tableName", 'success');
            }
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, 'already exists') !== false || 
                strpos($errorMsg, 'Duplicate') !== false) {
                $skipCount++;
                // Silently skip
            } else {
                $errorCount++;
                output("Error: " . substr($errorMsg, 0, 100), 'error');
            }
        }
    }
    
    // Now add missing columns to existing tables
    output("\nChecking and adding missing columns...", 'info');
    
    // hero_content table columns
    $heroColumns = [
        'headline' => "ALTER TABLE hero_content ADD COLUMN headline TEXT AFTER subtitle",
        'subheadline' => "ALTER TABLE hero_content ADD COLUMN subheadline TEXT AFTER headline",
        'background_size' => "ALTER TABLE hero_content ADD COLUMN background_size VARCHAR(20) DEFAULT 'contain' AFTER slide_mode",
        'background_position' => "ALTER TABLE hero_content ADD COLUMN background_position VARCHAR(20) DEFAULT 'center' AFTER background_size",
        'image_scale' => "ALTER TABLE hero_content ADD COLUMN image_scale DECIMAL(3,1) DEFAULT 1.0 AFTER background_position",
        'headline_color' => "ALTER TABLE hero_content ADD COLUMN headline_color VARCHAR(50) DEFAULT '#ffffff' AFTER image_scale",
        'subheadline_color' => "ALTER TABLE hero_content ADD COLUMN subheadline_color VARCHAR(50) DEFAULT '#ffffff' AFTER headline_color",
        'description_color' => "ALTER TABLE hero_content ADD COLUMN description_color VARCHAR(50) DEFAULT '#ffffff' AFTER subheadline_color"
    ];
    
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'hero_content'");
    if ($tableExists) {
        $columns = $db->fetchAll("SHOW COLUMNS FROM hero_content");
        $columnNames = array_column($columns, 'Field');
        
        foreach ($heroColumns as $colName => $sql) {
            if (!in_array($colName, $columnNames)) {
                try {
                    $conn->exec($sql);
                    output("Added column '{$colName}' to hero_content", 'success');
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate') === false) {
                        output("Error adding column '{$colName}': " . substr($e->getMessage(), 0, 80), 'warning');
                    }
                }
            }
        }
    }
    
    // about_content table columns
    $aboutColumns = [
        'hero_image' => "ALTER TABLE about_content ADD COLUMN hero_image VARCHAR(500) AFTER image",
        'about_image_2' => "ALTER TABLE about_content ADD COLUMN about_image_2 VARCHAR(500) AFTER hero_image",
        'hero_caption' => "ALTER TABLE about_content ADD COLUMN hero_caption VARCHAR(200) AFTER hero_image",
        'mission_title' => "ALTER TABLE about_content ADD COLUMN mission_title VARCHAR(200) AFTER content",
        'mission_content' => "ALTER TABLE about_content ADD COLUMN mission_content TEXT AFTER mission_title",
        'mission_paragraph2' => "ALTER TABLE about_content ADD COLUMN mission_paragraph2 TEXT AFTER mission_content",
        'core_values' => "ALTER TABLE about_content ADD COLUMN core_values JSON AFTER mission_paragraph2",
        'statistics' => "ALTER TABLE about_content ADD COLUMN statistics JSON AFTER core_values"
    ];
    
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'about_content'");
    if ($tableExists) {
        $columns = $db->fetchAll("SHOW COLUMNS FROM about_content");
        $columnNames = array_column($columns, 'Field');
        
        foreach ($aboutColumns as $colName => $sql) {
            if (!in_array($colName, $columnNames)) {
                try {
                    $conn->exec($sql);
                    output("Added column '{$colName}' to about_content", 'success');
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate') === false) {
                        output("Error adding column '{$colName}': " . substr($e->getMessage(), 0, 80), 'warning');
                    }
                }
            }
        }
    }
    
    // Summary
    output("\n" . str_repeat("=", 50), 'info');
    output("SETUP COMPLETE", 'info');
    output(str_repeat("=", 50), 'info');
    output("Successfully executed: $successCount statements", 'success');
    output("Skipped (already exists): $skipCount statements", 'info');
    if ($errorCount > 0) {
        output("Errors: $errorCount", 'error');
    }
    output("\n✅ Database setup completed!", 'success');
    output("You can now use the admin panel.", 'info');
    
} catch (Exception $e) {
    output("Fatal error: " . $e->getMessage(), 'error');
    if (!$isCli) {
        echo '</pre></body></html>';
    }
    exit(1);
}

if (!$isCli) {
    echo '</pre></body></html>';
}
