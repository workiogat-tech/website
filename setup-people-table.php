<?php
/**
 * Setup People Behind School Table - Ensures table exists with correct structure
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Setting Up People Behind School Table</h1>";

$db = getDB();

try {
    // Check if table exists
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'people_behind_school_content'");
    
    if (!$tableExists) {
        echo "<p>Table does not exist. Creating...</p>";
        
        $db->query("CREATE TABLE IF NOT EXISTS people_behind_school_content (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<p style='color: green;'>✅ Table created successfully!</p>";
    } else {
        echo "<p style='color: green;'>✅ Table already exists.</p>";
    }
    
    // Check table structure
    echo "<h2>Table Structure:</h2>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM people_behind_school_content");
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $hasPeopleColumn = false;
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        
        if ($col['Field'] === 'people') {
            $hasPeopleColumn = true;
        }
    }
    echo "</table>";
    
    // Ensure people column exists
    if (!$hasPeopleColumn) {
        echo "<p>Adding 'people' column...</p>";
        $db->query("ALTER TABLE people_behind_school_content ADD COLUMN people TEXT AFTER video_embed_code");
        echo "<p style='color: green;'>✅ 'people' column added!</p>";
    } else {
        echo "<p style='color: green;'>✅ 'people' column exists.</p>";
    }
    
    // Test insert
    echo "<h2>Testing Insert:</h2>";
    
    // Deactivate old records
    $existing = $db->fetchOne("SELECT id FROM people_behind_school_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    if ($existing) {
        $db->update("UPDATE people_behind_school_content SET is_active = 0 WHERE id = ?", [$existing['id']]);
        echo "<p>Deactivated old record (ID: " . $existing['id'] . ")</p>";
    }
    
    // Insert test data
    $testPeople = json_encode([
        [
            'full_name' => 'Robert Seki',
            'title' => 'Principal',
            'biography' => 'Robert Seki is a dedicated educator.',
            'photo' => '',
            'additional_role' => '',
            'email' => '',
            'phone' => ''
        ]
    ]);
    
    $fields = ['is_active', 'people', 'title', 'subtitle'];
    $values = [1, $testPeople, 'People Behind This School', 'Meet the Visionaries'];
    
    $placeholders = array_fill(0, count($values), '?');
    $sql = "INSERT INTO people_behind_school_content (" . implode(', ', $fields) . ", created_at) VALUES (" . implode(', ', $placeholders) . ", NOW())";
    
    echo "<p><strong>SQL:</strong> <code>" . htmlspecialchars($sql) . "</code></p>";
    echo "<p><strong>Values count:</strong> " . count($values) . "</p>";
    
    $id = $db->insert($sql, $values);
    
    if ($id && $id > 0) {
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ SUCCESS! Test record inserted with ID: " . $id . "</p>";
        
        // Verify
        $saved = $db->fetchOne("SELECT * FROM people_behind_school_content WHERE id = ?", [$id]);
        if ($saved) {
            $people = json_decode($saved['people'], true);
            if (is_array($people) && count($people) > 0) {
                echo "<p style='color: green;'>✅ Data verified! Robert Seki is saved.</p>";
                echo "<p><strong>Name:</strong> " . htmlspecialchars($people[0]['full_name']) . "</p>";
            }
        }
        
        // Clean up test record
        $db->delete("DELETE FROM people_behind_school_content WHERE id = ?", [$id]);
        echo "<p>Test record cleaned up.</p>";
        
    } else {
        echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ FAILED! Insert returned: " . var_export($id, true) . "</p>";
    }
    
    echo "<hr>";
    echo "<h2>✅ Table Setup Complete!</h2>";
    echo "<p><a href='admin.php?tab=people-behind-school'>Go to Admin Panel</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ ERROR:</p>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
