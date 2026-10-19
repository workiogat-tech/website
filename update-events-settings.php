<?php
/**
 * Update existing activities_settings to use "Events" instead of "Activities"
 * Run this file in your browser: http://localhost/2026sjs/update-events-settings.php
 */
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config/config.php';

$db = getDB();
$updated = false;
$errors = [];

try {
    // Check if table exists
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'activities_settings'");
    
    if ($tableExists) {
        // Get current settings
        $current = $db->fetchOne("SELECT * FROM activities_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        
        if ($current) {
            // Update title
            if ($current['title'] === 'Activities' || strpos($current['title'], 'Activities') !== false) {
                $db->query("UPDATE activities_settings SET title = 'Events' WHERE id = ?", [$current['id']]);
                $updated = true;
            }
            
            // Update subtitle - replace "Activities" with "Events"
            if (!empty($current['subtitle']) && strpos($current['subtitle'], 'Activities') !== false) {
                $newSubtitle = str_replace('Activities', 'Events', $current['subtitle']);
                $newSubtitle = str_replace('School Activities', 'School Events', $newSubtitle);
                $db->query("UPDATE activities_settings SET subtitle = ? WHERE id = ?", [$newSubtitle, $current['id']]);
                $updated = true;
            }
        } else {
            // No active settings found, create default
            $db->insert("INSERT INTO activities_settings (title, subtitle, is_active) VALUES (?, ?, ?)", 
                ['Events', 'Upcoming Events', 1]);
            $updated = true;
        }
    } else {
        $errors[] = "activities_settings table does not exist. It will be created when you save settings through the admin panel.";
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Events Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #10b981;
            background: #d1fae5;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .error {
            color: #dc2626;
            background: #fee2e2;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .info {
            color: #6b7280;
            background: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }
        a {
            color: #2563eb;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Events Settings</h1>
        
        <?php if ($updated): ?>
            <div class="success">
                <strong>✓ Success!</strong> Database updated successfully! All 'Activities' references have been changed to 'Events'.
            </div>
            <p>You can now:</p>
            <ul>
                <li><a href="events.php">View the Events page</a> to see the changes</li>
                <li><a href="admin.php?tab=activities">Go to Admin Panel</a> to verify the settings</li>
            </ul>
        <?php elseif (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="info">
                <p>No updates were needed. The settings are already using "Events".</p>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <p><strong>Note:</strong> If you still see "Activities" on the page, try:</p>
            <ol>
                <li>Clear your browser cache (Ctrl+F5 or Cmd+Shift+R)</li>
                <li>Refresh the Events page</li>
                <li>Or update manually through <a href="admin.php?tab=activities">Admin Panel → Events Management</a></li>
            </ol>
        </div>
    </div>
</body>
</html>
