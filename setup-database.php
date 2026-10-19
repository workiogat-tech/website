<?php
/**
 * Database Setup Script
 * Run this once to set up the database
 * Usage: php setup-database.php
 */

require_once __DIR__ . '/config/database.php';

echo "Setting up Excellence Academy Database...\n\n";

try {
    $db = getDB();
    
    // Read and execute schema file
    $schemaFile = __DIR__ . '/database/schema.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    
    // Split by semicolons (basic SQL parsing)
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    $conn = $db->getConnection();
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $conn->exec($statement);
            echo "✓ Executed statement\n";
        } catch (PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✅ Database setup completed successfully!\n";
    echo "📋 Database: excellence_academy\n";
    echo "👤 User: root\n";
    echo "👨‍💼 Admin: admin / admin123\n";
    echo "\n⚠️  IMPORTANT: Change the default admin password after first login!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
