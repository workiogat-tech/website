<?php
/**
 * Settings Tab
 */
?>
<div class="content-card">
    <h2>⚙️ Settings</h2>
    <p style="margin-bottom: 30px; color: #666;">General system settings and configuration.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h3 style="margin-bottom: 15px;">System Information</h3>
            <p style="color: #666; margin-bottom: 10px;"><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
            <p style="color: #666; margin-bottom: 10px;"><strong>Database:</strong> <?php echo DB_NAME; ?></p>
            <p style="color: #666;"><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h3 style="margin-bottom: 15px;">Security</h3>
            <?php
            $siteSettings = [];
            try {
                $siteSettings = $db->fetchOne("SELECT * FROM site_settings ORDER BY id DESC LIMIT 1") ?: [];
            } catch (Exception $e) {
                $siteSettings = [];
            }
            ?>
            <form method="POST" action="api/content/settings.php" id="settingsForm" onsubmit="return handleAdminFormSubmit(event, this);">
                <div class="form-group">
                    <label class="form-label">Session Timeout (minutes)</label>
                    <input type="number" name="session_timeout" class="form-input" min="5" max="1440" value="<?php echo htmlspecialchars($siteSettings['session_timeout'] ?? 30); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Max Upload Size (MB)</label>
                    <input type="number" name="max_upload_size" class="form-input" min="1" max="500" value="<?php echo htmlspecialchars($siteSettings['max_upload_size'] ?? 50); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save settings</button>
            </form>
            <p style="color:#78716c;font-size:13px;margin-top:12px;">These numbers are stored. The hosting server still sets the real session length and the largest file it will accept.</p>
        </div>
    </div>
</div>
