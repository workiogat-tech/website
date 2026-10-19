<?php
/**
 * Map Settings Tab
 */
$mapSettings = $db->fetchOne("SELECT * FROM map_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>📍 Map Settings</h2>
    <p style="margin-bottom: 30px; color: #666;">Configure map location and settings.</p>
    
    <form method="POST" action="api/content/map-settings.php" id="mapForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="form-group">
                <label class="form-label">Latitude</label>
                <input type="number" step="0.00000001" name="latitude" class="form-input" 
                       value="<?php echo htmlspecialchars($mapSettings['latitude'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Longitude</label>
                <input type="number" step="0.00000001" name="longitude" class="form-input" 
                       value="<?php echo htmlspecialchars($mapSettings['longitude'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Zoom Level</label>
                <input type="number" min="1" max="20" name="zoom" class="form-input" 
                       value="<?php echo htmlspecialchars($mapSettings['zoom'] ?? 15); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Marker Title</label>
            <input type="text" name="marker_title" class="form-input" 
                   value="<?php echo htmlspecialchars($mapSettings['marker_title'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Marker Description</label>
            <textarea name="marker_description" class="form-input" rows="3"><?php echo htmlspecialchars($mapSettings['marker_description'] ?? ''); ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
    </form>
</div>
