<?php
/**
 * Setup Admission Content Table
 * Creates the admission_content table if it doesn't exist
 */
require_once __DIR__ . '/config/config.php';

$db = getDB();

try {
    // Check if table exists
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'admission_content'");
    
    if (!$tableExists) {
        // Create admission_content table
        $db->query("
            CREATE TABLE IF NOT EXISTS admission_content (
                id INT AUTO_INCREMENT PRIMARY KEY,
                hero_image VARCHAR(500),
                olevel_content JSON,
                alevel_content JSON,
                fee_structure JSON,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✅ Admission content table created successfully!\n";
    } else {
        echo "✅ Admission content table already exists.\n";
        
        // Check and add missing columns
        $columns = $db->fetchAll("SHOW COLUMNS FROM admission_content");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'hero_image' => 'VARCHAR(500)',
            'olevel_content' => 'JSON',
            'alevel_content' => 'JSON',
            'fee_structure' => 'JSON'
        ];
        
        foreach ($requiredColumns as $column => $type) {
            if (!in_array($column, $columnNames)) {
                $db->query("ALTER TABLE admission_content ADD COLUMN $column $type");
                echo "✅ Added column: $column\n";
            }
        }
    }
    
    echo "\n✅ Admission table is ready!\n";
    echo "You can now use the Admission tab in the admin panel.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
