<?php
$parents = $db->fetchOne("SELECT * FROM parents_portal_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$additionalSlides = [];
if (!empty($parents['additional_slides'])) {
    $additionalSlides = is_string($parents['additional_slides']) ? json_decode($parents['additional_slides'], true) : $parents['additional_slides'];
    if (!is_array($additionalSlides)) $additionalSlides = [];
}

// Parse resource_detail_sections
$resourceDetailSections = [];
if (!empty($parents['resource_detail_sections'])) {
    $resourceDetailSections = is_string($parents['resource_detail_sections']) 
        ? json_decode($parents['resource_detail_sections'], true) 
        : $parents['resource_detail_sections'];
    if (!is_array($resourceDetailSections)) $resourceDetailSections = [];
}

// If no sections exist, provide default sections
if (empty($resourceDetailSections)) {
    $resourceDetailSections = [
        [
            'title' => 'Parent Handbook',
            'description' => 'The Parent Handbook provides clear guidelines on school policies, academic expectations, student behaviour, and communication between the school and parents. It helps parents understand school routines, responsibilities and support available for their children\'s education.',
            'image' => ''
        ],
        [
            'title' => 'Academic Support',
            'description' => 'Parents are expected to support their children\'s learning by ensuring regular attendance, completing homework, and providing learning materials. They should communicate with teachers, attend meetings, and monitor academic progress. Parental support helps improve discipline, motivation, and overall student achievement.',
            'image' => ''
        ],
        [
            'title' => 'Health & Safety',
            'description' => 'The school is committed to providing a safe and healthy learning environment for all learners and staff. Basic health services, hygiene practices, and safety rules are in place to prevent accidents and illness. Parents are encouraged to cooperate with the school to ensure children follow health and safety guidelines.',
            'image' => ''
        ],
        [
            'title' => 'Transportation',
            'description' => 'Bus schedules and transportation policies. The school provides safe and reliable transportation services for learners using the school bus. All buses operate on designated routes and follow strict safety rules to protect learners. Parents are advised to ensure timely drop-off and pickup at assigned bus points.',
            'image' => ''
        ]
    ];
}
?>
<div class="content-card">
    <h2>👥 Parents Portal</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage parents portal content.</p>
    
    <form method="POST" action="api/content/parents.php" id="parentsForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-input" 
                   value="<?php echo htmlspecialchars($parents['title'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Subtitle</label>
            <input type="text" name="subtitle" class="form-input" 
                   value="<?php echo htmlspecialchars($parents['subtitle'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="5"><?php echo htmlspecialchars($parents['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Hero Image</label>
            <div style="display: flex; align-items: center; gap: 15px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($parents['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                         alt="Hero image preview"
                         style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                         onerror="this.onerror=null; this.style.display='none';">
                <?php endif; ?>
                <div style="flex: 1;">
                    <input type="text" name="hero_image" id="parents_hero_image" class="form-input" 
                           value="<?php echo htmlspecialchars($parents['hero_image'] ?? ''); ?>" 
                           placeholder="Select image from media library" readonly>
                </div>
                <button type="button" onclick="openMediaLibrary('parents_hero_image')" 
                        class="btn btn-primary">🖼️ Select Image</button>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Additional Slide Images</label>
            <button type="button" onclick="openMediaLibrary('parents_slides')" 
                    class="btn btn-primary" style="margin-bottom: 15px;">➕ Add Slide Image</button>
            <div id="parentsSlidesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                <?php foreach ($additionalSlides as $slide): ?>
                    <div style="position: relative; border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px;">
                        <?php $slideUrl = normalizeImageUrl($slide); ?>
                        <img src="<?php echo htmlspecialchars($slideUrl); ?>" 
                             alt="Slide image"
                             style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;"
                             onerror="this.onerror=null; this.style.display='none';">
                        <input type="hidden" name="additional_slides[]" value="<?php echo htmlspecialchars($slide); ?>">
                        <button type="button" onclick="this.parentElement.remove()" 
                                class="btn btn-danger" style="width: 100%; padding: 5px; font-size: 12px;">🗑️ Remove</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Quick Actions (JSON)</label>
            <textarea name="quick_actions" class="form-input" rows="5"><?php echo htmlspecialchars($parents['quick_actions'] ?? '[]'); ?></textarea>
        </div>
        
        <!-- Resource Detail Sections -->
        <div style="background: white; border-radius: 8px; padding: 24px; margin-bottom: 24px; border: 1px solid #e0e0e0; margin-top: 30px;">
            <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 16px; color: #333;">Resource Detail Sections</h3>
            <p style="color: #666; margin-bottom: 20px; font-size: 14px;">Manage the detailed resource sections (Parent Handbook, Academic Support, etc.) that appear with side images in alternating layout.</p>
            
            <div id="resourceSectionsList">
                <?php foreach ($resourceDetailSections as $index => $section): ?>
                    <div class="resource-section-entry" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px; position: relative;">
                        <button type="button" onclick="removeResourceSection(this)" 
                                style="position: absolute; top: 16px; right: 16px; background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                            🗑️ Remove
                        </button>
                        
                        <div style="padding-right: 120px;">
                            <div class="form-group">
                                <label class="form-label">Section Title</label>
                                <input type="text" name="resource_section_title[]" class="form-input" 
                                       value="<?php echo htmlspecialchars($section['title'] ?? ''); ?>"
                                       placeholder="e.g., Parent Handbook">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Section Image</label>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <?php 
                                    $sectionImageUrl = normalizeImageUrl($section['image'] ?? '');
                                    if (!empty($sectionImageUrl)): ?>
                                        <img src="<?php echo htmlspecialchars($sectionImageUrl); ?>" 
                                             alt="Section image"
                                             style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                                             onerror="this.onerror=null; this.style.display='none';">
                                    <?php endif; ?>
                                    <div style="flex: 1;">
                                        <input type="text" name="resource_section_image[]" class="form-input resource-section-image-input" 
                                               value="<?php echo htmlspecialchars($section['image'] ?? ''); ?>" 
                                               placeholder="Image URL will appear here" readonly>
                                    </div>
                                    <button type="button" onclick="openMediaLibraryForResourceSection(this)" 
                                            class="btn btn-primary">🖼️</button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="resource_section_description[]" class="form-input" rows="6" 
                                          placeholder="Enter section description..."><?php echo htmlspecialchars($section['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" onclick="addResourceSection()" 
                    style="width: 100%; background: #9333ea; color: white; border: none; padding: 16px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 16px;">
                ➕ Add Resource Section
            </button>
        </div>
        
        <!-- Hidden field to store resource_detail_sections JSON -->
        <input type="hidden" name="resource_detail_sections" id="resourceSectionsJson" value='<?php echo htmlspecialchars(json_encode($resourceDetailSections)); ?>'>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 30px;">💾 Save Changes</button>
    </form>
</div>

<script>
let resourceSectionIndex = <?php echo count($resourceDetailSections); ?>;

function addResourceSection() {
    const sectionsList = document.getElementById('resourceSectionsList');
    const newSection = document.createElement('div');
    newSection.className = 'resource-section-entry';
    newSection.style.cssText = 'border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px; position: relative;';
    newSection.innerHTML = `
        <button type="button" onclick="removeResourceSection(this)" 
                style="position: absolute; top: 16px; right: 16px; background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
            🗑️ Remove
        </button>
        
        <div style="padding-right: 120px;">
            <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="resource_section_title[]" class="form-input" 
                       placeholder="e.g., Parent Handbook">
            </div>
            
            <div class="form-group">
                <label class="form-label">Section Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="resource_section_image[]" class="form-input resource-section-image-input" 
                               placeholder="Image URL will appear here" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibraryForResourceSection(this)" 
                            class="btn btn-primary">🖼️</button>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="resource_section_description[]" class="form-input" rows="6" 
                          placeholder="Enter section description..."></textarea>
            </div>
        </div>
    `;
    sectionsList.appendChild(newSection);
    resourceSectionIndex++;
}

function removeResourceSection(button) {
    if (confirm('Are you sure you want to remove this resource section?')) {
        button.closest('.resource-section-entry').remove();
    }
}

function openMediaLibraryForResourceSection(button) {
    const input = button.previousElementSibling.querySelector('input.resource-section-image-input');
    if (input && typeof openMediaLibrary === 'function') {
        openMediaLibrary(input.id || input.name);
    } else {
        // Fallback: create a temporary input with ID
        const tempId = 'resource_section_image_' + Date.now();
        input.id = tempId;
        openMediaLibrary(tempId);
    }
}

// Update hidden JSON field before form submission
document.getElementById('parentsForm')?.addEventListener('submit', function(e) {
    const sections = [];
    const entries = document.querySelectorAll('.resource-section-entry');
    
    entries.forEach((entry) => {
        const title = entry.querySelector('input[name="resource_section_title[]"]')?.value?.trim() || '';
        const image = entry.querySelector('input[name="resource_section_image[]"]')?.value?.trim() || '';
        const description = entry.querySelector('textarea[name="resource_section_description[]"]')?.value?.trim() || '';
        
        if (title || image || description) {
            sections.push({
                title: title,
                image: image,
                description: description
            });
        }
    });
    
    document.getElementById('resourceSectionsJson').value = JSON.stringify(sections);
});
</script>
