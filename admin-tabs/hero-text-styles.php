<?php
/**
 * Hero Text Styles Tab
 * Single panel to control all hero section text colors and styles across all pages
 */

// Ensure table exists with correct structure
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'hero_text_styles'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS hero_text_styles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title_color VARCHAR(7) DEFAULT '#ffffff',
            title_font_size INT DEFAULT 64,
            title_font_weight VARCHAR(20) DEFAULT 'bold',
            title_text_shadow BOOLEAN DEFAULT TRUE,
            subtitle_color VARCHAR(7) DEFAULT '#ffffff',
            subtitle_font_size INT DEFAULT 28,
            subtitle_font_weight VARCHAR(20) DEFAULT '300',
            subtitle_text_shadow BOOLEAN DEFAULT TRUE,
            caption_color VARCHAR(50) DEFAULT 'rgba(255, 255, 255, 0.9)',
            caption_font_size INT DEFAULT 21,
            caption_font_weight VARCHAR(20) DEFAULT '400',
            caption_text_shadow BOOLEAN DEFAULT FALSE,
            description_color VARCHAR(7) DEFAULT '#ffffff',
            description_font_size INT DEFAULT 20,
            description_font_weight VARCHAR(20) DEFAULT '400',
            description_text_shadow BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Insert default settings
        $db->insert(
            "INSERT INTO hero_text_styles (is_active) VALUES (1)"
        );
    } else {
        // Check and add missing columns if table exists but is incomplete
        $columns = $db->fetchAll("SHOW COLUMNS FROM hero_text_styles");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'title_color' => "ALTER TABLE hero_text_styles ADD COLUMN title_color VARCHAR(7) DEFAULT '#ffffff' AFTER id",
            'title_font_size' => "ALTER TABLE hero_text_styles ADD COLUMN title_font_size INT DEFAULT 64 AFTER title_color",
            'title_font_weight' => "ALTER TABLE hero_text_styles ADD COLUMN title_font_weight VARCHAR(20) DEFAULT 'bold' AFTER title_font_size",
            'title_text_shadow' => "ALTER TABLE hero_text_styles ADD COLUMN title_text_shadow BOOLEAN DEFAULT TRUE AFTER title_font_weight",
            'subtitle_color' => "ALTER TABLE hero_text_styles ADD COLUMN subtitle_color VARCHAR(7) DEFAULT '#ffffff' AFTER title_text_shadow",
            'subtitle_font_size' => "ALTER TABLE hero_text_styles ADD COLUMN subtitle_font_size INT DEFAULT 28 AFTER subtitle_color",
            'subtitle_font_weight' => "ALTER TABLE hero_text_styles ADD COLUMN subtitle_font_weight VARCHAR(20) DEFAULT '300' AFTER subtitle_font_size",
            'subtitle_text_shadow' => "ALTER TABLE hero_text_styles ADD COLUMN subtitle_text_shadow BOOLEAN DEFAULT TRUE AFTER subtitle_font_weight",
            'caption_color' => "ALTER TABLE hero_text_styles ADD COLUMN caption_color VARCHAR(50) DEFAULT 'rgba(255, 255, 255, 0.9)' AFTER subtitle_text_shadow",
            'caption_font_size' => "ALTER TABLE hero_text_styles ADD COLUMN caption_font_size INT DEFAULT 21 AFTER caption_color",
            'caption_font_weight' => "ALTER TABLE hero_text_styles ADD COLUMN caption_font_weight VARCHAR(20) DEFAULT '400' AFTER caption_font_size",
            'caption_text_shadow' => "ALTER TABLE hero_text_styles ADD COLUMN caption_text_shadow BOOLEAN DEFAULT FALSE AFTER caption_font_weight",
            'description_color' => "ALTER TABLE hero_text_styles ADD COLUMN description_color VARCHAR(7) DEFAULT '#ffffff' AFTER caption_text_shadow",
            'description_font_size' => "ALTER TABLE hero_text_styles ADD COLUMN description_font_size INT DEFAULT 20 AFTER description_color",
            'description_font_weight' => "ALTER TABLE hero_text_styles ADD COLUMN description_font_weight VARCHAR(20) DEFAULT '400' AFTER description_font_size",
            'description_text_shadow' => "ALTER TABLE hero_text_styles ADD COLUMN description_text_shadow BOOLEAN DEFAULT TRUE AFTER description_font_weight",
        ];
        
        foreach ($requiredColumns as $colName => $alterSql) {
            if (!in_array($colName, $columnNames)) {
                try {
                    $db->query($alterSql);
                } catch (Exception $e) {
                    error_log("Error adding column $colName to hero_text_styles: " . $e->getMessage());
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Hero text styles table check error: " . $e->getMessage());
}

// Fetch hero styles
try {
    $heroStyles = $db->fetchOne("SELECT * FROM hero_text_styles WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
} catch (Exception $e) {
    error_log("Error fetching hero styles: " . $e->getMessage());
    $heroStyles = [];
}
?>
<div class="content-card">
    <h2>📝 Hero Section Text Styles</h2>
    <p style="margin-bottom: 30px; color: #666;">Customize text colors and styles for all hero sections across your website. These settings apply to page headers like "Our Teachers", "Clubs", "School Background", etc.</p>
    
    <form method="POST" action="api/content/hero-text-styles.php" id="heroTextStylesForm" onsubmit="return handleAdminFormSubmit(event, this);">
        
        <!-- Title Styles Section -->
        <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); padding: 25px; border-radius: 12px; border: 2px solid #0ea5e9; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">📌 Main Title (e.g., "Our Teachers", "Clubs")</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Title Text Color</label>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <input type="color" 
                               id="title_color_picker"
                               name="title_color" 
                               value="<?php echo htmlspecialchars($heroStyles['title_color'] ?? '#ffffff'); ?>"
                               style="width: 80px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer;"
                               onchange="document.getElementById('title_color_text').value=this.value; updateTitlePreview();">
                        <input type="text" 
                               id="title_color_text"
                               name="title_color_text" 
                               value="<?php echo htmlspecialchars($heroStyles['title_color'] ?? '#ffffff'); ?>"
                               class="form-input" 
                               placeholder="#ffffff"
                               style="flex: 1; max-width: 200px;"
                               onchange="document.getElementById('title_color_picker').value=this.value; updateTitlePreview();"
                               pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                        <div id="title_color_preview" style="width: 100px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; background: <?php echo htmlspecialchars($heroStyles['title_color'] ?? '#ffffff'); ?>; color: <?php echo htmlspecialchars($heroStyles['title_color'] ?? '#ffffff'); ?>; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            Title
                        </div>
                    </div>
                    <small style="color: #6b7280; display: block; margin-top: 5px;">💡 Use white (#ffffff) or light colors for dark backgrounds</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Title Font Size (px)</label>
                    <input type="number" 
                           name="title_font_size" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($heroStyles['title_font_size'] ?? '64'); ?>"
                           min="24" 
                           max="120" 
                           step="1"
                           onchange="updateTitlePreview();">
                    <small style="color: #6b7280; display: block; margin-top: 5px;">Recommended: 48-72px for desktop</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Title Font Weight</label>
                    <select name="title_font_weight" class="form-input" onchange="updateTitlePreview();">
                        <option value="300" <?php echo ($heroStyles['title_font_weight'] ?? 'bold') === '300' ? 'selected' : ''; ?>>Light (300)</option>
                        <option value="400" <?php echo ($heroStyles['title_font_weight'] ?? 'bold') === '400' ? 'selected' : ''; ?>>Regular (400)</option>
                        <option value="500" <?php echo ($heroStyles['title_font_weight'] ?? 'bold') === '500' ? 'selected' : ''; ?>>Medium (500)</option>
                        <option value="600" <?php echo ($heroStyles['title_font_weight'] ?? 'bold') === '600' ? 'selected' : ''; ?>>Semi-Bold (600)</option>
                        <option value="700" <?php echo ($heroStyles['title_font_weight'] ?? 'bold') === '700' || ($heroStyles['title_font_weight'] ?? 'bold') === 'bold' ? 'selected' : ''; ?>>Bold (700)</option>
                        <option value="800" <?php echo ($heroStyles['title_font_weight'] ?? 'bold') === '800' ? 'selected' : ''; ?>>Extra Bold (800)</option>
                        <option value="900" <?php echo ($heroStyles['title_font_weight'] ?? 'bold') === '900' ? 'selected' : ''; ?>>Black (900)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Subtitle Styles Section -->
        <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 25px; border-radius: 12px; border: 2px solid #f59e0b; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">📝 Subtitle (e.g., "Our Expert Teachers", "Extracurricular Activities...")</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Subtitle Text Color</label>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <input type="color" 
                               id="subtitle_color_picker"
                               name="subtitle_color" 
                               value="<?php echo htmlspecialchars($heroStyles['subtitle_color'] ?? '#ffffff'); ?>"
                               style="width: 80px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer;"
                               onchange="document.getElementById('subtitle_color_text').value=this.value; updateSubtitlePreview();">
                        <input type="text" 
                               id="subtitle_color_text"
                               name="subtitle_color_text" 
                               value="<?php echo htmlspecialchars($heroStyles['subtitle_color'] ?? '#ffffff'); ?>"
                               class="form-input" 
                               placeholder="#ffffff"
                               style="flex: 1; max-width: 200px;"
                               onchange="document.getElementById('subtitle_color_picker').value=this.value; updateSubtitlePreview();"
                               pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                        <div id="subtitle_color_preview" style="width: 100px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: normal; background: <?php echo htmlspecialchars($heroStyles['subtitle_color'] ?? '#ffffff'); ?>; color: <?php echo htmlspecialchars($heroStyles['subtitle_color'] ?? '#ffffff'); ?>; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                            Subtitle
                        </div>
                    </div>
                    <small style="color: #6b7280; display: block; margin-top: 5px;">💡 Use white (#ffffff) or light colors for dark backgrounds</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Subtitle Font Size (px)</label>
                    <input type="number" 
                           name="subtitle_font_size" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($heroStyles['subtitle_font_size'] ?? '28'); ?>"
                           min="14" 
                           max="60" 
                           step="1"
                           onchange="updateSubtitlePreview();">
                    <small style="color: #6b7280; display: block; margin-top: 5px;">Recommended: 20-32px for desktop</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Subtitle Font Weight</label>
                    <select name="subtitle_font_weight" class="form-input" onchange="updateSubtitlePreview();">
                        <option value="300" <?php echo ($heroStyles['subtitle_font_weight'] ?? '300') === '300' ? 'selected' : ''; ?>>Light (300)</option>
                        <option value="400" <?php echo ($heroStyles['subtitle_font_weight'] ?? '300') === '400' ? 'selected' : ''; ?>>Regular (400)</option>
                        <option value="500" <?php echo ($heroStyles['subtitle_font_weight'] ?? '300') === '500' ? 'selected' : ''; ?>>Medium (500)</option>
                        <option value="600" <?php echo ($heroStyles['subtitle_font_weight'] ?? '300') === '600' ? 'selected' : ''; ?>>Semi-Bold (600)</option>
                        <option value="700" <?php echo ($heroStyles['subtitle_font_weight'] ?? '300') === '700' ? 'selected' : ''; ?>>Bold (700)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Description Styles Section (Homepage Hero) -->
        <div style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); padding: 25px; border-radius: 12px; border: 2px solid #22c55e; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">📄 Description Text (Homepage - "We focus on academic excellence...")</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Description Text Color</label>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <input type="color" 
                               id="description_color_picker"
                               name="description_color" 
                               value="<?php echo htmlspecialchars($heroStyles['description_color'] ?? '#ffffff'); ?>"
                               style="width: 80px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer;"
                               onchange="document.getElementById('description_color_text').value=this.value; updateDescriptionPreview();">
                        <input type="text" 
                               id="description_color_text"
                               name="description_color_text" 
                               value="<?php echo htmlspecialchars($heroStyles['description_color'] ?? '#ffffff'); ?>"
                               class="form-input" 
                               placeholder="#ffffff"
                               style="flex: 1; max-width: 200px;"
                               onchange="document.getElementById('description_color_picker').value=this.value; updateDescriptionPreview();"
                               pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                        <div id="description_color_preview" style="width: 100px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: normal; background: <?php echo htmlspecialchars($heroStyles['description_color'] ?? '#ffffff'); ?>; color: <?php echo htmlspecialchars($heroStyles['description_color'] ?? '#ffffff'); ?>; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                            Desc
                        </div>
                    </div>
                    <small style="color: #6b7280; display: block; margin-top: 5px;">💡 Use white (#ffffff) for best visibility on dark backgrounds</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description Font Size (px)</label>
                    <input type="number" 
                           name="description_font_size" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($heroStyles['description_font_size'] ?? '20'); ?>"
                           min="14" 
                           max="32" 
                           step="1"
                           onchange="updateDescriptionPreview();">
                    <small style="color: #6b7280; display: block; margin-top: 5px;">Recommended: 18-24px</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description Font Weight</label>
                    <select name="description_font_weight" class="form-input" onchange="updateDescriptionPreview();">
                        <option value="300" <?php echo ($heroStyles['description_font_weight'] ?? '400') === '300' ? 'selected' : ''; ?>>Light (300)</option>
                        <option value="400" <?php echo ($heroStyles['description_font_weight'] ?? '400') === '400' ? 'selected' : ''; ?>>Regular (400)</option>
                        <option value="500" <?php echo ($heroStyles['description_font_weight'] ?? '400') === '500' ? 'selected' : ''; ?>>Medium (500)</option>
                        <option value="600" <?php echo ($heroStyles['description_font_weight'] ?? '400') === '600' ? 'selected' : ''; ?>>Semi-Bold (600)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Caption Styles Section (if used) -->
        <div style="background: linear-gradient(135deg, #f3e8ff, #e9d5ff); padding: 25px; border-radius: 12px; border: 2px solid #a855f7; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">💬 Caption Text (e.g., "Know Us Better", "Meet the Team")</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Caption Text Color</label>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <input type="color" 
                               id="caption_color_picker"
                               name="caption_color" 
                               value="<?php echo htmlspecialchars($heroStyles['caption_color'] ?? 'rgba(255, 255, 255, 0.9)'); ?>"
                               style="width: 80px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer;"
                               onchange="document.getElementById('caption_color_text').value=this.value; updateCaptionPreview();">
                        <input type="text" 
                               id="caption_color_text"
                               name="caption_color_text" 
                               value="<?php echo htmlspecialchars($heroStyles['caption_color'] ?? 'rgba(255, 255, 255, 0.9)'); ?>"
                               class="form-input" 
                               placeholder="rgba(255, 255, 255, 0.9)"
                               style="flex: 1; max-width: 200px;"
                               onchange="document.getElementById('caption_color_picker').value=this.value; updateCaptionPreview();">
                        <div id="caption_color_preview" style="width: 100px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: normal; background: <?php echo htmlspecialchars($heroStyles['caption_color'] ?? 'rgba(255, 255, 255, 0.9)'); ?>; color: <?php echo htmlspecialchars($heroStyles['caption_color'] ?? 'rgba(255, 255, 255, 0.9)'); ?>;">
                            Caption
                        </div>
                    </div>
                    <small style="color: #6b7280; display: block; margin-top: 5px;">💡 Use rgba() for transparency, e.g., rgba(255, 255, 255, 0.9)</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Caption Font Size (px)</label>
                    <input type="number" 
                           name="caption_font_size" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($heroStyles['caption_font_size'] ?? '21'); ?>"
                           min="12" 
                           max="40" 
                           step="1"
                           onchange="updateCaptionPreview();">
                    <small style="color: #6b7280; display: block; margin-top: 5px;">Recommended: 18-24px</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Caption Font Weight</label>
                    <select name="caption_font_weight" class="form-input" onchange="updateCaptionPreview();">
                        <option value="300" <?php echo ($heroStyles['caption_font_weight'] ?? '400') === '300' ? 'selected' : ''; ?>>Light (300)</option>
                        <option value="400" <?php echo ($heroStyles['caption_font_weight'] ?? '400') === '400' ? 'selected' : ''; ?>>Regular (400)</option>
                        <option value="500" <?php echo ($heroStyles['caption_font_weight'] ?? '400') === '500' ? 'selected' : ''; ?>>Medium (500)</option>
                        <option value="600" <?php echo ($heroStyles['caption_font_weight'] ?? '400') === '600' ? 'selected' : ''; ?>>Semi-Bold (600)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Text Shadow Options -->
        <div style="background: white; padding: 25px; border-radius: 12px; border: 2px solid #e0e0e0; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">✨ Text Shadow (for better visibility on images)</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" 
                               name="title_text_shadow" 
                               value="1"
                               <?php echo ($heroStyles['title_text_shadow'] ?? '1') == '1' ? 'checked' : ''; ?>
                               onchange="updateTitlePreview();">
                        Enable Title Text Shadow
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" 
                               name="subtitle_text_shadow" 
                               value="1"
                               <?php echo ($heroStyles['subtitle_text_shadow'] ?? '1') == '1' ? 'checked' : ''; ?>
                               onchange="updateSubtitlePreview();">
                        Enable Subtitle Text Shadow
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" 
                               name="description_text_shadow" 
                               value="1"
                               <?php echo ($heroStyles['description_text_shadow'] ?? '1') == '1' ? 'checked' : ''; ?>
                               onchange="updateDescriptionPreview();">
                        Enable Description Text Shadow
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" 
                               name="caption_text_shadow" 
                               value="1"
                               <?php echo ($heroStyles['caption_text_shadow'] ?? '0') == '1' ? 'checked' : ''; ?>
                               onchange="updateCaptionPreview();">
                        Enable Caption Text Shadow
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Live Preview -->
        <div style="background: linear-gradient(135deg, #1e293b, #334155); padding: 40px; border-radius: 12px; margin-bottom: 30px; min-height: 300px; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,%3Csvg width=\'100\' height=\'100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%23222\'/%3E%3C/svg%3E') repeat; opacity: 0.3;"></div>
            <div style="position: relative; z-index: 1; text-align: center;">
                <h3 style="color: white; margin-bottom: 20px; font-size: 18px;">Live Preview</h3>
                <p id="preview_caption" style="margin-bottom: 10px; font-size: <?php echo htmlspecialchars($heroStyles['caption_font_size'] ?? '21'); ?>px; font-weight: <?php echo htmlspecialchars($heroStyles['caption_font_weight'] ?? '400'); ?>; color: <?php echo htmlspecialchars($heroStyles['caption_color'] ?? 'rgba(255, 255, 255, 0.9)'); ?>; <?php echo ($heroStyles['caption_text_shadow'] ?? '0') == '1' ? 'text-shadow: 1px 1px 2px rgba(0,0,0,0.5);' : ''; ?>">Know Us Better</p>
                <h1 id="preview_title" style="margin-bottom: 20px; font-size: <?php echo htmlspecialchars($heroStyles['title_font_size'] ?? '64'); ?>px; font-weight: <?php echo htmlspecialchars($heroStyles['title_font_weight'] ?? 'bold') === 'bold' ? '700' : ($heroStyles['title_font_weight'] ?? '700'); ?>; color: <?php echo htmlspecialchars($heroStyles['title_color'] ?? '#ffffff'); ?>; <?php echo ($heroStyles['title_text_shadow'] ?? '1') == '1' ? 'text-shadow: 2px 2px 4px rgba(0,0,0,0.5);' : ''; ?>">Welcome to SJS</h1>
                <p id="preview_subtitle" style="margin-bottom: 15px; font-size: <?php echo htmlspecialchars($heroStyles['subtitle_font_size'] ?? '28'); ?>px; font-weight: <?php echo htmlspecialchars($heroStyles['subtitle_font_weight'] ?? '300'); ?>; color: <?php echo htmlspecialchars($heroStyles['subtitle_color'] ?? '#ffffff'); ?>; <?php echo ($heroStyles['subtitle_text_shadow'] ?? '1') == '1' ? 'text-shadow: 1px 1px 2px rgba(0,0,0,0.5);' : ''; ?>">Empowering minds, shaping futures</p>
                <p id="preview_description" style="font-size: <?php echo htmlspecialchars($heroStyles['description_font_size'] ?? '20'); ?>px; font-weight: <?php echo htmlspecialchars($heroStyles['description_font_weight'] ?? '400'); ?>; color: <?php echo htmlspecialchars($heroStyles['description_color'] ?? '#ffffff'); ?>; <?php echo ($heroStyles['description_text_shadow'] ?? '1') == '1' ? 'text-shadow: 1px 1px 2px rgba(0,0,0,0.5);' : ''; ?>">We focus on academic excellence, moral values, and holistic development for every child.</p>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px 24px; font-size: 16px; font-weight: 600;">
                💾 Save Hero Text Styles
            </button>
            <button type="button" onclick="resetToDefaults()" class="btn" style="background: #6b7280; color: white; padding: 12px 24px; font-size: 16px; font-weight: 600;">
                🔄 Reset to Defaults
            </button>
        </div>
    </form>
</div>

<script>
// Update title preview
function updateTitlePreview() {
    const color = document.getElementById('title_color_text').value || '#ffffff';
    const fontSize = document.querySelector('input[name="title_font_size"]').value || '64';
    const fontWeight = document.querySelector('select[name="title_font_weight"]').value || '700';
    const textShadow = document.querySelector('input[name="title_text_shadow"]').checked;
    
    const preview = document.getElementById('preview_title');
    if (preview) {
        preview.style.color = color;
        preview.style.fontSize = fontSize + 'px';
        preview.style.fontWeight = fontWeight;
        preview.style.textShadow = textShadow ? '2px 2px 4px rgba(0,0,0,0.5)' : 'none';
    }
    
    // Update color preview box
    const colorPreview = document.getElementById('title_color_preview');
    if (colorPreview) {
        colorPreview.style.background = color;
        colorPreview.style.color = color;
        colorPreview.style.textShadow = textShadow ? '2px 2px 4px rgba(0,0,0,0.5)' : 'none';
    }
}

// Update subtitle preview
function updateSubtitlePreview() {
    const color = document.getElementById('subtitle_color_text').value || '#ffffff';
    const fontSize = document.querySelector('input[name="subtitle_font_size"]').value || '28';
    const fontWeight = document.querySelector('select[name="subtitle_font_weight"]').value || '300';
    const textShadow = document.querySelector('input[name="subtitle_text_shadow"]').checked;
    
    const preview = document.getElementById('preview_subtitle');
    if (preview) {
        preview.style.color = color;
        preview.style.fontSize = fontSize + 'px';
        preview.style.fontWeight = fontWeight;
        preview.style.textShadow = textShadow ? '1px 1px 2px rgba(0,0,0,0.5)' : 'none';
    }
    
    // Update color preview box
    const colorPreview = document.getElementById('subtitle_color_preview');
    if (colorPreview) {
        colorPreview.style.background = color;
        colorPreview.style.color = color;
        colorPreview.style.textShadow = textShadow ? '1px 1px 2px rgba(0,0,0,0.5)' : 'none';
    }
}

// Update description preview
function updateDescriptionPreview() {
    const color = document.getElementById('description_color_text').value || '#ffffff';
    const fontSize = document.querySelector('input[name="description_font_size"]').value || '20';
    const fontWeight = document.querySelector('select[name="description_font_weight"]').value || '400';
    const textShadow = document.querySelector('input[name="description_text_shadow"]').checked;
    
    const preview = document.getElementById('preview_description');
    if (preview) {
        preview.style.color = color;
        preview.style.fontSize = fontSize + 'px';
        preview.style.fontWeight = fontWeight;
        preview.style.textShadow = textShadow ? '1px 1px 2px rgba(0,0,0,0.5)' : 'none';
    }
    
    // Update color preview box
    const colorPreview = document.getElementById('description_color_preview');
    if (colorPreview) {
        colorPreview.style.background = color;
        colorPreview.style.color = color;
        colorPreview.style.textShadow = textShadow ? '1px 1px 2px rgba(0,0,0,0.5)' : 'none';
    }
}

// Update caption preview
function updateCaptionPreview() {
    const color = document.getElementById('caption_color_text').value || 'rgba(255, 255, 255, 0.9)';
    const fontSize = document.querySelector('input[name="caption_font_size"]').value || '21';
    const fontWeight = document.querySelector('select[name="caption_font_weight"]').value || '400';
    const textShadow = document.querySelector('input[name="caption_text_shadow"]').checked;
    
    const preview = document.getElementById('preview_caption');
    if (preview) {
        preview.style.color = color;
        preview.style.fontSize = fontSize + 'px';
        preview.style.fontWeight = fontWeight;
        preview.style.textShadow = textShadow ? '1px 1px 2px rgba(0,0,0,0.5)' : 'none';
    }
    
    // Update color preview box
    const colorPreview = document.getElementById('caption_color_preview');
    if (colorPreview) {
        colorPreview.style.background = color;
        colorPreview.style.color = color;
        colorPreview.style.textShadow = textShadow ? '1px 1px 2px rgba(0,0,0,0.5)' : 'none';
    }
}

// Reset to defaults
function resetToDefaults() {
    if (confirm('Are you sure you want to reset all hero text styles to defaults?')) {
        document.getElementById('title_color_text').value = '#ffffff';
        document.getElementById('title_color_picker').value = '#ffffff';
        document.querySelector('input[name="title_font_size"]').value = '64';
        document.querySelector('select[name="title_font_weight"]').value = '700';
        document.querySelector('input[name="title_text_shadow"]').checked = true;
        
        document.getElementById('subtitle_color_text').value = '#ffffff';
        document.getElementById('subtitle_color_picker').value = '#ffffff';
        document.querySelector('input[name="subtitle_font_size"]').value = '28';
        document.querySelector('select[name="subtitle_font_weight"]').value = '300';
        document.querySelector('input[name="subtitle_text_shadow"]').checked = true;
        
        document.getElementById('description_color_text').value = '#ffffff';
        document.getElementById('description_color_picker').value = '#ffffff';
        document.querySelector('input[name="description_font_size"]').value = '20';
        document.querySelector('select[name="description_font_weight"]').value = '400';
        document.querySelector('input[name="description_text_shadow"]').checked = true;
        
        document.getElementById('caption_color_text').value = 'rgba(255, 255, 255, 0.9)';
        document.getElementById('caption_color_picker').value = '#ffffff';
        document.querySelector('input[name="caption_font_size"]').value = '21';
        document.querySelector('select[name="caption_font_weight"]').value = '400';
        document.querySelector('input[name="caption_text_shadow"]').checked = false;
        
        updateTitlePreview();
        updateSubtitlePreview();
        updateDescriptionPreview();
        updateCaptionPreview();
    }
}

// Initialize previews on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTitlePreview();
    updateSubtitlePreview();
    updateDescriptionPreview();
    updateCaptionPreview();
});
</script>
