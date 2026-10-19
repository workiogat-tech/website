<?php
/**
 * Resources Tab - Dedicated admin for Resources page
 */
// Ensure parents_content table exists
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'parents_content'");
    if (!$tableExists) {
        $db->query("CREATE TABLE IF NOT EXISTS parents_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hero_caption VARCHAR(255) DEFAULT 'Resources & Information',
            hero_title VARCHAR(255) DEFAULT 'Resources',
            hero_image VARCHAR(500),
            section_title VARCHAR(255) DEFAULT 'Resources & Information',
            section_subtitle TEXT,
            resource_detail_sections TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }
} catch (Exception $e) {
    error_log("Resources table check error: " . $e->getMessage());
}

// Get resources content from parents_content table (Resources page uses this)
$resourcesContent = [];
try {
    // First check if table exists, if not create it
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'parents_content'");
    if (!$tableExists) {
        // Table doesn't exist, create it
        $db->query("CREATE TABLE IF NOT EXISTS parents_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hero_caption VARCHAR(255) DEFAULT 'Resources & Information',
            hero_title VARCHAR(255) DEFAULT 'Resources',
            hero_image VARCHAR(500),
            section_title VARCHAR(255) DEFAULT 'Resources & Information',
            section_subtitle TEXT,
            resource_detail_sections TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }
    
    // Now try to fetch content
    $resourcesContent = $db->fetchOne("SELECT * FROM parents_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
} catch (Exception $e) {
    error_log("Error in resources admin tab: " . $e->getMessage());
    // If fetch fails, just use empty array - form will work with defaults
    $resourcesContent = [];
}

// Parse resource_detail_sections
$resourceDetailSections = [];
if (!empty($resourcesContent['resource_detail_sections'])) {
    $resourceDetailSections = is_string($resourcesContent['resource_detail_sections']) 
        ? json_decode($resourcesContent['resource_detail_sections'], true) 
        : $resourcesContent['resource_detail_sections'];
    if (!is_array($resourceDetailSections)) $resourceDetailSections = [];
}

// Default sections if empty
if (empty($resourceDetailSections)) {
    $resourceDetailSections = [
        [
            'title' => 'Parent Handbook',
            'description' => 'The Parent Handbook provides clear guidelines on school policies, academic expectations, student behaviour, and communication between the school and parents. It helps parents understand school routines, responsibilities and support available for their children\'s education.',
            'image' => '',
            'id' => 'parent-handbook'
        ],
        [
            'title' => 'Academic Support',
            'description' => 'Parents are expected to support their children\'s learning by ensuring regular attendance, completing homework, and providing learning materials. They should communicate with teachers, attend meetings, and monitor academic progress. Parental support helps improve discipline, motivation, and overall student achievement.',
            'image' => '',
            'id' => 'academic-support'
        ],
        [
            'title' => 'Health & Safety',
            'description' => 'The school is committed to providing a safe and healthy learning environment for all learners and staff. Basic health services, hygiene practices, and safety rules are in place to prevent accidents and illness. Parents are encouraged to cooperate with the school to ensure children follow health and safety guidelines.',
            'image' => '',
            'id' => 'health-safety'
        ],
        [
            'title' => 'Transportation',
            'description' => 'Bus schedules and transportation policies. The school provides safe and reliable transportation services for learners using the school bus. All buses operate on designated routes and follow strict safety rules to protect learners. Parents are advised to ensure timely drop-off and pickup at assigned bus points.',
            'image' => '',
            'id' => 'transportation'
        ]
    ];
}
?>
<div class="content-card" style="padding: 0;">
    <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px; border: 1px solid #0ea5e9; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">📚 Resources & Information</h2>
            <button onclick="window.open('resources.php', '_blank')" style="padding: 8px 16px; background: #4b5563; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: background 0.2s;" onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#4b5563'">
                <span>🔗</span> Preview Page
            </button>
        </div>
        <p style="font-size: 14px; color: #6b7280; margin: 0;">Manage the Resources page content, including hero section, section headers, and resource detail sections.</p>
    </div>
    
    <form method="POST" action="api/content/resources.php" id="resourcesForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <!-- Hero Section -->
        <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 25px; border-radius: 12px; border: 2px solid #f59e0b; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">🎯 Page Header (Hero Section)</h3>
            
            <div class="form-group">
                <label class="form-label">Hero Caption</label>
                <input type="text" name="hero_caption" class="form-input" 
                       value="<?php echo htmlspecialchars($resourcesContent['hero_caption'] ?? 'Resources & Information'); ?>"
                       placeholder="Resources & Information">
                <small style="color: #6b7280; display: block; margin-top: 5px;">This appears as the caption above the main title in the hero section</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Title</label>
                <input type="text" name="hero_title" class="form-input" 
                       value="<?php echo htmlspecialchars($resourcesContent['hero_title'] ?? 'Resources'); ?>"
                       placeholder="Resources">
                <small style="color: #6b7280; display: block; margin-top: 5px;">This appears as the main title in the hero section</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Image</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears in the header/hero section of the Resources page</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $heroImageUrl = normalizeImageUrl($resourcesContent['hero_image'] ?? '');
                    if (!empty($heroImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview"
                             style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="hero_image" id="resources_hero_image" class="form-input" 
                               value="<?php echo htmlspecialchars($resourcesContent['hero_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('resources_hero_image')" 
                            class="btn btn-primary">🖼️ Select Image</button>
                </div>
            </div>
        </div>
        
        <!-- Main Content Section Header -->
        <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); padding: 25px; border-radius: 12px; border: 2px solid #0ea5e9; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">📋 Main Content Section Header</h3>
            
            <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="section_title" class="form-input" 
                       value="<?php echo htmlspecialchars($resourcesContent['section_title'] ?? 'Resources & Information'); ?>"
                       placeholder="Resources & Information">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Main section title that appears above the resource sections</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Section Subtitle</label>
                <textarea name="section_subtitle" class="form-input" rows="2" 
                          placeholder="Access important documents, policies, and resources to support your child's education."><?php echo htmlspecialchars($resourcesContent['section_subtitle'] ?? 'Access important documents, policies, and resources to support your child\'s education.'); ?></textarea>
                <small style="color: #6b7280; display: block; margin-top: 5px;">Subtitle/description that appears below the section title</small>
            </div>
        </div>
        
        <!-- Resource Detail Sections -->
        <div style="background: white; border-radius: 12px; padding: 24px; border: 2px solid #9333ea; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: #333; margin: 0;">📖 Resource Detail Sections</h3>
                <button type="button" onclick="addResourceSection()" 
                        style="padding: 10px 20px; background: #9333ea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    ➕ Add Resource Section
                </button>
            </div>
            <p style="color: #6b7280; margin-bottom: 20px; font-size: 14px;">Manage the detailed resource sections (Parent Handbook, Academic Support, etc.) that appear with side images in alternating layout.</p>
            
            <div id="resourceSectionsList">
                <?php foreach ($resourceDetailSections as $index => $section): ?>
                    <div class="resource-section-entry" style="border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 24px; position: relative; background: #f9fafb;">
                        <button type="button" onclick="removeResourceSection(this)" 
                                style="position: absolute; top: 16px; right: 16px; background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; transition: background 0.2s;"
                                onmouseover="this.style.background='#b91c1c'"
                                onmouseout="this.style.background='#dc2626'">
                            🗑️ Remove
                        </button>
                        
                        <div style="padding-right: 120px;">
                            <div class="form-group">
                                <label class="form-label">Section Title *</label>
                                <input type="text" name="resource_section_title[]" class="form-input" 
                                       value="<?php echo htmlspecialchars($section['title'] ?? ''); ?>"
                                       placeholder="e.g., Parent Handbook" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Section ID (for anchor links)</label>
                                <input type="text" name="resource_section_id[]" class="form-input" 
                                       value="<?php echo htmlspecialchars($section['id'] ?? ''); ?>"
                                       placeholder="e.g., parent-handbook">
                                <small style="color: #6b7280; display: block; margin-top: 5px;">Optional: Unique ID for this section (auto-generated from title if empty)</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Section Image</label>
                                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears alongside the section content in alternating layout</p>
                                <div style="display: flex; align-items: start; gap: 16px;">
                                    <?php 
                                    $sectionImageUrl = normalizeImageUrl($section['image'] ?? '');
                                    if (!empty($sectionImageUrl)): ?>
                                        <div style="flex-shrink: 0;">
                                            <img src="<?php echo htmlspecialchars($sectionImageUrl); ?>" 
                                                 alt="Section image preview"
                                                 style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                                                 onerror="this.onerror=null; this.style.display='none';">
                                        </div>
                                    <?php endif; ?>
                                    <div style="flex: 1;">
                                        <input type="text" name="resource_section_image[]" class="form-input resource-section-image-input" 
                                               value="<?php echo htmlspecialchars($section['image'] ?? ''); ?>" 
                                               placeholder="Select image from media library" readonly>
                                        <div style="display: flex; gap: 10px; margin-top: 12px;">
                                            <button type="button" onclick="openMediaLibraryForResourceSection(this)" 
                                                    class="btn btn-primary" style="flex: 1;">🖼️ Select Image</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description *</label>
                                <textarea name="resource_section_description[]" class="form-input" rows="6" 
                                          placeholder="Enter section description..." required><?php echo htmlspecialchars($section['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" onclick="addResourceSection()" 
                    style="width: 100%; background: #9333ea; color: white; border: none; padding: 16px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 16px; transition: background 0.2s;"
                    onmouseover="this.style.background='#7e22ce'"
                    onmouseout="this.style.background='#9333ea'">
                ➕ Add Resource Section
            </button>
        </div>
        
        <!-- Hidden field to store resource_detail_sections JSON -->
        <input type="hidden" name="resource_detail_sections" id="resourceSectionsJson" value='<?php echo htmlspecialchars(json_encode($resourceDetailSections)); ?>'>
        
        <div style="display: flex; gap: 12px; margin-top: 30px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
            <button type="submit" 
                    style="padding: 12px 24px; background: linear-gradient(135deg, #9333ea, #7e22ce); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(147, 51, 234, 0.4)'"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                <span>💾</span> Save Changes
            </button>
            <button type="button" 
                    onclick="window.open('resources.php', '_blank')"
                    style="padding: 12px 24px; background: #4b5563; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                    onmouseover="this.style.background='#374151'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.background='#4b5563'; this.style.transform='translateY(0)'">
                <span>🔗</span> Preview
            </button>
        </div>
    </form>
</div>

<script>
let resourceSectionIndex = <?php echo count($resourceDetailSections); ?>;

function addResourceSection() {
    const sectionsList = document.getElementById('resourceSectionsList');
    const newSection = document.createElement('div');
    newSection.className = 'resource-section-entry';
    newSection.style.cssText = 'border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 24px; position: relative; background: #f9fafb;';
    newSection.innerHTML = `
        <button type="button" onclick="removeResourceSection(this)" 
                style="position: absolute; top: 16px; right: 16px; background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; transition: background 0.2s;"
                onmouseover="this.style.background='#b91c1c'"
                onmouseout="this.style.background='#dc2626'">
            🗑️ Remove
        </button>
        
        <div style="padding-right: 120px;">
            <div class="form-group">
                <label class="form-label">Section Title *</label>
                <input type="text" name="resource_section_title[]" class="form-input" 
                       placeholder="e.g., Parent Handbook" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Section ID (for anchor links)</label>
                <input type="text" name="resource_section_id[]" class="form-input" 
                       placeholder="e.g., parent-handbook">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Optional: Unique ID for this section (auto-generated from title if empty)</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Section Image</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears alongside the section content in alternating layout</p>
                <div style="display: flex; align-items: start; gap: 16px;">
                    <div style="flex: 1;">
                        <input type="text" name="resource_section_image[]" class="form-input resource-section-image-input" 
                               placeholder="Select image from media library" readonly>
                        <div style="display: flex; gap: 10px; margin-top: 12px;">
                            <button type="button" onclick="openMediaLibraryForResourceSection(this)" 
                                    class="btn btn-primary" style="flex: 1;">🖼️ Select Image</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="resource_section_description[]" class="form-input" rows="6" 
                          placeholder="Enter section description..." required></textarea>
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
    // Find the input field - it's in the same parent container as the button
    // Structure: div.flex-1 > input + div > button
    const inputContainer = button.parentElement.parentElement; // Go up to the flex:1 div
    const input = inputContainer ? inputContainer.querySelector('input.resource-section-image-input') : null;
    
    // Fallback: search within the section entry
    if (!input) {
        const sectionEntry = button.closest('.resource-section-entry');
        if (sectionEntry) {
            const foundInput = sectionEntry.querySelector('input.resource-section-image-input');
            if (foundInput) {
                // Store the input reference
                const tempId = 'resource_section_image_' + Date.now();
                foundInput.setAttribute('data-temp-id', tempId);
                window['resourceImageInput_' + tempId] = foundInput;
                
                // Open media library
                openMediaLibrary(tempId);
                return;
            }
        }
    }
    
    if (!input) {
        console.error('Could not find resource section image input');
        return;
    }
    
    // Store the input reference
    const tempId = 'resource_section_image_' + Date.now();
    input.setAttribute('data-temp-id', tempId);
    window['resourceImageInput_' + tempId] = input;
    
    // Open media library
    openMediaLibrary(tempId);
}

// Override media library callback for resource sections
// This will be called by selectImage in admin.php after it handles resource sections
// But we keep it here as a backup handler
(function() {
    const originalSelectMediaImage = window.selectMediaImage;
    window.selectMediaImage = function(imageUrl) {
        // Check if we're working with a resource section image
        const currentField = window.currentImageField || '';
        if (currentField && currentField.startsWith('resource_section_image_')) {
            const input = window['resourceImageInput_' + currentField];
            if (input) {
                input.value = imageUrl;
                // Update preview if exists
                const container = input.closest('div[style*="display: flex"]');
                if (container) {
                    const existingImg = container.querySelector('img');
                    if (existingImg) {
                        existingImg.src = imageUrl;
                        existingImg.style.display = 'block';
                    } else {
                        // Add preview image
                        const previewDiv = container.querySelector('div[style*="flex-shrink: 0"]');
                        if (!previewDiv) {
                            const newPreview = document.createElement('div');
                            newPreview.style.cssText = 'flex-shrink: 0;';
                            newPreview.innerHTML = `<img src="${imageUrl}" alt="Section image preview" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;" onerror="this.onerror=null; this.style.display='none';">`;
                            container.insertBefore(newPreview, container.firstChild);
                        }
                    }
                }
                return; // Don't call original if we handled it
            }
        }
        
        // Call original handler if it exists
        if (originalSelectMediaImage) {
            originalSelectMediaImage(imageUrl);
        }
    };
})();

// Handle form submission
document.getElementById('resourcesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Collect resource sections
    const entries = document.querySelectorAll('.resource-section-entry');
    const sections = [];
    
    entries.forEach(entry => {
        const title = entry.querySelector('input[name="resource_section_title[]"]')?.value?.trim() || '';
        const id = entry.querySelector('input[name="resource_section_id[]"]')?.value?.trim() || '';
        const image = entry.querySelector('input[name="resource_section_image[]"]')?.value?.trim() || '';
        const description = entry.querySelector('textarea[name="resource_section_description[]"]')?.value?.trim() || '';
        
        if (title || description) {
            sections.push({
                title: title,
                id: id || strToId(title),
                image: image,
                description: description
            });
        }
    });
    
    // Update hidden field
    document.getElementById('resourceSectionsJson').value = JSON.stringify(sections);
    
    // Prepare form data
    const formData = new FormData(this);
    const data = {
        hero_caption: formData.get('hero_caption') || 'Resources & Information',
        hero_title: formData.get('hero_title') || 'Resources',
        hero_image: formData.get('hero_image') || '',
        section_title: formData.get('section_title') || 'Resources & Information',
        section_subtitle: formData.get('section_subtitle') || 'Access important documents, policies, and resources to support your child\'s education.',
        resource_detail_sections: sections
    };
    
    // Send AJAX request
    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse response:', text);
            throw new Error('Server returned invalid response');
        }
        
        if (result.error) {
            showToast(result.error, 'error');
        } else {
            showToast(result.message || 'Resources content saved successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to save content. Please try again.', 'error');
    });
    
    return false;
});

function strToId(str) {
    return str.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
</script>
