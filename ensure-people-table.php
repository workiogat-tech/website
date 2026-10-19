<?php
/**
 * Ensure People Behind School table exists and is correct
 * Run this first to fix any database issues
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Ensuring People Behind School Table Exists</h1>";

$db = getDB();

try {
    // Drop and recreate table to ensure it's correct
    echo "<h2>Step 1: Checking/Creating Table</h2>";
    
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'people_behind_school_content'");
    
    if ($tableExists) {
        echo "<p>Table exists. Verifying structure...</p>";
        
        // Drop and recreate to ensure correct structure
        echo "<p>Recreating table with correct structure...</p>";
        $db->query("DROP TABLE IF EXISTS people_behind_school_content");
    }
    
    // Create table with correct structure
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
    echo "<p style='color: green; font-weight: bold;'>✅ Table created successfully!</p>";
    
    // Verify structure
    echo "<h2>Step 2: Verifying Table Structure</h2>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM people_behind_school_content");
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $requiredFields = ['id', 'title', 'subtitle', 'hero_image', 'content', 'video_url', 'video_embed_code', 'people', 'is_active', 'created_at', 'updated_at'];
    $foundFields = [];
    
    foreach ($columns as $col) {
        $foundFields[] = $col['Field'];
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for missing fields
    $missing = array_diff($requiredFields, $foundFields);
    if (empty($missing)) {
        echo "<p style='color: green; font-weight: bold;'>✅ All required fields exist!</p>";
    } else {
        echo "<p style='color: red;'>❌ Missing fields: " . implode(', ', $missing) . "</p>";
    }
    
    // Test insert
    echo "<h2>Step 3: Testing Insert</h2>";
    
    $testPeople = json_encode([
        [
            'full_name' => 'Robert Seki',
            'title' => 'Principal',
            'biography' => 'Robert Seki is a dedicated educator with years of experience.',
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
    
    echo "<p><strong>Test SQL:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($sql) . "</pre>";
    echo "<p><strong>Values:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    foreach ($fields as $i => $field) {
        $val = $values[$i];
        if (strlen($val) > 100) {
            $val = substr($val, 0, 100) . '...';
        }
        echo $field . " = " . htmlspecialchars($val) . "\n";
    }
    echo "</pre>";
    
    $id = $db->insert($sql, $values);
    
    if ($id && $id > 0) {
        echo "<p style='color: green; font-size: 20px; font-weight: bold;'>✅✅✅ SUCCESS! Test insert worked! ID: " . $id . "</p>";
        
        // Verify
        $saved = $db->fetchOne("SELECT * FROM people_behind_school_content WHERE id = ?", [$id]);
        if ($saved) {
            $people = json_decode($saved['people'], true);
            if (is_array($people) && count($people) > 0 && $people[0]['full_name'] === 'Robert Seki') {
                echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅✅✅ VERIFIED: Robert Seki is saved correctly!</p>";
                echo "<ul>";
                echo "<li><strong>Name:</strong> " . htmlspecialchars($people[0]['full_name']) . "</li>";
                echo "<li><strong>Title:</strong> " . htmlspecialchars($people[0]['title']) . "</li>";
                echo "<li><strong>Biography:</strong> " . htmlspecialchars($people[0]['biography']) . "</li>";
                echo "</ul>";
            }
        }
        
        // Keep the record
        echo "<p style='color: blue;'><strong>Test record kept in database (ID: " . $id . "). You should see Robert Seki in the admin panel now.</strong></p>";
        
    } else {
        echo "<p style='color: red; font-size: 20px; font-weight: bold;'>❌❌❌ FAILED! Insert returned: " . var_export($id, true) . "</p>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✅ Database Setup Complete!</h2>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li><a href='admin.php?tab=people-behind-school' target='_blank'>Go to Admin Panel</a> - You should see Robert Seki</li>";
    echo "<li>Try adding another person via the form</li>";
    echo "<li>If it still fails, check browser console (F12) for errors</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERROR:</h2>";
    echo "<p style='color: red; font-size: 16px;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre style='background: #fee; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
