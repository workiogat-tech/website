<?php
/**
 * Setup script to create parents_content table for Resources page
 */
require_once __DIR__ . '/config/config.php';

$db = getDB();

try {
    // Check if table exists
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'parents_content'");
    
    if (!$tableExists) {
        echo "Creating parents_content table...\n";
        $db->query("CREATE TABLE IF NOT EXISTS parents_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hero_caption VARCHAR(255) DEFAULT 'Resources & Information',
            hero_title VARCHAR(255) DEFAULT 'Resources',
            hero_image VARCHAR(500),
            section_title VARCHAR(255) DEFAULT 'Resources & Information',
            section_subtitle TEXT,
            resource_detail_sections TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "✓ Table created successfully!\n";
    } else {
        echo "Table already exists. Checking columns...\n";
        
        // Ensure all columns exist
        $columns = $db->fetchAll("SHOW COLUMNS FROM parents_content");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'hero_caption' => "ALTER TABLE parents_content ADD COLUMN hero_caption VARCHAR(255) DEFAULT 'Resources & Information' AFTER id",
            'hero_title' => "ALTER TABLE parents_content ADD COLUMN hero_title VARCHAR(255) DEFAULT 'Resources' AFTER hero_caption",
            'hero_image' => "ALTER TABLE parents_content ADD COLUMN hero_image VARCHAR(500) AFTER hero_title",
            'section_title' => "ALTER TABLE parents_content ADD COLUMN section_title VARCHAR(255) DEFAULT 'Resources & Information' AFTER hero_image",
            'section_subtitle' => "ALTER TABLE parents_content ADD COLUMN section_subtitle TEXT AFTER section_title",
            'resource_detail_sections' => "ALTER TABLE parents_content ADD COLUMN resource_detail_sections TEXT AFTER section_subtitle"
        ];
        
        foreach ($requiredColumns as $column => $sql) {
            if (!in_array($column, $columnNames)) {
                echo "Adding column: $column...\n";
                $db->query($sql);
                echo "✓ Column $column added!\n";
            }
        }
        
        echo "✓ All columns are present!\n";
    }
    
    echo "\nSetup completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
