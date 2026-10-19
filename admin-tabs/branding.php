<?php
/**
 * Branding & Identity Tab - EXACT replica from images
 */
$branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card" style="padding: 0;">
    <div style="padding: 30px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 30px;">
            <span style="font-size: 28px;">🎨</span>
            <h2 style="font-size: 28px; font-weight: bold; color: #111827; margin: 0;">Branding & Identity</h2>
        </div>
        
        <form method="POST" action="api/content/branding.php" id="brandingForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <!-- School Name -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">School Name</label>
                <input type="text" 
                       name="school_name" 
                       class="form-input" 
                       style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                       value="<?php echo htmlspecialchars($branding['school_name'] ?? 'SJS ACADEMY'); ?>" 
                       placeholder="SJS ACADEMY"
                       onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
            </div>
            
            <!-- Tagline -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Tagline</label>
                <input type="text" 
                       name="tagline" 
                       class="form-input" 
                       style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                       value="<?php echo htmlspecialchars($branding['tagline'] ?? 'Shaping Tomorrows Leaders Today'); ?>" 
                       placeholder="Shaping Tomorrows Leaders Today"
                       onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
            </div>
            
            <!-- School Logo -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">School Logo</label>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <?php 
                    $logoUrl = normalizeImageUrl($branding['logo'] ?? '');
                    if (!empty($logoUrl)): ?>
                        <div style="flex-shrink: 0;">
                            <img src="<?php echo htmlspecialchars($logoUrl); ?>" 
                                 alt="School Logo" 
                                 style="width: 80px; height: 80px; object-fit: contain; border-radius: 50%; border: 2px solid #e5e7eb; padding: 4px; background: white;"
                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 80px; height: 80px; border-radius: 50%; border: 2px solid #e5e7eb; background: #f3f4f6; align-items: center; justify-content: center;">
                                <span style="font-size: 32px; color: #9ca3af;">🖼️</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="width: 80px; height: 80px; border-radius: 50%; border: 2px solid #e5e7eb; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <span style="font-size: 32px; color: #9ca3af;">🖼️</span>
                        </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" 
                               name="logo" 
                               id="branding_logo" 
                               class="form-input" 
                               style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb; color: #6b7280;"
                               value="<?php echo htmlspecialchars($branding['logo'] ?? ''); ?>" 
                               placeholder="Select logo from media library"
                               readonly>
                    </div>
                    <button type="button" 
                            onclick="openMediaLibrary('branding_logo')" 
                            style="width: 48px; height: 48px; background: #2563eb; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; flex-shrink: 0;"
                            onmouseover="this.style.background='#1d4ed8'"
                            onmouseout="this.style.background='#2563eb'">
                        <span style="font-size: 20px; color: white;">🖼️</span>
                    </button>
                </div>
            </div>
            
            <!-- Colors Section -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 30px;">
                <!-- Primary Color -->
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Primary Color</label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="color" 
                               id="primary_color_picker"
                               name="primary_color" 
                               style="width: 60px; height: 50px; padding: 0; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; flex-shrink: 0;"
                               value="<?php echo htmlspecialchars($branding['primary_color'] ?? '#800020'); ?>"
                               onchange="document.getElementById('primary_color_text').value=this.value">
                        <input type="text" 
                               id="primary_color_text"
                               name="primary_color_text" 
                               class="form-input" 
                               style="flex: 1; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-family: monospace;"
                               value="<?php echo htmlspecialchars($branding['primary_color'] ?? '#800020'); ?>"
                               onchange="document.getElementById('primary_color_picker').value=this.value"
                               pattern="^#[0-9A-Fa-f]{6}$"
                               placeholder="#800020">
                    </div>
                </div>
                
                <!-- Secondary Color -->
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Secondary Color</label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="color" 
                               id="secondary_color_picker"
                               name="secondary_color" 
                               style="width: 60px; height: 50px; padding: 0; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; flex-shrink: 0;"
                               value="<?php echo htmlspecialchars($branding['secondary_color'] ?? '#6B0000'); ?>"
                               onchange="document.getElementById('secondary_color_text').value=this.value">
                        <input type="text" 
                               id="secondary_color_text"
                               name="secondary_color_text" 
                               class="form-input" 
                               style="flex: 1; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-family: monospace;"
                               value="<?php echo htmlspecialchars($branding['secondary_color'] ?? '#6B0000'); ?>"
                               onchange="document.getElementById('secondary_color_picker').value=this.value"
                               pattern="^#[0-9A-Fa-f]{6}$"
                               placeholder="#6B0000">
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: flex; gap: 12px; margin-top: 30px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="submit" 
                        style="padding: 12px 24px; background: linear-gradient(135deg, #9333ea, #7e22ce); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(147, 51, 234, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span>💾</span> Save Changes
                </button>
                <button type="button" 
                        onclick="window.open('index.php', '_blank')"
                        style="padding: 12px 24px; background: #4b5563; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                        onmouseover="this.style.background='#374151'; this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.background='#4b5563'; this.style.transform='translateY(0)'">
                    <span>👁️</span> Preview
                </button>
            </div>
        </form>
    </div>
</div>
