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
            <div class="form-group">
                <label class="form-label">Session Timeout (minutes)</label>
                <input type="number" class="form-input" value="30">
            </div>
            <div class="form-group">
                <label class="form-label">Max Upload Size (MB)</label>
                <input type="number" class="form-input" value="50">
            </div>
        </div>
    </div>
    
    <button class="btn btn-primary" style="margin-top: 20px;">💾 Save Settings</button>
</div>
