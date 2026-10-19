<?php
/**
 * Setup Homepage Sections Database Tables
 * Run this script once to create all necessary tables for homepage content management
 */
require_once __DIR__ . '/config/config.php';

$db = getDB();

echo "<h1>Setting up Homepage Sections Database Tables</h1>";
echo "<pre>";

try {
    // Read and execute the SQL file
    $sqlFile = __DIR__ . '/database/homepage-sections-schema.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments and split by semicolon
    $lines = explode("\n", $sql);
    $cleanSql = '';
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comment lines
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        $cleanSql .= $line . "\n";
    }
    
    // Split by semicolon
    $statements = array_filter(array_map('trim', explode(';', $cleanSql)));
    
    $successCount = 0;
    $errorCount = 0;
    $createStatements = [];
    $insertStatements = [];
    
    // Separate CREATE and INSERT statements
    foreach ($statements as $statement) {
        if (empty($statement)) {
            continue;
        }
        
        $upperStatement = strtoupper(trim($statement));
        if (strpos($upperStatement, 'CREATE TABLE') !== false) {
            $createStatements[] = $statement . ';';
        } elseif (strpos($upperStatement, 'INSERT INTO') !== false) {
            $insertStatements[] = $statement . ';';
        }
    }
    
    echo "Found " . count($createStatements) . " CREATE statements and " . count($insertStatements) . " INSERT statements\n\n";
    
    // First, execute all CREATE TABLE statements
    echo "Creating tables...\n";
    foreach ($createStatements as $statement) {
        try {
            $db->query($statement);
            $successCount++;
            // Extract table name for better logging
            if (preg_match('/CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}\n";
            } else {
                echo "✓ Executed CREATE statement\n";
            }
        } catch (Exception $e) {
            // Check if it's a "table already exists" error (which is OK)
            if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "⚠ Table already exists (skipping)\n";
            } else {
                $errorCount++;
                echo "✗ Error creating table: " . $e->getMessage() . "\n";
                echo "   Statement preview: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    // Then, execute all INSERT statements
    echo "\nInserting default data...\n";
    foreach ($insertStatements as $statement) {
        try {
            $db->query($statement);
            $successCount++;
            // Extract table name for better logging
            if (preg_match('/INSERT INTO `?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Inserted data into: {$matches[1]}\n";
            } else {
                echo "✓ Executed INSERT statement\n";
            }
        } catch (Exception $e) {
            // Check if it's a duplicate key error (which is OK for ON DUPLICATE KEY UPDATE)
            if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                echo "⚠ Data already exists (skipping)\n";
            } else {
                $errorCount++;
                echo "✗ Error inserting data: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Setup Complete!\n";
    echo "Successfully executed: $successCount statements\n";
    if ($errorCount > 0) {
        echo "Errors encountered: $errorCount\n";
    }
    echo "========================================\n";
    echo "\n";
    echo "You can now:\n";
    echo "1. Go to admin.php?tab=homepage-sections to manage homepage content\n";
    echo "2. All homepage sections are now connected to the admin panel\n";
    
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>
