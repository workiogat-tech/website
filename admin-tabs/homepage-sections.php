<?php
/**
 * Homepage Sections Management Tab
 * Manage all homepage sections: Academics, Vision/Mission, Why Choose, Custom Section, Three Columns
 */
$db = getDB();

// Get all sections data
$academicsContent = $db->fetchOne("SELECT * FROM academics_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$visionMissionContent = $db->fetchOne("SELECT * FROM vision_mission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$coreValuesItems = $db->fetchAll("SELECT * FROM core_values_items WHERE is_active = 1 ORDER BY order_index ASC");
$whyChooseContent = $db->fetchOne("SELECT * FROM why_choose_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$whyChoosePoints = $db->fetchAll("SELECT * FROM why_choose_points WHERE is_active = 1 ORDER BY order_index ASC");
try {
    $customSectionContent = $db->fetchOne("SELECT * FROM custom_section_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
} catch (Exception $e) {
    $customSectionContent = [];
}

try {
    $threeColumnSections = $db->fetchAll("SELECT * FROM three_column_sections WHERE is_active = 1 ORDER BY order_index ASC");
} catch (Exception $e) {
    $threeColumnSections = [];
}
?>
<div class="content-card">
    <h2>🏠 Homepage Sections Management</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage all content sections on the homepage.</p>
    
    <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.6;">
            <strong>📍 Looking to edit the School Life, Health, and Sports columns?</strong><br>
            Click on the <strong>"Three Columns"</strong> tab below to edit those sections that appear on your homepage with red headers.
        </p>
    </div>
    
    <!-- Tabs for different sections -->
    <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #e5e7eb; flex-wrap: wrap;">
        <button class="section-tab-btn active" onclick="showSection('academics')">Academics</button>
        <button class="section-tab-btn" onclick="showSection('vision-mission')">Vision/Mission</button>
        <button class="section-tab-btn" onclick="showSection('why-choose')">Why Choose</button>
        <button class="section-tab-btn" onclick="showSection('custom-section')">Custom Section</button>
        <button class="section-tab-btn" onclick="showSection('three-columns')">Three Columns</button>
    </div>
    
    <!-- Academics Section -->
    <div id="academics-section" class="section-content">
        <h3>Academics Section</h3>
        <form method="POST" action="api/content/homepage-sections.php" style="margin-top: 20px;" onsubmit="return handleAdminFormSubmit(event, this);">
            <input type="hidden" name="section_type" value="academics">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($academicsContent['title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="5"><?php echo htmlspecialchars($academicsContent['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Student Count</label>
                <input type="text" name="student_count" class="form-input" value="<?php echo htmlspecialchars($academicsContent['student_count'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Success Rate</label>
                <input type="text" name="success_rate" class="form-input" value="<?php echo htmlspecialchars($academicsContent['success_rate'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Motto</label>
                <input type="text" name="motto" class="form-input" value="<?php echo htmlspecialchars($academicsContent['motto'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="image" id="academics_image" class="form-input" value="<?php echo htmlspecialchars($academicsContent['image'] ?? ''); ?>" readonly>
                    <button type="button" onclick="openMediaLibrary('academics_image')" class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Curriculum Link</label>
                <input type="text" name="curriculum_link" class="form-input" value="<?php echo htmlspecialchars($academicsContent['curriculum_link'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Button Text</label>
                <input type="text" name="button_text" class="form-input" value="<?php echo htmlspecialchars($academicsContent['button_text'] ?? 'Learn More About Our Curriculum'); ?>" placeholder="Learn More About Our Curriculum">
            </div>
            <button type="submit" class="btn btn-primary">💾 Save Academics Content</button>
        </form>
    </div>
    
    <!-- Vision/Mission Section -->
    <div id="vision-mission-section" class="section-content" style="display: none;">
        <h3>Vision, Mission & Core Values</h3>
        <form method="POST" action="api/content/homepage-sections.php" style="margin-top: 20px;" onsubmit="return handleAdminFormSubmit(event, this);">
            <input type="hidden" name="section_type" value="vision-mission">
            <div class="form-group">
                <label class="form-label">Vision Title</label>
                <input type="text" name="vision_title" class="form-input" value="<?php echo htmlspecialchars($visionMissionContent['vision_title'] ?? 'OUR VISION'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Vision Text</label>
                <textarea name="vision_text" class="form-input" rows="3"><?php echo htmlspecialchars($visionMissionContent['vision_text'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Vision Icon (Emoji)</label>
                <input type="text" name="vision_icon_emoji" class="form-input" value="<?php echo htmlspecialchars($visionMissionContent['vision_icon_emoji'] ?? '💡'); ?>" maxlength="10">
            </div>
            <div class="form-group">
                <label class="form-label">Mission Title</label>
                <input type="text" name="mission_title" class="form-input" value="<?php echo htmlspecialchars($visionMissionContent['mission_title'] ?? 'OUR MISSION'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Mission Text</label>
                <textarea name="mission_text" class="form-input" rows="3"><?php echo htmlspecialchars($visionMissionContent['mission_text'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Mission Icon (Emoji)</label>
                <input type="text" name="mission_icon_emoji" class="form-input" value="<?php echo htmlspecialchars($visionMissionContent['mission_icon_emoji'] ?? '🎓'); ?>" maxlength="10">
            </div>
            <div class="form-group">
                <label class="form-label">Core Values Title</label>
                <input type="text" name="core_values_title" class="form-input" value="<?php echo htmlspecialchars($visionMissionContent['core_values_title'] ?? 'OUR CORE VALUES'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Core Values Icon (Emoji)</label>
                <input type="text" name="core_values_icon_emoji" class="form-input" value="<?php echo htmlspecialchars($visionMissionContent['core_values_icon_emoji'] ?? '👥'); ?>" maxlength="10">
            </div>
            <button type="submit" class="btn btn-primary">💾 Save Vision/Mission</button>
        </form>
        
        <div style="margin-top: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h4>Core Values</h4>
                <button class="btn btn-primary" onclick="showAddCoreValueModal()">➕ Add Value</button>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Value Name</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coreValuesItems)): ?>
                        <tr><td colspan="3" style="text-align: center; padding: 40px;">No values found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($coreValuesItems as $value): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($value['value_name']); ?></td>
                                <td><?php echo $value['order_index']; ?></td>
                                <td>
                                    <button class="btn" onclick="editCoreValue(<?php echo $value['id']; ?>)">Edit</button>
                                    <button class="btn btn-danger" onclick="deleteCoreValue(<?php echo $value['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Why Choose Section -->
    <div id="why-choose-section" class="section-content" style="display: none;">
        <h3>Why Choose SJS Section</h3>
        <form method="POST" action="api/content/homepage-sections.php" id="whyChooseForm" style="margin-top: 20px;" onsubmit="return handleWhyChooseFormSubmit(event, this);">
            <input type="hidden" name="section_type" value="why-choose">
            
            <div class="form-group">
                <label class="form-label">Section Title</label>
                <input type="text" name="title" class="form-input" 
                       value="<?php echo htmlspecialchars($whyChooseContent['title'] ?? 'Why Choose SJS'); ?>" 
                       placeholder="Why Choose SJS">
            </div>
            
            <div class="form-group">
                <label class="form-label">Section Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $whyChooseImageUrl = normalizeImageUrl($whyChooseContent['image'] ?? '');
                    if (!empty($whyChooseImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($whyChooseImageUrl); ?>" 
                             alt="Why Choose Image Preview"
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="image" id="why_choose_image" class="form-input" 
                               value="<?php echo htmlspecialchars($whyChooseContent['image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly
                               required>
                    </div>
                    <button type="button" onclick="openMediaLibrary('why_choose_image')" 
                            class="btn btn-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        🖼️ Select Image
                    </button>
                </div>
            </div>
            
            <!-- Points List -->
            <div style="margin-top: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 style="font-size: 18px; font-weight: 600; color: #333; margin: 0;">Points List</h4>
                    <button type="button" onclick="addWhyChoosePoint()" class="btn btn-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        ➕ Add Point
                    </button>
                </div>
                
                <div id="whyChoosePointsContainer">
                    <?php if (!empty($whyChoosePoints)): ?>
                        <?php foreach ($whyChoosePoints as $index => $point): ?>
                            <div class="why-choose-point-item" data-point-id="<?php echo $point['id']; ?>" style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 15px;">
                                <div style="flex: 1;">
                                    <input type="text" 
                                           name="points[<?php echo $point['id']; ?>]" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($point['point_text']); ?>" 
                                           placeholder="Enter point text...">
                                </div>
                                <button type="button" onclick="removeWhyChoosePoint(<?php echo $point['id']; ?>)" 
                                        class="btn btn-danger" style="padding: 8px 15px; font-size: 12px; flex-shrink: 0;">
                                    🗑️ Remove
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div id="emptyPointsMessage" style="<?php echo !empty($whyChoosePoints) ? 'display: none;' : ''; ?> text-align: center; padding: 40px; color: #999; background: #f9fafb; border-radius: 8px; border: 2px dashed #e5e7eb;">
                    <p style="margin: 0;">No points added yet. Click "+ Add Point" to get started.</p>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 20px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                💾 Save Why Choose Section
            </button>
        </form>
    </div>
    
    <!-- Custom Section -->
    <div id="custom-section" class="section-content" style="display: none;">
        <h3>Custom Section (Additional Section)</h3>
        <p style="color: #666; margin-bottom: 20px;">Add a new customizable section similar to Academics. You can add title, description, image, and optional button.</p>
        <form method="POST" action="api/content/homepage-sections.php" style="margin-top: 20px;" onsubmit="return handleAdminFormSubmit(event, this);">
            <input type="hidden" name="section_type" value="custom-section">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($customSectionContent['title'] ?? ''); ?>" placeholder="Enter section title">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="5" placeholder="Enter section description..."><?php echo htmlspecialchars($customSectionContent['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="image" id="custom_section_image" class="form-input" value="<?php echo htmlspecialchars($customSectionContent['image'] ?? ''); ?>" readonly>
                    <button type="button" onclick="openMediaLibrary('custom_section_image')" class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Button Text (Optional)</label>
                    <input type="text" name="button_text" class="form-input" value="<?php echo htmlspecialchars($customSectionContent['button_text'] ?? ''); ?>" placeholder="e.g., Learn More">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Link (Optional)</label>
                    <input type="text" name="button_link" class="form-input" value="<?php echo htmlspecialchars($customSectionContent['button_link'] ?? ''); ?>" placeholder="e.g., about.php">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">💾 Save Custom Section</button>
        </form>
    </div>
    
    <!-- Three Columns Section -->
    <div id="three-columns" class="section-content" style="display: none;">
        <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-left: 4px solid #3b82f6; padding: 20px; border-radius: 8px; margin-bottom: 24px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #1e40af; margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                <span>📝</span> How to Edit the Three Column Sections on Homepage
            </h3>
            <p style="color: #1e3a8a; margin: 0 0 12px 0; line-height: 1.6;">
                This section allows you to edit the <strong>School Life</strong>, <strong>Health</strong>, and <strong>Sports</strong> columns that appear on your homepage. 
                These are the three sections displayed side-by-side with red headers on the main page.
            </p>
            <div style="background: white; padding: 15px; border-radius: 6px; margin-top: 12px;">
                <p style="color: #374151; margin: 0 0 8px 0; font-weight: 500;">To edit each section:</p>
                <ol style="color: #4b5563; margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Scroll down to find the section you want to edit (School Life, Health, or Sports)</li>
                    <li>Update the <strong>Description</strong> text in the textarea field</li>
                    <li>Click <strong>"🖼️ Select"</strong> button to choose an image from your media library</li>
                    <li>Click <strong>"💾 Save Three Columns"</strong> at the bottom to save all changes</li>
                </ol>
            </div>
            <p style="color: #6b7280; margin: 12px 0 0 0; font-size: 13px; font-style: italic;">
                💡 <strong>Tip:</strong> The section titles (School Life, Health, Sports) are fixed and cannot be changed. Only the description and images can be edited.
            </p>
        </div>
        
        <h3>Three Column Sections (School Life, Health, Sports)</h3>
        <p style="color: #666; margin-bottom: 20px;">Manage the three-column layout with red headers. Each column has a title, image, and description.</p>
        
        <?php 
        // Ensure we have three sections
        $sectionTitles = ['School Life', 'Health', 'Sports'];
        $sections = [];
        
        foreach ($sectionTitles as $index => $title) {
            $section = null;
            foreach ($threeColumnSections as $col) {
                if (strtolower($col['title']) === strtolower($title)) {
                    $section = $col;
                    break;
                }
            }
            if (!$section) {
                $section = ['id' => null, 'title' => $title, 'description' => '', 'image' => '', 'order_index' => $index];
            }
            $sections[] = $section;
        }
        ?>
        
        <form method="POST" action="api/content/homepage-sections.php" style="margin-top: 20px;" onsubmit="return handleAdminFormSubmit(event, this);">
            <input type="hidden" name="section_type" value="three-columns">
            
            <?php foreach ($sections as $index => $section): ?>
            <div style="background: #f9fafb; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 2px solid #e5e7eb; border-left: 4px solid #dc2626;">
                <div style="background: #dc2626; color: white; padding: 12px 16px; border-radius: 6px; margin: -24px -24px 20px -24px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;"><?php 
                        $icons = ['School Life' => '🏫', 'Health' => '🏥', 'Sports' => '⚽'];
                        echo $icons[$section['title']] ?? '📋';
                    ?></span>
                    <h4 style="font-size: 18px; font-weight: 600; color: white; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo htmlspecialchars($section['title']); ?></h4>
                </div>
                
                <?php if ($section['id']): ?>
                    <input type="hidden" name="sections[<?php echo $index; ?>][id]" value="<?php echo $section['id']; ?>">
                <?php endif; ?>
                <input type="hidden" name="sections[<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($section['title']); ?>">
                <input type="hidden" name="sections[<?php echo $index; ?>][order_index]" value="<?php echo $index; ?>">
                
                <div class="form-group">
                    <label class="form-label">Description <span style="color: #6b7280; font-weight: normal;">(This text appears below the image on the homepage)</span></label>
                    <textarea name="sections[<?php echo $index; ?>][description]" class="form-input" rows="4" placeholder="Enter description that will appear in the <?php echo htmlspecialchars($section['title']); ?> column..."><?php echo htmlspecialchars($section['description'] ?? ''); ?></textarea>
                    <small style="color: #6b7280; display: block; margin-top: 5px;">💡 This description will be displayed in the <?php echo htmlspecialchars($section['title']); ?> column on your homepage.</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Image <span style="color: #6b7280; font-weight: normal;">(This image appears in the <?php echo htmlspecialchars($section['title']); ?> column)</span></label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="sections[<?php echo $index; ?>][image]" id="three_col_image_<?php echo $index; ?>" class="form-input" value="<?php echo htmlspecialchars($section['image'] ?? ''); ?>" placeholder="Paste an image address or click Select">
                        <button type="button" onclick="openMediaLibrary('three_col_image_<?php echo $index; ?>')" class="btn btn-primary">🖼️ Select Image</button>
                    </div>
                    <?php if (!empty($section['image'])): ?>
                        <div style="margin-top: 10px;">
                            <img src="<?php echo htmlspecialchars(normalizeImageUrl($section['image'])); ?>" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn btn-primary">💾 Save Three Columns</button>
        </form>
    </div>
</div>

<!-- Core Value Modal -->
<div id="coreValueModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="coreValueModalTitle">Add Core Value</h3>
            <span onclick="closeCoreValueModal()" style="cursor: pointer; font-size: 24px; color: #999;">&times;</span>
        </div>
        <form id="coreValueForm" onsubmit="saveCoreValue(event)">
            <input type="hidden" name="id" value="">
            <div class="form-group">
                <label class="form-label">Value Name</label>
                <input type="text" name="value_name" class="form-input" required placeholder="e.g., Faith">
            </div>
            <div class="form-group">
                <label class="form-label">Order Index</label>
                <input type="number" name="order_index" class="form-input" min="1" value="1">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">💾 Save</button>
                <button type="button" onclick="closeCoreValueModal()" class="btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: #fefefe;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    animation: slideDown 0.3s;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.section-tab-btn {
    padding: 10px 20px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    cursor: pointer;
    border-radius: 8px 8px 0 0;
    font-weight: 500;
    transition: all 0.3s;
}
.section-tab-btn:hover {
    background: #e5e7eb;
}
.section-tab-btn.active {
    background: #3b82f6;
    color: white;
}
.section-content {
    animation: fadeIn 0.3s;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
// Close modals when clicking outside
window.onclick = function(event) {
    const modals = ['coreValueModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.section-content').forEach(el => el.style.display = 'none');
    // Remove active class from all tabs
    document.querySelectorAll('.section-tab-btn').forEach(btn => btn.classList.remove('active'));
    // Show selected section - try both with and without '-section' suffix
    const sectionId = sectionName + '-section';
    const sectionElement = document.getElementById(sectionId) || document.getElementById(sectionName);
    if (sectionElement) {
        sectionElement.style.display = 'block';
    }
    // Add active class to clicked tab
    event.target.classList.add('active');
}

// Core Values Management
function showAddCoreValueModal(id = null) {
    const modal = document.getElementById('coreValueModal');
    const form = document.getElementById('coreValueForm');
    const modalTitle = document.getElementById('coreValueModalTitle');
    
    if (id) {
        modalTitle.textContent = 'Edit Core Value';
        form.querySelector('input[name="id"]').value = id;
        
        fetch(`api/content/homepage-sections.php?type=core-values&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    form.querySelector('input[name="value_name"]').value = data.data.value_name || '';
                    form.querySelector('input[name="order_index"]').value = data.data.order_index || '';
                }
            })
            .catch(err => {
                console.error('Error fetching core value:', err);
                showToast('Failed to load core value data', 'error');
            });
    } else {
        modalTitle.textContent = 'Add Core Value';
        form.reset();
        form.querySelector('input[name="id"]').value = '';
    }
    
    modal.style.display = 'block';
}

function closeCoreValueModal() {
    document.getElementById('coreValueModal').style.display = 'none';
}

function saveCoreValue(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {
        section_type: 'core-values',
        id: formData.get('id') || null,
        value_name: formData.get('value_name'),
        order_index: formData.get('order_index') || 0
    };
    
    fetch('api/content/homepage-sections.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(async res => {
        const result = await res.json();
        if (result.success) {
            showToast(result.message || 'Core value saved successfully!', 'success');
            closeCoreValueModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'Failed to save core value', 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showToast('Failed to save core value. Please try again.', 'error');
    });
}

function editCoreValue(id) {
    showAddCoreValueModal(id);
}

function deleteCoreValue(id) {
    if (!confirm('Are you sure you want to delete this core value?')) return;
    
    fetch(`api/content/homepage-sections.php?type=core-values&id=${id}`, {
        method: 'DELETE'
    })
    .then(async res => {
        const result = await res.json();
        if (result.success) {
            showToast(result.message || 'Core value deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error || 'Failed to delete core value', 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showToast('Failed to delete core value. Please try again.', 'error');
    });
}

// Why Choose Points Management
let whyChoosePointIndex = <?php echo !empty($whyChoosePoints) ? max(array_column($whyChoosePoints, 'id')) + 1 : 0; ?>;

function addWhyChoosePoint() {
    const container = document.getElementById('whyChoosePointsContainer');
    const emptyMessage = document.getElementById('emptyPointsMessage');
    
    const pointHtml = `
        <div class="why-choose-point-item" data-point-id="new_${whyChoosePointIndex}" style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 15px;">
            <div style="flex: 1;">
                <input type="text" 
                       name="points[new_${whyChoosePointIndex}]" 
                       class="form-input" 
                       placeholder="Enter point text...">
            </div>
            <button type="button" onclick="removeWhyChoosePoint('new_${whyChoosePointIndex}')" 
                    class="btn btn-danger" style="padding: 8px 15px; font-size: 12px; flex-shrink: 0;">
                🗑️ Remove
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', pointHtml);
    whyChoosePointIndex++;
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    // Scroll to the new point
    const newPoint = container.lastElementChild;
    if (newPoint) {
        newPoint.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        newPoint.querySelector('input').focus();
    }
}

function removeWhyChoosePoint(id) {
    const pointItem = document.querySelector(`.why-choose-point-item[data-point-id="${id}"]`);
    if (pointItem) {
        pointItem.remove();
        
        // Check if any points remain
        const container = document.getElementById('whyChoosePointsContainer');
        const emptyMessage = document.getElementById('emptyPointsMessage');
        const remainingPoints = container.querySelectorAll('.why-choose-point-item');
        
        if (remainingPoints.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        }
    }
}

// Handle Why Choose form submission
function handleWhyChooseFormSubmit(event, form) {
    event.preventDefault();
    
    // Collect points data
    const points = [];
    const pointItems = document.querySelectorAll('.why-choose-point-item');
    
    pointItems.forEach((item) => {
        const input = item.querySelector('input[name*="points"]');
        const pointText = input?.value?.trim();
        const pointId = item.getAttribute('data-point-id');
        
        if (pointText) {
            if (pointId && pointId.toString().startsWith('new_')) {
                // New point
                points.push({ point_text: pointText, id: null });
            } else if (pointId && !isNaN(pointId)) {
                // Existing point
                points.push({ point_text: pointText, id: parseInt(pointId) });
            } else {
                // Fallback for new points
                points.push({ point_text: pointText, id: null });
            }
        }
    });
    
    // Get image value directly from input field
    const imageInput = document.getElementById('why_choose_image');
    const imageValue = imageInput ? imageInput.value.trim() : '';
    
    if (!imageValue) {
        showToast('Please select an image first', 'error');
        return false;
    }
    
    // Prepare data
    const formData = new FormData(form);
    const data = {
        section_type: 'why-choose',
        title: formData.get('title') || '',
        image: imageValue,
        points: points
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
            throw new Error('Server returned invalid response');
        }
        
        if (result.error) {
            showToast(result.error, 'error');
        } else {
            showToast(result.message || 'Why Choose section saved successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to save section. Please try again.', 'error');
    });
    
    return false;
}
</script>
