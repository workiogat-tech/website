<?php
/**
 * Fix Admission Levels - Clear old format and ensure new format is used
 * Run this script once to migrate from old format to new format
 */
require_once __DIR__ . '/config/config.php';

$db = getDB();

try {
    // Get all admission content records
    $records = $db->fetchAll("SELECT * FROM admission_content ORDER BY created_at DESC");
    
    echo "Found " . count($records) . " admission content record(s)\n\n";
    
    foreach ($records as $record) {
        echo "Processing record ID: " . $record['id'] . "\n";
        
        // Check if admission_levels exists and has data
        $hasNewFormat = isset($record['admission_levels']) && $record['admission_levels'] !== null;
        $admissionLevels = [];
        
        if ($hasNewFormat) {
            $admissionLevels = is_string($record['admission_levels']) 
                ? json_decode($record['admission_levels'], true) 
                : $record['admission_levels'];
            if (!is_array($admissionLevels)) $admissionLevels = [];
            
            echo "  - New format found: " . count($admissionLevels) . " level(s)\n";
            if (!empty($admissionLevels)) {
                foreach ($admissionLevels as $idx => $level) {
                    echo "    Level " . ($idx + 1) . ": " . ($level['title'] ?? 'No title') . " (Icon: " . ($level['icon'] ?? 'None') . ")\n";
                }
            }
        } else {
            echo "  - No new format found\n";
        }
        
        // Check old format
        $hasOldFormat = false;
        $olevelContent = [];
        $alevelContent = [];
        
        if (!empty($record['olevel_content'])) {
            $olevelContent = is_string($record['olevel_content']) 
                ? json_decode($record['olevel_content'], true) 
                : $record['olevel_content'];
            if (is_array($olevelContent) && (!empty($olevelContent['title']) || !empty($olevelContent['description']))) {
                $hasOldFormat = true;
                echo "  - Old O'LEVEL format found: " . ($olevelContent['title'] ?? 'No title') . "\n";
            }
        }
        
        if (!empty($record['alevel_content'])) {
            $alevelContent = is_string($record['alevel_content']) 
                ? json_decode($record['alevel_content'], true) 
                : $record['alevel_content'];
            if (is_array($alevelContent) && (!empty($alevelContent['title']) || !empty($alevelContent['description']))) {
                $hasOldFormat = true;
                echo "  - Old A'LEVEL format found: " . ($alevelContent['title'] ?? 'No title') . "\n";
            }
        }
        
        // If new format is empty but old format exists, convert it
        if (empty($admissionLevels) && $hasOldFormat) {
            echo "  - Converting old format to new format...\n";
            
            $newLevels = [];
            
            if (!empty($olevelContent['title']) || !empty($olevelContent['description'])) {
                $newLevels[] = [
                    'icon' => '📚',
                    'heading' => "O'LEVEL Information",
                    'title' => $olevelContent['title'] ?? "O'LEVEL",
                    'description' => $olevelContent['description'] ?? ''
                ];
            }
            
            if (!empty($alevelContent['title']) || !empty($alevelContent['description'])) {
                $newLevels[] = [
                    'icon' => '📚',
                    'heading' => "A'LEVEL Information",
                    'title' => $alevelContent['title'] ?? "A'LEVEL",
                    'description' => $alevelContent['description'] ?? ''
                ];
            }
            
            // Update the record
            $db->update(
                "UPDATE admission_content SET admission_levels = ?, olevel_content = NULL, alevel_content = NULL WHERE id = ?",
                [json_encode($newLevels), $record['id']]
            );
            
            echo "  - ✅ Converted and cleared old format\n";
        } elseif (!empty($admissionLevels) && $hasOldFormat) {
            // New format exists but old format also exists - clear old format
            echo "  - Clearing old format fields (new format is being used)...\n";
            $db->update(
                "UPDATE admission_content SET olevel_content = NULL, alevel_content = NULL WHERE id = ?",
                [$record['id']]
            );
            echo "  - ✅ Cleared old format\n";
        } else {
            echo "  - No action needed\n";
        }
        
        echo "\n";
    }
    
    echo "✅ Migration complete!\n";
    echo "\nNext steps:\n";
    echo "1. Go to Admin Panel → Admission Management\n";
    echo "2. Edit your levels (icons, headings, titles, descriptions)\n";
    echo "3. Click 'Save Changes'\n";
    echo "4. Refresh the admission page to see your changes\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
