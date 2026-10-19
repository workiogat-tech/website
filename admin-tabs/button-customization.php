<?php
/**
 * Button Customization Tab
 * This is the main customization interface
 */

// Get button settings
$buttonSettings = $db->fetchOne("SELECT * FROM button_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];

// Get branding
$branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>🎯 Design & Customization</h2>
    <p style="margin-bottom: 30px; color: #666;">Customize the visual appearance of all sections on your website. Changes save automatically and reflect immediately.</p>
    
    <!-- Section Tabs -->
    <div class="customization-tabs">
        <?php foreach ($customizationSections as $section): ?>
            <a href="?tab=button-customization&section=<?php echo $section['id']; ?>" 
               class="customization-tab <?php echo $customizationSection === $section['id'] ? 'active' : ''; ?>">
                <span style="margin-right: 8px;"><?php echo $section['icon']; ?></span>
                <?php echo $section['label']; ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Section Content -->
    <div style="background: white; border-radius: 12px; padding: 30px; margin-top: 20px;">
        <?php if ($customizationSection === 'header'): ?>
            <!-- Header & Navigation Customization -->
            <h3 style="margin-bottom: 20px; font-size: 24px;">Header & Navigation</h3>
            
            <!-- Live Preview -->
            <div style="background: #f8f9fa; padding: 30px; border-radius: 12px; margin-bottom: 30px; border: 2px dashed #ddd;">
                <h4 style="margin-bottom: 15px;">Live Preview</h4>
                <div style="display: flex; align-items: center; justify-content: space-between; background: white; padding: 20px; border-radius: 8px;">
                    <!-- Branding Preview -->
                    <div style="display: flex; align-items: center;">
                        <div style="width: 50px; height: 50px; background: #3B82F6; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <span style="color: white; font-weight: bold; font-size: 24px;">E</span>
                        </div>
                        <div>
                            <h1 style="font-size: 24px; font-weight: bold; margin: 0; color: #333;">
                                <?php echo htmlspecialchars($branding['school_name'] ?? 'Excellence Academy'); ?>
                            </h1>
                            <p style="font-size: 12px; color: #666; margin: 0;">
                                <?php echo htmlspecialchars($branding['tagline'] ?? 'Shaping Tomorrow\'s Leaders Today'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Buttons Preview -->
                    <div style="display: flex; gap: 15px;">
                        <button style="padding: 10px 20px; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: default;">
                            ❤️ Sponsorship
                        </button>
                        <button style="padding: 10px 20px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: default;">
                            👥 Parents Portal
                        </button>
                        <button style="padding: 10px 20px; background: linear-gradient(135deg, #800020, #a00030); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: default;">
                            👤 Admin Sign In
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Customization Options -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
                <!-- Global Settings -->
                <div style="background: linear-gradient(135deg, #e0f2fe, #dbeafe); padding: 20px; border-radius: 12px; border: 2px solid #93c5fd;">
                    <h4 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <span>⚙️</span> Global Settings
                    </h4>
                    <div class="form-group">
                        <label class="form-label">Button Spacing</label>
                        <select class="form-input">
                            <option>None (0px)</option>
                            <option>Extra Tight (2px)</option>
                            <option>Tight (4px)</option>
                            <option selected>Normal (8px)</option>
                            <option>Roomy (12px)</option>
                            <option>Wide (16px)</option>
                        </select>
                    </div>
                </div>
                
                <!-- Branding Text -->
                <div style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); padding: 20px; border-radius: 12px; border: 2px solid #6ee7b7;">
                    <h4 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <span>🎨</span> Branding Text
                    </h4>
                    <div class="form-group">
                        <label class="form-label">Text Color</label>
                        <select class="form-input">
                            <option>Dark Gray</option>
                            <option>Black</option>
                            <option>Blue</option>
                            <option>Purple</option>
                            <option>Custom Color</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Text Size</label>
                        <select class="form-input">
                            <option>Small (18px)</option>
                            <option>Medium (20px)</option>
                            <option>Large (24px)</option>
                            <option>Extra Large (30px)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Font Weight</label>
                        <select class="form-input">
                            <option>Medium</option>
                            <option>Semibold</option>
                            <option>Bold</option>
                            <option>Extra Bold</option>
                        </select>
                    </div>
                </div>
                
                <!-- Sponsorship Button -->
                <div style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); padding: 20px; border-radius: 12px; border: 2px solid #10b981;">
                    <h4 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <span>❤️</span> Sponsorship Button
                    </h4>
                    <div class="form-group">
                        <label class="form-label">Background Color</label>
                        <select class="form-input">
                            <option selected>Green</option>
                            <option>Blue</option>
                            <option>Purple</option>
                            <option>Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hover Color</label>
                        <select class="form-input">
                            <option selected>Green Dark</option>
                            <option>Blue Dark</option>
                            <option>Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Text Color</label>
                        <select class="form-input">
                            <option selected>White</option>
                            <option>Black</option>
                            <option>Custom</option>
                        </select>
                    </div>
                </div>
                
                <!-- Parents Portal Button -->
                <div style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); padding: 20px; border-radius: 12px; border: 2px solid #6366f1;">
                    <h4 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <span>👥</span> Parents Portal Button
                    </h4>
                    <div class="form-group">
                        <label class="form-label">Background Color</label>
                        <select class="form-input">
                            <option selected>Indigo</option>
                            <option>Blue</option>
                            <option>Purple</option>
                            <option>Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hover Color</label>
                        <select class="form-input">
                            <option selected>Indigo Dark</option>
                            <option>Blue Dark</option>
                            <option>Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Text Color</label>
                        <select class="form-input">
                            <option selected>White</option>
                            <option>Black</option>
                            <option>Custom</option>
                        </select>
                    </div>
                </div>
                
                <!-- Admin Sign In Button -->
                <div style="background: linear-gradient(135deg, #fce7f3, #fbcfe8); padding: 20px; border-radius: 12px; border: 2px solid #ec4899;">
                    <h4 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <span>👤</span> Admin Sign In Button
                    </h4>
                    <div class="form-group">
                        <label class="form-label">Background Color</label>
                        <select class="form-input">
                            <option selected>Burgundy</option>
                            <option>Blue</option>
                            <option>Purple</option>
                            <option>Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hover Color</label>
                        <select class="form-input">
                            <option selected>Burgundy Dark</option>
                            <option>Blue Dark</option>
                            <option>Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Text Color</label>
                        <select class="form-input">
                            <option selected>White</option>
                            <option>Black</option>
                            <option>Custom</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="api/content/button-settings.php" id="buttonSettingsForm" onsubmit="return handleAdminFormSubmit(event, this);">
                <input type="hidden" name="button_name" value="header_buttons">
                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn btn-primary">
                        💾 Save Changes
                    </button>
                </div>
            </form>
            
        <?php elseif ($customizationSection === 'hero'): ?>
            <h3 style="margin-bottom: 20px; font-size: 24px;">Hero Section Customization</h3>
            <p style="color: #666; margin-bottom: 20px;">Customize hero section colors, typography, spacing, and visual appearance.</p>
            <div class="form-group">
                <label class="form-label">Hero Title</label>
                <input type="text" class="form-input" placeholder="Enter hero title">
            </div>
            <div class="form-group">
                <label class="form-label">Hero Subtitle</label>
                <input type="text" class="form-input" placeholder="Enter hero subtitle">
            </div>
            <div class="form-group">
                <label class="form-label">Background Color</label>
                <input type="color" class="form-input" style="height: 50px;">
            </div>
            <button class="btn btn-primary">💾 Save Changes</button>
            
        <?php else: ?>
            <h3 style="margin-bottom: 20px; font-size: 24px;"><?php echo $customizationSections[array_search($customizationSection, array_column($customizationSections, 'id'))]['label'] ?? 'Customization'; ?></h3>
            <p style="color: #666;">This section is coming soon. Full customization options will be available here.</p>
        <?php endif; ?>
    </div>
</div>
