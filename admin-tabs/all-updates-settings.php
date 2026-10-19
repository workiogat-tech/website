<?php
$settings = $db->fetchOne("SELECT * FROM all_updates_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>📰 All Updates Settings</h2>
    <p style="margin-bottom: 30px; color: #666;">Configure settings for the All Updates page.</p>
    
    <form method="POST" action="api/content/all-updates-settings.php" id="allUpdatesForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-input" 
                   value="<?php echo htmlspecialchars($settings['title'] ?? 'All Updates'); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Subtitle</label>
            <textarea name="subtitle" class="form-input" rows="3"><?php echo htmlspecialchars($settings['subtitle'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Hero Image</label>
            <div style="display: flex; align-items: center; gap: 15px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($settings['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                         alt="Hero image preview"
                         style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                         onerror="this.onerror=null; this.style.display='none';">
                <?php endif; ?>
                <div style="flex: 1;">
                    <input type="text" name="hero_image" id="all_updates_hero_image" class="form-input" 
                           value="<?php echo htmlspecialchars($settings['hero_image'] ?? ''); ?>" 
                           placeholder="Select image from media library" readonly>
                </div>
                <button type="button" onclick="openMediaLibrary('all_updates_hero_image')" 
                        class="btn btn-primary">🖼️ Select Image</button>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 30px;">💾 Save Changes</button>
    </form>
</div>
