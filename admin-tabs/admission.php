<?php
// Ensure table exists before querying
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'admission_content'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS admission_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hero_image VARCHAR(500),
            olevel_content JSON,
            alevel_content JSON,
            fee_structure JSON,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} catch (Exception $e) {
    error_log("Admission table creation error: " . $e->getMessage());
}

// Now safely fetch admission content
try {
    $admission = $db->fetchOne("SELECT * FROM admission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
} catch (Exception $e) {
    error_log("Admission fetch error: " . $e->getMessage());
    $admission = [];
}

// Parse admission levels (new flexible system)
$admissionLevels = [];
if (!empty($admission['admission_levels'])) {
    $admissionLevels = is_string($admission['admission_levels']) ? json_decode($admission['admission_levels'], true) : $admission['admission_levels'];
    if (!is_array($admissionLevels)) $admissionLevels = [];
}

// Backward compatibility: If no levels, use old format
if (empty($admissionLevels)) {
    $olevelContent = [];
    $alevelContent = [];
    
    if (!empty($admission['olevel_content'])) {
        $olevelContent = is_string($admission['olevel_content']) ? json_decode($admission['olevel_content'], true) : $admission['olevel_content'];
        if (!is_array($olevelContent)) $olevelContent = [];
    }
    
    if (!empty($admission['alevel_content'])) {
        $alevelContent = is_string($admission['alevel_content']) ? json_decode($admission['alevel_content'], true) : $admission['alevel_content'];
        if (!is_array($alevelContent)) $alevelContent = [];
    }
    
    // Convert to new format
    if (!empty($olevelContent['title']) || !empty($olevelContent['description'])) {
        $admissionLevels[] = [
            'icon' => '📚',
            'heading' => "O'LEVEL Information",
            'title' => $olevelContent['title'] ?? "O'LEVEL",
            'description' => $olevelContent['description'] ?? ''
        ];
    }
    
    if (!empty($alevelContent['title']) || !empty($alevelContent['description'])) {
        $admissionLevels[] = [
            'icon' => '📚',
            'heading' => "A'LEVEL Information",
            'title' => $alevelContent['title'] ?? "A'LEVEL",
            'description' => $alevelContent['description'] ?? ''
        ];
    }
}

// Default values if still empty
if (empty($admissionLevels)) {
    $admissionLevels = [
        [
            'icon' => '🎒',
            'heading' => "Nursery Information",
            'title' => "Nursery",
            'description' => ''
        ],
        [
            'icon' => '📚',
            'heading' => "Primary Information",
            'title' => "Primary",
            'description' => ''
        ]
    ];
}

$feeStructure = [];
if (!empty($admission['fee_structure'])) {
    $feeStructure = is_string($admission['fee_structure']) ? json_decode($admission['fee_structure'], true) : $admission['fee_structure'];
    if (!is_array($feeStructure)) $feeStructure = [];
}
?>
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0 0 8px 0;">📝 Admission Management</h2>
            <p style="color: #6b7280; margin: 0;">Manage admission page content including hero image, O'LEVEL and A'LEVEL information, and fee structure.</p>
        </div>
        <button onclick="window.open('admission.php', '_blank')" style="padding: 8px 16px; background: #4b5563; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <span>🔗</span> Preview
        </button>
    </div>
    
    <form method="POST" action="api/content/admission.php" id="admissionForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <!-- Hero Image Section -->
        <div style="background: #f9fafb; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">🖼️ Hero Image</h3>
            
            <div class="form-group">
                <label class="form-label">Hero Background Image</label>
                <div style="display: flex; align-items: flex-start; gap: 15px;">
                    <?php 
                    $heroImage = $admission['hero_image'] ?? '';
                    if (!empty($heroImage)): 
                        $heroImageUrl = normalizeImageUrl($heroImage);
                    ?>
                        <div style="flex-shrink: 0;">
                            <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" alt="Hero Image" style="width: 200px; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <p style="text-align: center; font-size: 11px; color: #666; margin-top: 5px;">Preview</p>
                        </div>
                    <?php else: ?>
                        <div style="flex-shrink: 0;">
                            <div style="width: 200px; height: 120px; background: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #9ca3af; border: 2px dashed #d1d5db;">
                                <div style="text-align: center;">
                                    <span style="font-size: 24px;">🖼️</span>
                                    <p style="font-size: 11px; color: #999; margin-top: 5px;">No image</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="hero_image" id="admission_hero_image" class="form-input" 
                               value="<?php echo htmlspecialchars($heroImage); ?>" 
                               placeholder="Select image from media library" readonly>
                        <button type="button" onclick="openMediaLibrary('admission_hero_image')" 
                                class="btn btn-primary" style="margin-top: 10px;">🖼️ Select Image</button>
                        <p style="font-size: 12px; color: #6b7280; margin-top: 8px;">Select a background image from your media library for the admission hero section. Recommended size: 1920x600px</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Admission Levels Section -->
        <div style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 5px 0;">📋 Admission Levels</h3>
                    <p style="color: #6b7280; margin: 0; font-size: 14px;">Add, edit, or remove admission levels. Customize icons and headings for each level.</p>
                </div>
                <button type="button" onclick="addAdmissionLevel()" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    <span>➕</span> Add Level
                </button>
            </div>
            
            <div id="admissionLevelsContainer" class="admission-accordion-list">
                <?php foreach ($admissionLevels as $index => $level): ?>
                <div class="admission-accordion-item" data-index="<?php echo $index; ?>">
                    <div class="admission-accordion-header" onclick="toggleAdmissionAccordion(this)">
                        <div class="admission-accordion-chevron">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                        <div class="admission-accordion-icon">
                            <span style="font-size: 24px;"><?php echo htmlspecialchars($level['icon'] ?? '📚'); ?></span>
                        </div>
                        <span class="admission-accordion-text"><?php echo htmlspecialchars($level['heading'] ?: $level['title'] ?: 'Level ' . ($index + 1)); ?></span>
                        <button type="button" onclick="event.stopPropagation(); removeAdmissionLevel(<?php echo $index; ?>)" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px; margin-left: auto;">🗑️ Remove</button>
                    </div>
                    <div class="admission-accordion-content">
                        <div style="padding: 20px;">
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; margin-bottom: 15px;">
                                <div class="form-group">
                                    <label class="form-label">Icon (Emoji)</label>
                                    <input type="text" name="admission_levels[<?php echo $index; ?>][icon]" class="form-input" 
                                           value="<?php echo htmlspecialchars($level['icon'] ?? '📚'); ?>"
                                           placeholder="📚" maxlength="10">
                                    <small style="color: #6b7280; font-size: 12px;">Enter an emoji icon (e.g., 📚, 🎓, 📖)</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Section Heading</label>
                                    <input type="text" name="admission_levels[<?php echo $index; ?>][heading]" class="form-input" 
                                           value="<?php echo htmlspecialchars($level['heading'] ?? ''); ?>"
                                           placeholder="e.g., O'LEVEL Information">
                                    <small style="color: #6b7280; font-size: 12px;">This appears as the section heading in the admin panel</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Title</label>
                                <input type="text" name="admission_levels[<?php echo $index; ?>][title]" class="form-input" 
                                       value="<?php echo htmlspecialchars($level['title'] ?? ''); ?>"
                                       placeholder="e.g., O'LEVEL">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="admission_levels[<?php echo $index; ?>][description]" class="form-input" rows="4" 
                                          placeholder="Enter admission information for this level..."><?php echo htmlspecialchars($level['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div id="emptyLevelsMessage" style="<?php echo !empty($admissionLevels) ? 'display: none;' : ''; ?> text-align: center; padding: 40px; color: #999; background: #f9fafb; border-radius: 8px; border: 2px dashed #e5e7eb;">
                <p style="margin: 0;">No levels added yet. Click "+ Add Level" to get started.</p>
            </div>
        </div>
        
        <!-- Fee Structure Section -->
        <div style="background: #f9fafb; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">💰 Fee Structure Image</h3>
            
            <div class="form-group">
                <label class="form-label">Fee Structure Image</label>
                <div style="display: flex; align-items: flex-start; gap: 15px;">
                    <?php 
                    $feeImage = $feeStructure['image'] ?? '';
                    if (!empty($feeImage)): 
                        $feeImageUrl = normalizeImageUrl($feeImage);
                    ?>
                        <div style="flex-shrink: 0;">
                            <img src="<?php echo htmlspecialchars($feeImageUrl); ?>" alt="Fee Structure" style="width: 200px; height: auto; max-height: 300px; object-fit: contain; border-radius: 8px; border: 2px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <p style="text-align: center; font-size: 11px; color: #666; margin-top: 5px;">Preview</p>
                        </div>
                    <?php else: ?>
                        <div style="flex-shrink: 0;">
                            <div style="width: 200px; height: 150px; background: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #9ca3af; border: 2px dashed #d1d5db;">
                                <div style="text-align: center;">
                                    <span style="font-size: 24px;">🖼️</span>
                                    <p style="font-size: 11px; color: #999; margin-top: 5px;">No image</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="fee_structure_image" id="admission_fee_structure_image" class="form-input" 
                               value="<?php echo htmlspecialchars($feeImage); ?>" 
                               placeholder="Select image from media library" readonly>
                        <button type="button" onclick="openMediaLibrary('admission_fee_structure_image')" 
                                class="btn btn-primary" style="margin-top: 10px;">🖼️ Select Image</button>
                        <p style="font-size: 12px; color: #6b7280; margin-top: 8px;">Select an image from your media library showing the fee structure. This will be displayed at the top of the admission page.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
            <button type="button" onclick="window.location.reload()" style="padding: 12px 24px; background: #6b7280; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                Cancel
            </button>
            <button type="submit" style="padding: 12px 24px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                💾 Save Changes
            </button>
        </div>
    </form>
</div>

<script>
let admissionLevelIndex = <?php echo count($admissionLevels); ?>;

// Update image previews when images are selected from media library
function updateImagePreview(fieldId, imageUrl) {
    if (!imageUrl) return;
    
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Find the parent container
    const parentContainer = field.closest('div[style*="display: flex"]');
    if (!parentContainer) return;
    
    // Find the preview area (img or placeholder div)
    const previewArea = parentContainer.querySelector('img') || parentContainer.querySelector('div[style*="No image"]')?.parentElement;
    if (!previewArea) return;
    
    if (previewArea.tagName === 'IMG') {
        // Update existing image
        previewArea.src = imageUrl;
        previewArea.style.display = 'block';
    } else {
        // Replace placeholder with image
        const newImg = document.createElement('img');
        newImg.src = imageUrl;
        newImg.alt = fieldId.includes('hero') ? 'Hero Image' : 'Fee Structure';
        newImg.style.cssText = fieldId.includes('hero') 
            ? 'width: 200px; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'
            : 'width: 200px; height: auto; max-height: 300px; object-fit: contain; border-radius: 8px; border: 2px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        
        const previewContainer = document.createElement('div');
        previewContainer.style.cssText = 'flex-shrink: 0;';
        previewContainer.appendChild(newImg);
        
        const previewLabel = document.createElement('p');
        previewLabel.textContent = 'Preview';
        previewLabel.style.cssText = 'text-align: center; font-size: 11px; color: #666; margin-top: 5px;';
        previewContainer.appendChild(previewLabel);
        
        previewArea.parentElement.replaceChild(previewContainer, previewArea.parentElement.querySelector('div[style*="flex-shrink: 0"]'));
    }
}

// Override media library selection callback for admission images
const originalSelectImage = window.selectImage;
window.selectImage = function(imageUrl) {
    if (typeof originalSelectImage === 'function') {
        originalSelectImage(imageUrl);
    }
    
    // Update preview for admission images
    const currentField = window.currentImageField || '';
    if (currentField === 'admission_hero_image' || currentField === 'admission_fee_structure_image') {
        updateImagePreview(currentField, imageUrl);
    }
};

function addAdmissionLevel() {
    const container = document.getElementById('admissionLevelsContainer');
    const emptyMessage = document.getElementById('emptyLevelsMessage');
    const index = admissionLevelIndex++;
    
    const levelHtml = `
        <div class="admission-accordion-item" data-index="${index}">
            <div class="admission-accordion-header" onclick="toggleAdmissionAccordion(this)">
                <div class="admission-accordion-chevron">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="admission-accordion-icon">
                    <span style="font-size: 24px;">📚</span>
                </div>
                <span class="admission-accordion-text">Level ${index + 1}</span>
                <button type="button" onclick="event.stopPropagation(); removeAdmissionLevel(${index})" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px; margin-left: auto;">🗑️ Remove</button>
            </div>
            <div class="admission-accordion-content">
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group">
                            <label class="form-label">Icon (Emoji)</label>
                            <input type="text" name="admission_levels[${index}][icon]" class="form-input" 
                                   value="📚" placeholder="📚" maxlength="10">
                            <small style="color: #6b7280; font-size: 12px;">Enter an emoji icon (e.g., 📚, 🎓, 📖)</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Section Heading</label>
                            <input type="text" name="admission_levels[${index}][heading]" class="form-input" 
                                   value="" placeholder="e.g., NURSERY Information">
                            <small style="color: #6b7280; font-size: 12px;">This appears as the section heading in the admin panel</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" name="admission_levels[${index}][title]" class="form-input" 
                               value="" placeholder="e.g., NURSERY">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="admission_levels[${index}][description]" class="form-input" rows="4" 
                                  placeholder="Enter admission information for this level..."></textarea>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', levelHtml);
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    // Scroll to the new level
    const newLevel = container.lastElementChild;
    if (newLevel) {
        newLevel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        newLevel.querySelector('input[name*="[heading]"]').focus();
    }
}

function removeAdmissionLevel(index) {
    if (!confirm('Are you sure you want to remove this level?')) return;
    
    const levelItem = document.querySelector(`.admission-accordion-item[data-index="${index}"]`);
    if (levelItem) {
        levelItem.remove();
        
        // Check if any levels remain
        const container = document.getElementById('admissionLevelsContainer');
        const emptyMessage = document.getElementById('emptyLevelsMessage');
        const remainingLevels = container.querySelectorAll('.admission-accordion-item');
        
        if (remainingLevels.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        }
        
        // Renumber remaining levels
        remainingLevels.forEach((item, idx) => {
            const textSpan = item.querySelector('.admission-accordion-text');
            if (textSpan && textSpan.textContent.startsWith('Level ')) {
                textSpan.textContent = `Level ${idx + 1}`;
            }
        });
    }
}

// Accordion Toggle Function for Admission Levels
function toggleAdmissionAccordion(header) {
    if (!header) return;
    
    const item = header.closest('.admission-accordion-item');
    if (!item) return;
    
    const content = item.querySelector('.admission-accordion-content');
    const chevron = header.querySelector('.admission-accordion-chevron svg');
    const isExpanded = item.classList.contains('expanded');
    
    // Toggle current accordion
    if (isExpanded) {
        item.classList.remove('expanded');
        if (content) {
            content.style.maxHeight = null;
        }
        if (chevron) {
            chevron.style.transform = 'rotate(0deg)';
        }
    } else {
        item.classList.add('expanded');
        if (content) {
            // Set max-height to actual content height
            content.style.maxHeight = content.scrollHeight + 'px';
        }
        if (chevron) {
            chevron.style.transform = 'rotate(180deg)';
        }
    }
}

// Update icon display when icon input changes
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to icon inputs to update display
    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('[icon]')) {
            const iconInput = e.target;
            const accordionItem = iconInput.closest('.admission-accordion-item');
            if (accordionItem) {
                const iconDisplay = accordionItem.querySelector('.admission-accordion-icon span');
                if (iconDisplay) {
                    iconDisplay.textContent = iconInput.value || '📚';
                }
            }
        }
        
        // Update heading text when heading input changes
        if (e.target.name && e.target.name.includes('[heading]')) {
            const headingInput = e.target;
            const accordionItem = headingInput.closest('.admission-accordion-item');
            if (accordionItem) {
                const textSpan = accordionItem.querySelector('.admission-accordion-text');
                if (textSpan && headingInput.value) {
                    textSpan.textContent = headingInput.value;
                }
            }
        }
    });
    
    // Initialize accordions - expand first one by default
    const firstAccordion = document.querySelector('.admission-accordion-item');
    if (firstAccordion) {
        const firstHeader = firstAccordion.querySelector('.admission-accordion-header');
        if (firstHeader) {
            setTimeout(() => {
                toggleAdmissionAccordion(firstHeader);
            }, 100);
        }
    }
    
    // Update max-height when window is resized
    window.addEventListener('resize', function() {
        document.querySelectorAll('.admission-accordion-item.expanded').forEach(item => {
            const content = item.querySelector('.admission-accordion-content');
            if (content) {
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
});
</script>

<style>
/* Admission Accordion Styles - Based on give-back.php accordion */
.admission-accordion-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 20px;
}

.admission-accordion-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.admission-accordion-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.admission-accordion-header {
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    cursor: pointer;
    transition: background 0.2s;
    background: white;
}

.admission-accordion-header:hover {
    background: #f9fafb;
}

.admission-accordion-chevron {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #fce7f3;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #ec4899;
    transition: transform 0.3s ease;
}

.admission-accordion-chevron svg {
    width: 16px;
    height: 16px;
    transition: transform 0.3s ease;
}

.admission-accordion-item.expanded .admission-accordion-chevron svg {
    transform: rotate(180deg);
}

.admission-accordion-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 40px;
    height: 40px;
}

.admission-accordion-text {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    flex: 1;
}

.admission-accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, padding 0.4s ease;
    padding: 0 24px;
}

.admission-accordion-item.expanded .admission-accordion-content {
    padding: 0 24px 24px 24px;
    max-height: 2000px; /* Fallback for when JS doesn't set inline style */
}
</style>
