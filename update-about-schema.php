<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

try {
    // Check and add columns one by one
    $columns = [
        'hero_image' => "VARCHAR(500)",
        'mission_title' => "VARCHAR(200) DEFAULT 'Our Mission'",
        'mission_content' => "TEXT",
        'mission_paragraph2' => "TEXT",
        'core_values' => "JSON",
        'statistics' => "JSON"
    ];
    
    foreach ($columns as $column => $definition) {
        try {
            $exists = $db->fetchOne("SHOW COLUMNS FROM about_content LIKE '$column'");
            if (!$exists) {
                $db->query("ALTER TABLE about_content ADD COLUMN $column $definition");
                echo "✓ Added column: $column\n";
            } else {
                echo "⚠ Column already exists: $column\n";
            }
        } catch (Exception $e) {
            echo "✗ Error adding $column: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nSchema update complete!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
