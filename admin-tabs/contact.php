<?php
/**
 * Contact Tab - With Image Upload
 */
$contact = $db->fetchOne("SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>📧 Contact Information</h2>
    <p style="margin-bottom: 30px; color: #666;">Update contact details and information.</p>
    
    <!-- Hero Image Section -->
    <div style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); padding: 25px; border-radius: 12px; border: 2px solid #10b981; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <span>🖼️</span> Contact Page Header Image
        </h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
            This is the large background image displayed at the top of the Contact page.
        </p>
        
        <form method="POST" action="api/content/contact.php" id="contactHeroForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 20px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($contact['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <div style="flex-shrink: 0; position: relative;">
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview" 
                             style="width: 160px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display: none; width: 160px; height: 96px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center; position: absolute; top: 0; left: 0;">
                            <div style="text-align: center;">
                                <span style="font-size: 24px; color: #999;">🖼️</span>
                                <p style="font-size: 11px; color: #999; margin-top: 5px;">Failed to load</p>
                            </div>
                        </div>
                        <p style="text-align: center; font-size: 11px; color: #666; margin-top: 5px;">Preview</p>
                    </div>
                <?php else: ?>
                    <div style="flex-shrink: 0; width: 160px; height: 96px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;">
                        <div style="text-align: center;">
                            <span style="font-size: 24px; color: #999;">🖼️</span>
                            <p style="font-size: 11px; color: #999; margin-top: 5px;">No image</p>
                        </div>
                    </div>
                <?php endif; ?>
                <div style="flex: 1;">
                    <input type="text" 
                           name="hero_image" 
                           id="contact_hero_image" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($contact['hero_image'] ?? ''); ?>" 
                           placeholder="Image URL will appear here after selection"
                           readonly>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" 
                                onclick="openMediaLibrary('contact_hero_image')" 
                                class="btn btn-primary"
                                style="flex: 1; background: linear-gradient(135deg, #16a34a, #059669);">
                            🖼️ Select Hero Image
                        </button>
                        <?php if (!empty($contact['hero_image'])): ?>
                            <button type="button" 
                                    onclick="document.getElementById('contact_hero_image').value='';" 
                                    class="btn btn-danger"
                                    style="padding: 12px 20px;">
                                Remove Image
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #16a34a, #059669);">
                💾 Save Hero Image
            </button>
        </form>
    </div>
    
    <form method="POST" action="api/content/contact.php" id="contactForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-input" rows="3"><?php echo htmlspecialchars($contact['address'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-input" 
                   value="<?php echo htmlspecialchars($contact['phone'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" 
                   value="<?php echo htmlspecialchars($contact['email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Hours</label>
            <textarea name="hours" class="form-input" rows="3"><?php echo htmlspecialchars($contact['hours'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Intro Text</label>
            <textarea name="intro_text" class="form-input" rows="3"><?php echo htmlspecialchars($contact['intro_text'] ?? ''); ?></textarea>
        </div>

        <div id="school-map" class="form-group" style="background:#fafaf9;border:1px solid #e7e5e4;border-radius:12px;padding:18px;">
            <h3 style="margin:0 0 8px;font-size:18px;">School map</h3>
            <p style="color:#57534e;font-size:14px;line-height:1.5;margin:0 0 12px;">Open Google Maps, find the school, click Share, then Embed a map, and copy the HTML. Paste it here. A normal Google Maps link also works. This map is shown on the Contact page.</p>
            <label class="form-label">Google Maps embed or link</label>
            <textarea name="map_embed" class="form-input" rows="4" placeholder="Paste the embed code or the maps link"><?php echo htmlspecialchars($contact['map_embed'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Section Image</label>
            <div style="display: flex; align-items: center; gap: 15px;">
                <?php 
                $sectionImageUrl = normalizeImageUrl($contact['image'] ?? '');
                if (!empty($sectionImageUrl)): ?>
                    <img src="<?php echo htmlspecialchars($sectionImageUrl); ?>" 
                         alt="Section Preview" 
                         style="width: 120px; height: 80px; object-fit: cover; border: 2px solid #e0e0e0; border-radius: 8px;"
                         onerror="this.onerror=null; this.style.display='none';">
                <?php endif; ?>
                <div style="flex: 1;">
                    <input type="text" 
                           name="image" 
                           id="contact_image" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($contact['image'] ?? ''); ?>" 
                           placeholder="Select image from media library"
                           readonly>
                </div>
                <button type="button" 
                        onclick="openMediaLibrary('contact_image')" 
                        class="btn btn-primary"
                        style="background: linear-gradient(135deg, #2563eb, #4f46e5);">
                    🖼️ Select Image
                </button>
            </div>
            <p style="color: #666; font-size: 13px; margin-top: 8px;">This image will appear next to the contact information on the right side of the page.</p>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 30px;">Save contact details</button>
    </form>

    <?php $mapSettings = $db->fetchOne("SELECT * FROM map_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? []; ?>
    <form method="POST" action="api/content/map-settings.php" id="mapForm" onsubmit="return handleAdminFormSubmit(event, this);" style="margin-top:24px;background:#fff;border:1px solid #e7e5e4;border-radius:12px;padding:18px;">
        <h3 style="margin:0 0 8px;font-size:18px;">Or enter the coordinates</h3>
        <p style="color:#57534e;font-size:14px;line-height:1.5;margin:0 0 14px;">In Google Maps, right-click the school. The first line looks like <strong>-1.292100, 36.821900</strong>. The first number is latitude. The second is longitude. The Contact page uses this pin when no embed code is saved.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
            <div class="form-group">
                <label class="form-label">Latitude</label>
                <input type="number" step="0.00000001" name="latitude" class="form-input" value="<?php echo htmlspecialchars($mapSettings['latitude'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Longitude</label>
                <input type="number" step="0.00000001" name="longitude" class="form-input" value="<?php echo htmlspecialchars($mapSettings['longitude'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Zoom (1 to 20)</label>
                <input type="number" min="1" max="20" name="zoom" class="form-input" value="<?php echo htmlspecialchars($mapSettings['zoom'] ?? 15); ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Marker title</label>
            <input type="text" name="marker_title" class="form-input" value="<?php echo htmlspecialchars($mapSettings['marker_title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Marker description</label>
            <textarea name="marker_description" class="form-input" rows="2"><?php echo htmlspecialchars($mapSettings['marker_description'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save map location</button>
    </form>
</div>
