<?php
/**
 * Curriculum Management Tab
 */
$db = getDB();

// Ensure tables exist before querying
try {
    $db->query("CREATE TABLE IF NOT EXISTS curriculum_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_title VARCHAR(200),
        page_subtitle VARCHAR(200),
        intro_text TEXT,
        hero_image VARCHAR(500),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $db->query("CREATE TABLE IF NOT EXISTS curriculum_programs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        program_title VARCHAR(200),
        description TEXT,
        subjects TEXT,
        order_index INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $db->query("CREATE TABLE IF NOT EXISTS curriculum_why_choose (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_title VARCHAR(200),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $db->query("CREATE TABLE IF NOT EXISTS curriculum_why_choose_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        icon_name VARCHAR(100),
        title VARCHAR(200),
        description TEXT,
        order_index INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {
    error_log("Curriculum tables creation error: " . $e->getMessage());
}

// Get curriculum content with error handling
try {
    $curriculumContent = $db->fetchOne("SELECT * FROM curriculum_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
} catch (Exception $e) {
    error_log("Error fetching curriculum content: " . $e->getMessage());
    $curriculumContent = [];
}

try {
    $curriculumPrograms = $db->fetchAll("SELECT * FROM curriculum_programs WHERE is_active = 1 ORDER BY order_index ASC");
} catch (Exception $e) {
    error_log("Error fetching curriculum programs: " . $e->getMessage());
    $curriculumPrograms = [];
}

try {
    $curriculumWhyChoose = $db->fetchOne("SELECT * FROM curriculum_why_choose WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
} catch (Exception $e) {
    error_log("Error fetching curriculum why choose: " . $e->getMessage());
    $curriculumWhyChoose = [];
}

try {
    $curriculumWhyChooseItems = $db->fetchAll("SELECT * FROM curriculum_why_choose_items WHERE is_active = 1 ORDER BY order_index ASC");
} catch (Exception $e) {
    error_log("Error fetching curriculum why choose items: " . $e->getMessage());
    $curriculumWhyChooseItems = [];
}

// Parse JSON fields
$whyChooseItems = [];
if (!empty($curriculumWhyChooseItems)) {
    $whyChooseItems = $curriculumWhyChooseItems;
}
?>
<div class="content-card curriculum-card">
    <h2>📚 Curriculum Management</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage curriculum page content, educational programs, and why choose section.</p>
    
    <form method="POST" action="api/content/curriculum.php" id="curriculumForm" onsubmit="return handleCurriculumFormSubmit(event, this);">
        <!-- Basic Content -->
        <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Basic Content</h3>
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="page_title" class="form-input" 
                       value="<?php echo htmlspecialchars($curriculumContent['page_title'] ?? 'Curriculum'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Page Subtitle</label>
                <input type="text" name="page_subtitle" class="form-input" 
                       value="<?php echo htmlspecialchars($curriculumContent['page_subtitle'] ?? 'Comprehensive Educational Programs'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Intro Text</label>
                <textarea name="intro_text" class="form-input" rows="4"><?php echo htmlspecialchars($curriculumContent['intro_text'] ?? 'We offer a comprehensive curriculum designed to develop well-rounded individuals prepared for success in all areas of life.'); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Hero Background Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $heroImageUrl = normalizeImageUrl($curriculumContent['hero_image'] ?? '');
                    if (!empty($heroImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview"
                             style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="hero_image" id="curriculum_hero_image" class="form-input" 
                               value="<?php echo htmlspecialchars($curriculumContent['hero_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('curriculum_hero_image')" 
                            class="btn btn-primary">🖼️ Select Image</button>
                </div>
            </div>
        </div>
        
        <!-- Educational Programs -->
        <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Educational Programs</h3>
                <button type="button" onclick="addProgram()" class="btn btn-primary">➕ Add Program</button>
            </div>
            
            <div id="programsContainer">
                <?php if (!empty($curriculumPrograms)): ?>
                    <?php foreach ($curriculumPrograms as $index => $program): ?>
                        <div class="program-item" data-program-id="<?php echo $program['id']; ?>" style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Program <?php echo $index + 1; ?></h4>
                                <button type="button" onclick="removeProgram(<?php echo $program['id']; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                                    🗑️ Remove Program
                                </button>
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label class="form-label">Program Title</label>
                                <input type="text" name="programs[<?php echo $program['id']; ?>][program_title]" class="form-input" 
                                       value="<?php echo htmlspecialchars($program['program_title'] ?? ''); ?>" 
                                       placeholder="e.g., Nursery Education Program">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label class="form-label">Description</label>
                                <textarea name="programs[<?php echo $program['id']; ?>][description]" class="form-input" rows="3" 
                                          placeholder="Program description..."><?php echo htmlspecialchars($program['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Subjects (one per line)</label>
                                <textarea name="programs[<?php echo $program['id']; ?>][subjects]" class="form-input" rows="4" 
                                          placeholder="Language Development&#10;Early Mathematics&#10;Creative Arts"><?php echo htmlspecialchars($program['subjects'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Why Choose Our Curriculum Section -->
        <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Why Choose Our Curriculum Section</h3>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Section Title</label>
                <input type="text" name="why_choose_title" class="form-input" 
                       value="<?php echo htmlspecialchars($curriculumWhyChoose['section_title'] ?? 'Why Choose Our Curriculum?'); ?>">
            </div>
            
            <div style="margin-top: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Items</h4>
                    <button type="button" onclick="addWhyChooseItem()" class="btn btn-primary">➕ Add Item</button>
                </div>
                
                <div id="whyChooseItemsContainer">
                    <?php if (!empty($whyChooseItems)): ?>
                        <?php foreach ($whyChooseItems as $index => $item): ?>
                            <div class="why-choose-item" data-item-id="<?php echo $item['id']; ?>" style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                    <h5 style="font-size: 14px; font-weight: 600; color: #333; margin: 0;">Item <?php echo $index + 1; ?></h5>
                                    <button type="button" onclick="removeWhyChooseItem(<?php echo $item['id']; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                                        🗑️ Remove Item
                                    </button>
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label class="form-label">Icon Name (e.g., GraduationCap, Users, Award, BookOpen)</label>
                                    <input type="text" name="why_choose_items[<?php echo $item['id']; ?>][icon_name]" class="form-input" 
                                           value="<?php echo htmlspecialchars($item['icon_name'] ?? ''); ?>" 
                                           placeholder="GraduationCap">
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="why_choose_items[<?php echo $item['id']; ?>][title]" class="form-input" 
                                           value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>" 
                                           placeholder="Academic Excellence">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="why_choose_items[<?php echo $item['id']; ?>][description]" class="form-input" rows="2" 
                                              placeholder="Rigorous academic programs that challenge and inspire students"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">💾 Save Curriculum Content</button>
            <button type="button" onclick="window.open('curriculum.php', '_blank')" class="btn" style="background: linear-gradient(135deg, #9333ea, #7c3aed); color: white;">
                👁️ Preview
            </button>
        </div>
    </form>
</div>

<script>
let programIndex = <?php echo !empty($curriculumPrograms) ? max(array_column($curriculumPrograms, 'id')) + 1 : 0; ?>;
let whyChooseItemIndex = <?php echo !empty($whyChooseItems) ? max(array_column($whyChooseItems, 'id')) + 1 : 0; ?>;

function addProgram() {
    const container = document.getElementById('programsContainer');
    const programHtml = `
        <div class="program-item" data-index="new_${programIndex}" data-program-id="null" style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">New Program</h4>
                <button type="button" onclick="removeProgram('new_${programIndex}')" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                    🗑️ Remove Program
                </button>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Program Title</label>
                <input type="text" name="programs[new_${programIndex}][program_title]" class="form-input" 
                       placeholder="e.g., Nursery Education Program">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Description</label>
                <textarea name="programs[new_${programIndex}][description]" class="form-input" rows="3" 
                          placeholder="Program description..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Subjects (one per line)</label>
                <textarea name="programs[new_${programIndex}][subjects]" class="form-input" rows="4" 
                          placeholder="Language Development&#10;Early Mathematics&#10;Creative Arts"></textarea>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', programHtml);
    programIndex++;
}

function removeProgram(id) {
    const programItem = document.querySelector(`.program-item[data-program-id="${id}"], .program-item[data-index="new_${id}"]`);
    if (programItem) {
        programItem.remove();
    }
}

function addWhyChooseItem() {
    const container = document.getElementById('whyChooseItemsContainer');
    const itemHtml = `
        <div class="why-choose-item" data-item-id="new_${whyChooseItemIndex}" data-why-choose-id="null" style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h5 style="font-size: 14px; font-weight: 600; color: #333; margin: 0;">New Item</h5>
                <button type="button" onclick="removeWhyChooseItem('new_${whyChooseItemIndex}')" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                    🗑️ Remove Item
                </button>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Icon Name (e.g., GraduationCap, Users, Award, BookOpen)</label>
                <input type="text" name="why_choose_items[new_${whyChooseItemIndex}][icon_name]" class="form-input" 
                       placeholder="GraduationCap">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Title</label>
                <input type="text" name="why_choose_items[new_${whyChooseItemIndex}][title]" class="form-input" 
                       placeholder="Academic Excellence">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="why_choose_items[new_${whyChooseItemIndex}][description]" class="form-input" rows="2" 
                          placeholder="Rigorous academic programs that challenge and inspire students"></textarea>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    whyChooseItemIndex++;
}

function removeWhyChooseItem(id) {
    const item = document.querySelector(`.why-choose-item[data-item-id="${id}"], .why-choose-item[data-item-id="new_${id}"]`);
    if (item) {
        item.remove();
    }
}

function handleCurriculumFormSubmit(event, form) {
    event.preventDefault();
    
    // Collect programs data
    const programs = [];
    const programItems = document.querySelectorAll('.program-item');
    programItems.forEach((item) => {
        const titleInput = item.querySelector('input[name*="[program_title]"]');
        const descriptionInput = item.querySelector('textarea[name*="[description]"]');
        const subjectsInput = item.querySelector('textarea[name*="[subjects]"]');
        
        const title = titleInput?.value?.trim() || '';
        const description = descriptionInput?.value?.trim() || '';
        const subjects = subjectsInput?.value?.trim() || '';
        
        // Get ID from data attribute or from input name
        let programId = item.getAttribute('data-program-id');
        if (!programId || programId === 'null') {
            // Try to extract from input name
            const nameAttr = titleInput?.getAttribute('name') || '';
            const match = nameAttr.match(/programs\[([^\]]+)\]/);
            if (match && match[1] && !match[1].startsWith('new_')) {
                programId = match[1];
            }
        }
        
        // Only add if there's at least some content
        if (title || description || subjects) {
            programs.push({
                id: programId && !programId.toString().startsWith('new_') && programId !== 'null' ? parseInt(programId) : null,
                program_title: title,
                description: description,
                subjects: subjects
            });
        }
    });
    
    // Collect why choose items
    const whyChooseItems = [];
    const whyChooseItemElements = document.querySelectorAll('.why-choose-item');
    whyChooseItemElements.forEach((item) => {
        const iconNameInput = item.querySelector('input[name*="[icon_name]"]');
        const titleInput = item.querySelector('input[name*="[title]"]');
        const descriptionInput = item.querySelector('textarea[name*="[description]"]');
        
        const iconName = iconNameInput?.value?.trim() || '';
        const title = titleInput?.value?.trim() || '';
        const description = descriptionInput?.value?.trim() || '';
        
        // Get ID from data attribute or from input name
        let itemId = item.getAttribute('data-item-id');
        if (!itemId || itemId === 'null') {
            // Try to extract from input name
            const nameAttr = titleInput?.getAttribute('name') || '';
            const match = nameAttr.match(/why_choose_items\[([^\]]+)\]/);
            if (match && match[1] && !match[1].startsWith('new_')) {
                itemId = match[1];
            }
        }
        
        // Only add if there's at least some content
        if (iconName || title || description) {
            whyChooseItems.push({
                id: itemId && !itemId.toString().startsWith('new_') && itemId !== 'null' ? parseInt(itemId) : null,
                icon_name: iconName,
                title: title,
                description: description
            });
        }
    });
    
    // Prepare data
    const formData = new FormData(form);
    const data = {
        page_title: formData.get('page_title') || '',
        page_subtitle: formData.get('page_subtitle') || '',
        intro_text: formData.get('intro_text') || '',
        hero_image: formData.get('hero_image') || '',
        why_choose_title: formData.get('why_choose_title') || '',
        programs: programs,
        why_choose_items: whyChooseItems
    };
    
    // Send AJAX request
    fetch(form.action, {
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
            // Check if response is HTML (error page)
            if (text.trim().startsWith('<!') || text.includes('<html') || text.includes('Fatal error')) {
                // Suppress fatal error messages - only log to console
                console.error('Server error: API returned HTML instead of JSON. Response:', text.substring(0, 500));
                // Don't show toast for fatal errors
            } else {
                showToast('Server returned invalid response. Please try again.', 'error');
            }
            return;
        }
        
        if (result.error) {
            // Suppress "Fatal error" messages
            if (!result.error.toLowerCase().includes('fatal error') && !result.error.toLowerCase().includes('server error: fatal')) {
                showToast(result.error, 'error');
            } else {
                console.error('Fatal error suppressed:', result.error);
            }
        } else if (result.success) {
            showToast(result.message || 'Curriculum content saved successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Unexpected response from server.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Suppress fatal error messages
        if (!error.message || (!error.message.toLowerCase().includes('fatal error') && !error.message.toLowerCase().includes('server error: fatal'))) {
            showToast('Failed to save content: ' + (error.message || 'Please try again.'), 'error');
        }
    });
    
    return false;
}
</script>
