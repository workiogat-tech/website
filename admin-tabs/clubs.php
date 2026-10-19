<?php
/**
 * Clubs Tab
 */
$clubsData = $db->fetchOne("SELECT * FROM clubs_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];

// Parse clubs JSON
$clubsList = [];
if (!empty($clubsData['clubs'])) {
    $parsed = json_decode($clubsData['clubs'], true);
    if (is_array($parsed)) {
        $clubsList = $parsed;
    }
}
?>
<div class="content-card">
    <h2>👥 Clubs Page Management</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage school clubs and organizations.</p>
    
    <form method="POST" action="api/content/clubs.php" id="clubsForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <!-- Basic Content Section -->
        <div style="background: white; border-radius: 8px; padding: 24px; margin-bottom: 24px; border: 1px solid #e0e0e0;">
            <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 16px; color: #333;">Basic Content</h3>
            
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="title" class="form-input" 
                       value="<?php echo htmlspecialchars($clubsData['title'] ?? 'Clubs'); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Page Subtitle</label>
                <input type="text" name="subtitle" class="form-input" 
                       value="<?php echo htmlspecialchars($clubsData['subtitle'] ?? 'Extracurricular Activities and Student Organizations'); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Title Text Color</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">Choose the color for the main title text (e.g., "Clubs")</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <input type="color" 
                           id="title_color_picker"
                           name="title_color" 
                           value="<?php echo htmlspecialchars($clubsData['title_color'] ?? '#ffffff'); ?>"
                           style="width: 80px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer;"
                           onchange="document.getElementById('title_color_text').value=this.value; updateTitlePreview();">
                    <input type="text" 
                           id="title_color_text"
                           name="title_color_text" 
                           value="<?php echo htmlspecialchars($clubsData['title_color'] ?? '#ffffff'); ?>"
                           class="form-input" 
                           placeholder="#ffffff"
                           style="flex: 1; max-width: 200px;"
                           onchange="document.getElementById('title_color_picker').value=this.value; updateTitlePreview();"
                           pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                    <div id="title_color_preview" style="width: 100px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; background: <?php echo htmlspecialchars($clubsData['title_color'] ?? '#ffffff'); ?>; color: <?php echo htmlspecialchars($clubsData['title_color'] ?? '#ffffff'); ?>;">
                        Preview
                    </div>
                </div>
                <small style="color: #6b7280; display: block; margin-top: 5px;">💡 Tip: Use white (#ffffff) or light colors for dark backgrounds</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Subtitle Text Color</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">Choose the color for the subtitle text (e.g., "Extracurricular Activities...")</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <input type="color" 
                           id="subtitle_color_picker"
                           name="subtitle_color" 
                           value="<?php echo htmlspecialchars($clubsData['subtitle_color'] ?? '#ffffff'); ?>"
                           style="width: 80px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer;"
                           onchange="document.getElementById('subtitle_color_text').value=this.value; updateSubtitlePreview();">
                    <input type="text" 
                           id="subtitle_color_text"
                           name="subtitle_color_text" 
                           value="<?php echo htmlspecialchars($clubsData['subtitle_color'] ?? '#ffffff'); ?>"
                           class="form-input" 
                           placeholder="#ffffff"
                           style="flex: 1; max-width: 200px;"
                           onchange="document.getElementById('subtitle_color_picker').value=this.value; updateSubtitlePreview();"
                           pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                    <div id="subtitle_color_preview" style="width: 100px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; background: <?php echo htmlspecialchars($clubsData['subtitle_color'] ?? '#ffffff'); ?>; color: <?php echo htmlspecialchars($clubsData['subtitle_color'] ?? '#ffffff'); ?>;">
                        Preview
                    </div>
                </div>
                <small style="color: #6b7280; display: block; margin-top: 5px;">💡 Tip: Use white (#ffffff) or light colors for dark backgrounds</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Background Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $heroImageUrl = normalizeImageUrl($clubsData['hero_image'] ?? '');
                    if (!empty($heroImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview"
                             style="width: 96px; height: 64px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="hero_image" id="clubs_hero_image" class="form-input" 
                               value="<?php echo htmlspecialchars($clubsData['hero_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('clubs_hero_image')" 
                            class="btn btn-primary">🖼️ Select Image</button>
                </div>
            </div>
        </div>

        <!-- Clubs List Section -->
        <div style="background: white; border-radius: 8px; padding: 24px; margin-bottom: 24px; border: 1px solid #e0e0e0;">
            <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 16px; color: #333;">Clubs</h3>
            
            <div id="clubsList">
                <?php foreach ($clubsList as $index => $club): ?>
                    <div class="club-entry" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px; position: relative;">
                        <button type="button" onclick="removeClub(this)" 
                                style="position: absolute; top: 16px; right: 16px; background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                            🗑️ Remove
                        </button>
                        
                        <div style="padding-right: 120px;">
                            <div class="form-group">
                                <label class="form-label">Club Name</label>
                                <input type="text" name="club_name[]" class="form-input" 
                                       value="<?php echo htmlspecialchars($club['name'] ?? ''); ?>"
                                       placeholder="Enter club name">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Image</label>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <?php 
                                    $clubImageUrl = normalizeImageUrl($club['image'] ?? '');
                                    if (!empty($clubImageUrl)): ?>
                                        <img src="<?php echo htmlspecialchars($clubImageUrl); ?>" 
                                             alt="Club image"
                                             style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                                             onerror="this.onerror=null; this.style.display='none';">
                                    <?php endif; ?>
                                    <div style="flex: 1;">
                                        <input type="text" name="club_image[]" class="form-input club-image-input" 
                                               value="<?php echo htmlspecialchars($club['image'] ?? ''); ?>" 
                                               placeholder="Image URL will appear here" readonly>
                                    </div>
                                    <button type="button" onclick="openMediaLibraryForClub(this)" 
                                            class="btn btn-primary">🖼️</button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="club_description[]" class="form-input" rows="6" 
                                          placeholder="Enter club description..."><?php echo htmlspecialchars($club['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="button" onclick="removeClub(this)" 
                                    style="width: 100%; background: #dc2626; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                🗑️ Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" onclick="addClub()" 
                    style="width: 100%; background: #9333ea; color: white; border: none; padding: 16px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 16px;">
                ➕ Add Club
            </button>
        </div>

        <!-- Hidden field to store clubs JSON -->
        <input type="hidden" name="clubs" id="clubsJson" value='<?php echo htmlspecialchars(json_encode($clubsList)); ?>'>
        
        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px; font-size: 16px; font-weight: 600;">
                💾 Save Clubs Content
            </button>
            <button type="button" onclick="window.open('clubs.php', '_blank')" 
                    style="background: #6b7280; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600;">
                👁️ Preview
            </button>
        </div>
    </form>
</div>

<script>
let clubIndex = <?php echo count($clubsList); ?>;

function addClub() {
    const clubsList = document.getElementById('clubsList');
    const newClub = document.createElement('div');
    newClub.className = 'club-entry';
    newClub.style.cssText = 'border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px; position: relative;';
    newClub.innerHTML = `
        <button type="button" onclick="removeClub(this)" 
                style="position: absolute; top: 16px; right: 16px; background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
            🗑️ Remove
        </button>
        
        <div style="padding-right: 120px;">
            <div class="form-group">
                <label class="form-label">Club Name</label>
                <input type="text" name="club_name[]" class="form-input" placeholder="Enter club name">
            </div>
            
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="club_image[]" class="form-input club-image-input" 
                               placeholder="Image URL will appear here" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibraryForClub(this)" 
                            class="btn btn-primary">🖼️</button>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="club_description[]" class="form-input" rows="6" 
                          placeholder="Enter club description..."></textarea>
            </div>
            
            <button type="button" onclick="removeClub(this)" 
                    style="width: 100%; background: #dc2626; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                🗑️ Remove
            </button>
        </div>
    `;
    clubsList.appendChild(newClub);
    updateClubsJson();
}

function removeClub(button) {
    if (confirm('Are you sure you want to remove this club?')) {
        button.closest('.club-entry').remove();
        updateClubsJson();
    }
}

function updateClubsJson() {
    const clubs = [];
    const entries = document.querySelectorAll('.club-entry');
    
    entries.forEach(entry => {
        const name = entry.querySelector('input[name="club_name[]"]')?.value || '';
        const image = entry.querySelector('input[name="club_image[]"]')?.value || '';
        const description = entry.querySelector('textarea[name="club_description[]"]')?.value || '';
        
        if (name || image || description) {
            clubs.push({
                name: name,
                image: image,
                description: description
            });
        }
    });
    
    document.getElementById('clubsJson').value = JSON.stringify(clubs);
}

function openMediaLibraryForClub(button) {
    const input = button.previousElementSibling.querySelector('.club-image-input') || 
                  button.parentElement.querySelector('.club-image-input');
    if (input) {
        // Create a unique ID for this input if it doesn't have one
        if (!input.id) {
            input.id = 'club_image_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        // Store reference for preview update
        window.currentClubImageInput = input;
        openMediaLibrary(input.id);
    }
}

// Override selectImage to handle club images
document.addEventListener('DOMContentLoaded', function() {
    const originalSelectImage = window.selectImage;
    if (originalSelectImage) {
        window.selectImage = function(imageUrl) {
            if (window.currentClubImageInput) {
                const input = window.currentClubImageInput;
                input.value = imageUrl;
                
                // Update or create image preview
                const container = input.parentElement;
                let img = container.previousElementSibling;
                
                if (img && img.tagName === 'IMG') {
                    img.src = imageUrl;
                    img.style.display = 'block';
                } else {
                    // Create new image preview
                    img = document.createElement('img');
                    img.src = imageUrl;
                    img.alt = 'Club image';
                    img.style.cssText = 'width: 96px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;';
                    img.onerror = function() { this.style.display = 'none'; };
                    container.parentElement.insertBefore(img, container);
                }
                
                window.currentClubImageInput = null;
                updateClubsJson();
                
                // Close media library
                if (typeof closeMediaLibrary === 'function') {
                    closeMediaLibrary();
                }
            } else {
                originalSelectImage(imageUrl);
            }
        };
    }
});

// Update JSON when inputs change
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clubsForm');
    if (form) {
        form.addEventListener('input', function(e) {
            if (e.target.name && (e.target.name.includes('club_name') || 
                                  e.target.name.includes('club_image') || 
                                  e.target.name.includes('club_description'))) {
                updateClubsJson();
            }
        });
    }
    
    // Initialize color previews
    updateTitlePreview();
    updateSubtitlePreview();
});

// Update title color preview
function updateTitlePreview() {
    const color = document.getElementById('title_color_text').value || '#ffffff';
    const preview = document.getElementById('title_color_preview');
    if (preview) {
        preview.style.background = color;
        preview.style.color = color;
        // Add text shadow for visibility if color is light
        const rgb = hexToRgb(color);
        if (rgb && (rgb.r + rgb.g + rgb.b) > 500) {
            preview.style.textShadow = '2px 2px 4px rgba(0,0,0,0.5)';
        } else {
            preview.style.textShadow = 'none';
        }
    }
}

// Update subtitle color preview
function updateSubtitlePreview() {
    const color = document.getElementById('subtitle_color_text').value || '#ffffff';
    const preview = document.getElementById('subtitle_color_preview');
    if (preview) {
        preview.style.background = color;
        preview.style.color = color;
        // Add text shadow for visibility if color is light
        const rgb = hexToRgb(color);
        if (rgb && (rgb.r + rgb.g + rgb.b) > 500) {
            preview.style.textShadow = '2px 2px 4px rgba(0,0,0,0.5)';
        } else {
            preview.style.textShadow = 'none';
        }
    }
}

// Helper function to convert hex to RGB
function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}
</script>
