<?php
/**
 * People Behind School Tab
 * Complete management interface matching the design
 */
// Ensure table exists
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'people_behind_school_content'");
    if ($tableExists === false || $tableExists === null) {
        $db->query("CREATE TABLE IF NOT EXISTS people_behind_school_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title TEXT,
            subtitle TEXT,
            hero_image VARCHAR(500),
            content TEXT,
            video_url VARCHAR(500),
            video_embed_code TEXT,
            people TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} catch (Exception $e) {
    error_log("People Behind School table creation error: " . $e->getMessage());
}

$peopleContent = [];
try {
    $result = $db->fetchOne("SELECT * FROM people_behind_school_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $peopleContent = ($result !== false && $result !== null) ? $result : [];
} catch (Exception $e) {
    error_log("People Behind School fetch error: " . $e->getMessage());
}

// Parse people data
$peopleList = [];
if (!empty($peopleContent['people'])) {
    $peopleList = is_string($peopleContent['people']) ? json_decode($peopleContent['people'], true) : $peopleContent['people'];
    if (!is_array($peopleList)) {
        $peopleList = [];
    }
}
?>
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0 0 8px 0;">👥 People Behind This School Page Management</h2>
            <p style="color: #6b7280; margin: 0;">Manage the content and team members for the People Behind School page.</p>
        </div>
        <button onclick="window.open('people-behind-school.php', '_blank')" style="padding: 8px 16px; background: #4b5563; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <span>🔗</span> Preview Page
        </button>
    </div>
    
    <form method="POST" action="api/content/people-behind-school.php" id="peopleForm" onsubmit="return handleAdminFormSubmit(event, this);" enctype="multipart/form-data">
        
        <!-- Basic Content Section -->
        <div style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Basic Content</h3>
            
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="title" class="form-input" 
                       value="<?php echo htmlspecialchars($peopleContent['title'] ?? 'People Behind This School'); ?>"
                       placeholder="People Behind This School">
            </div>
            
            <div class="form-group">
                <label class="form-label">Page Subtitle</label>
                <input type="text" name="subtitle" class="form-input" 
                       value="<?php echo htmlspecialchars($peopleContent['subtitle'] ?? 'Meet the Visionaries and Leaders'); ?>"
                       placeholder="Meet the Visionaries and Leaders">
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Background Image</label>
                <div style="display: flex; align-items: flex-start; gap: 15px;">
                    <?php 
                    $heroImageUrl = normalizeImageUrl($peopleContent['hero_image'] ?? '');
                    if (!empty($heroImageUrl)): ?>
                        <div style="flex-shrink: 0;">
                            <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                                 alt="Hero image preview"
                                 style="width: 160px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 160px; height: 96px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center;">
                                <div style="text-align: center;">
                                    <span style="font-size: 24px; color: #999;">🖼️</span>
                                    <p style="font-size: 11px; color: #999; margin-top: 5px;">No image</p>
                                </div>
                            </div>
                            <p style="text-align: center; font-size: 11px; color: #666; margin-top: 5px;">Preview</p>
                        </div>
                    <?php else: ?>
                        <div style="flex-shrink: 0;">
                            <div style="width: 160px; height: 96px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;">
                                <div style="text-align: center;">
                                    <span style="font-size: 24px; color: #999;">🖼️</span>
                                    <p style="font-size: 11px; color: #999; margin-top: 5px;">No image</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="hero_image" id="people_hero_image" class="form-input" 
                               value="<?php echo htmlspecialchars($peopleContent['hero_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                        <button type="button" onclick="openMediaLibrary('people_hero_image')" 
                                class="btn btn-primary" style="margin-top: 10px;">🖼️ Change Photo</button>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Main Content Write-up</label>
                <textarea name="content" class="form-input" rows="6" 
                          placeholder="Enter the main content that will be displayed on the page..."><?php echo htmlspecialchars($peopleContent['content'] ?? 'SJS is founded and guided by a team of dedicated educators, administrators, and community leaders who believe in quality education. Their combined experience, commitment, and shared vision focus on nurturing disciplined, knowledgeable, and responsible learners. Together, they work tirelessly to ensure SJS provides a supportive and inspiring learning environment for every child.'); ?></textarea>
                <small style="color: #6b7280; font-style: italic; display: block; margin-top: 5px;">This is the main write-up that will be displayed on the page.</small>
            </div>
        </div>
        
        <!-- Video Content Section -->
        <div style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Video Content</h3>
            
            <div class="form-group">
                <label class="form-label">Video URL</label>
                <input type="text" name="video_url" class="form-input" 
                       value="<?php echo htmlspecialchars($peopleContent['video_url'] ?? ''); ?>"
                       placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://vimeo.com/VIDEO_ID">
                <small style="color: #6b7280; display: block; margin-top: 5px;">
                    <strong>⚠️ Important:</strong> Use a specific <strong>video URL</strong>, not a channel URL.<br>
                    <strong>YouTube:</strong> <code>https://www.youtube.com/watch?v=VIDEO_ID</code> or <code>https://youtu.be/VIDEO_ID</code><br>
                    <strong>Vimeo:</strong> <code>https://vimeo.com/VIDEO_ID</code><br>
                    <strong>Note:</strong> Channel URLs (like <code>@channelname</code>) cannot be embedded. Use a specific video URL instead.
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Video Embed Code (Alternative)</label>
                <textarea name="video_embed_code" class="form-input" rows="4" 
                          placeholder='<iframe src="https://www.youtube.com/embed/VIDEO_ID" frameborder="0" allowfullscreen></iframe>'><?php echo htmlspecialchars($peopleContent['video_embed_code'] ?? ''); ?></textarea>
                <small style="color: #6b7280; display: block; margin-top: 5px;">
                    <strong>Alternative:</strong> Paste full HTML embed code (iframe tag) if you prefer custom embed instead of URL.<br>
                    <strong>Example:</strong> <code>&lt;iframe src="https://www.youtube.com/embed/VIDEO_ID" frameborder="0" allowfullscreen&gt;&lt;/iframe&gt;</code>
                </small>
            </div>
        </div>
        
        <!-- Team Members & Leadership Section -->
        <div style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 5px 0;">Team Members & Leadership</h3>
                    <p style="color: #6b7280; margin: 0; font-size: 14px;">Add people with their photos, names, titles, and bios</p>
                </div>
                <button type="button" onclick="showAddPersonModal()" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    <span>👤</span> + Add Person
                </button>
            </div>
            
            <div id="peopleList">
                <?php if (empty($peopleList)): ?>
                    <div style="text-align: center; padding: 40px; background: #f9fafb; border-radius: 12px; color: #6b7280;">
                        <p style="font-size: 16px; margin-bottom: 10px;">No team members added yet</p>
                        <p>Click "Add Person" to add your first team member.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($peopleList as $index => $person): ?>
                        <div class="person-card" data-index="<?php echo $index; ?>" style="background: #f9fafb; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 2px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h4 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; display: flex; align-items: center; gap: 10px;">
                                    <span>👥</span> Person <?php echo $index + 1; ?> - <?php echo htmlspecialchars($person['full_name'] ?? 'Unnamed'); ?>
                                </h4>
                                <button type="button" onclick="deletePerson(<?php echo $index; ?>)" class="btn" style="background: #ef4444; color: white; padding: 8px 16px;">
                                    🗑️ Delete
                                </button>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 20px;">
                                <!-- Left: Profile Photo -->
                                <div>
                                    <label class="form-label">Profile Photo</label>
                                    <div style="margin-bottom: 15px;">
                                        <?php 
                                        $personImageUrl = normalizeImageUrl($person['photo'] ?? '');
                                        if (!empty($personImageUrl)): ?>
                                            <img src="<?php echo htmlspecialchars($personImageUrl); ?>" 
                                                 alt="Profile photo"
                                                 style="width: 100%; max-width: 300px; height: 300px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981; margin-bottom: 10px;"
                                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div style="display: none; width: 100%; max-width: 300px; height: 300px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center;">
                                                <div style="text-align: center;">
                                                    <span style="font-size: 48px; color: #999;">🖼️</span>
                                                    <p style="font-size: 14px; color: #999; margin-top: 10px;">No image</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div style="width: 100%; max-width: 300px; height: 300px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                                <div style="text-align: center;">
                                                    <span style="font-size: 48px; color: #999;">🖼️</span>
                                                    <p style="font-size: 14px; color: #999; margin-top: 10px;">No image</p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <input type="text" name="people[<?php echo $index; ?>][photo]" 
                                               id="person_photo_<?php echo $index; ?>" 
                                               class="form-input" 
                                               value="<?php echo htmlspecialchars($person['photo'] ?? ''); ?>" 
                                               placeholder="Select image from media library" 
                                               readonly
                                               style="margin-bottom: 10px;">
                                        <button type="button" onclick="openMediaLibrary('person_photo_<?php echo $index; ?>')" 
                                                class="btn btn-primary" style="width: 100%;">🖼️ Change Photo</button>
                                        <small style="color: #6b7280; display: block; margin-top: 5px;">Recommended: Square image, minimum 400x400px</small>
                                    </div>
                                </div>
                                
                                <!-- Right: Personal Details -->
                                <div>
                                    <div class="form-group">
                                        <label class="form-label">Full Name <span style="color: #ef4444;">*</span></label>
                                        <input type="text" name="people[<?php echo $index; ?>][full_name]" class="form-input" 
                                               value="<?php echo htmlspecialchars($person['full_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Title/Role <span style="color: #ef4444;">*</span></label>
                                        <input type="text" name="people[<?php echo $index; ?>][title]" class="form-input" 
                                               value="<?php echo htmlspecialchars($person['title'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Additional Role <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                                        <input type="text" name="people[<?php echo $index; ?>][additional_role]" class="form-input" 
                                               value="<?php echo htmlspecialchars($person['additional_role'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Biography <span style="color: #ef4444;">*</span></label>
                                        <textarea name="people[<?php echo $index; ?>][biography]" class="form-input" rows="4" required><?php echo htmlspecialchars($person['biography'] ?? ''); ?></textarea>
                                        <small style="color: #6b7280; display: block; margin-top: 5px;">2-3 sentences recommended</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Email <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                                        <input type="email" name="people[<?php echo $index; ?>][email]" class="form-input" 
                                               value="<?php echo htmlspecialchars($person['email'] ?? ''); ?>"
                                               placeholder="email@example.com">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Phone <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                                        <input type="text" name="people[<?php echo $index; ?>][phone]" class="form-input" 
                                               value="<?php echo htmlspecialchars($person['phone'] ?? ''); ?>"
                                               placeholder="+256 123 456 789">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Hidden field to store people data as JSON -->
        <input type="hidden" name="people_json" id="people_json" value="">
        
        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px 24px; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <span>💾</span> Save People Behind School Content
            </button>
            <button type="button" onclick="window.open('people-behind-school.php', '_blank')" class="btn" style="background: #6b7280; color: white; padding: 12px 24px; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <span>👁️</span> Preview Page
            </button>
        </div>
    </form>
</div>

<!-- Add Person Modal -->
<div id="addPersonModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="padding: 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 24px; font-weight: bold; color: #333;">Add New Person</h3>
            <button onclick="closeAddPersonModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">✕</button>
        </div>
        
        <!-- Error message display in modal -->
        <div id="addPersonError" style="display: none; margin: 15px 20px; padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b; font-size: 14px;"></div>
        
        <form id="addPersonForm" onsubmit="return addPerson(event);" method="POST" action="#" style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 20px;">
                <!-- Left: Profile Photo -->
                <div>
                    <label class="form-label">Profile Photo</label>
                    <div id="newPersonPhotoPreview" style="width: 100%; max-width: 300px; height: 300px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                        <div style="text-align: center;">
                            <span style="font-size: 48px; color: #999;">🖼️</span>
                            <p style="font-size: 14px; color: #999; margin-top: 10px;">No image</p>
                        </div>
                    </div>
                    <input type="text" id="newPersonPhoto" class="form-input" placeholder="Select image from media library" readonly style="margin-bottom: 10px;">
                    <button type="button" onclick="openMediaLibrary('newPersonPhoto')" class="btn btn-primary" style="width: 100%;">🖼️ Change Photo</button>
                    <small style="color: #6b7280; display: block; margin-top: 5px;">Recommended: Square image, minimum 400x400px</small>
                </div>
                
                <!-- Right: Personal Details -->
                <div>
                    <div class="form-group">
                        <label class="form-label">Full Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="newPersonFullName" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Title/Role <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="newPersonTitle" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Additional Role <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                        <input type="text" id="newPersonAdditionalRole" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Biography <span style="color: #ef4444;">*</span></label>
                        <textarea id="newPersonBiography" class="form-input" rows="4" required></textarea>
                        <small style="color: #6b7280; display: block; margin-top: 5px;">2-3 sentences recommended</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                        <input type="email" id="newPersonEmail" class="form-input" placeholder="email@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                        <input type="text" id="newPersonPhone" class="form-input" placeholder="+256 123 456 789">
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="button" onclick="closeAddPersonModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Add Person</button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize peopleList - ensure it's always an array and GLOBALLY accessible
let peopleList = <?php echo json_encode($peopleList); ?>;
if (!Array.isArray(peopleList)) {
    peopleList = [];
}
// Make it globally accessible IMMEDIATELY
window.peopleList = peopleList;

// Update image preview when image is selected
function updateImagePreview(fieldId, imageUrl) {
    if (fieldId === 'newPersonPhoto') {
        const preview = document.getElementById('newPersonPhotoPreview');
        if (imageUrl && preview) {
            preview.innerHTML = `<img src="${imageUrl}" alt="Profile photo" style="width: 100%; max-width: 300px; height: 300px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981;">`;
        }
    } else if (fieldId.startsWith('person_photo_')) {
        const index = parseInt(fieldId.replace('person_photo_', ''));
        const card = document.querySelector(`.person-card[data-index="${index}"]`);
        if (card && imageUrl) {
            const img = card.querySelector('img');
            if (img) {
                img.src = imageUrl;
                img.style.display = 'block';
            }
        }
    } else if (fieldId === 'people_hero_image') {
        const heroImg = document.querySelector('#people_hero_image').previousElementSibling?.querySelector('img');
        if (heroImg && imageUrl) {
            heroImg.src = imageUrl;
            heroImg.style.display = 'block';
        }
    }
}

// Override the global updateImagePreview function
if (typeof window.updateImagePreview === 'function') {
    const originalUpdate = window.updateImagePreview;
    window.updateImagePreview = function(fieldId, imageUrl) {
        originalUpdate(fieldId, imageUrl);
        updateImagePreview(fieldId, imageUrl);
    };
} else {
    window.updateImagePreview = updateImagePreview;
}

function showAddPersonModal() {
    try {
        const modal = document.getElementById('addPersonModal');
        const form = document.getElementById('addPersonForm');
        
        if (!modal) {
            console.error('Add person modal not found');
            if (typeof showToast === 'function') {
                showToast('Error: Modal not found. Please refresh the page.', 'error');
            } else {
                alert('Error: Modal not found. Please refresh the page.');
            }
            return;
        }
        
        if (!form) {
            console.error('Add person form not found');
            if (typeof showToast === 'function') {
                showToast('Error: Form not found. Please refresh the page.', 'error');
            } else {
                alert('Error: Form not found. Please refresh the page.');
            }
            return;
        }
        
        // Show modal
        modal.style.display = 'flex';
        
        // Reset form
        form.reset();
        
        // Clear photo field and preview
        const photoInput = document.getElementById('newPersonPhoto');
        const photoPreview = document.getElementById('newPersonPhotoPreview');
        
        if (photoInput) {
            photoInput.value = '';
        }
        
        if (photoPreview) {
            photoPreview.innerHTML = `
                <div style="text-align: center;">
                    <span style="font-size: 48px; color: #999;">🖼️</span>
                    <p style="font-size: 14px; color: #999; margin-top: 10px;">No image</p>
                </div>
            `;
        }
        
        // Clear any error messages
        const errorDiv = document.getElementById('addPersonError');
        if (errorDiv) {
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
        }
        
        // Focus on first field
        const fullNameInput = document.getElementById('newPersonFullName');
        if (fullNameInput) {
            setTimeout(() => fullNameInput.focus(), 100);
        }
    } catch (error) {
        console.error('Error showing add person modal:', error);
        if (typeof showToast === 'function') {
            showToast('Error opening modal: ' + error.message, 'error');
        } else {
            alert('Error opening modal: ' + error.message);
        }
    }
}

function closeAddPersonModal() {
    try {
        const modal = document.getElementById('addPersonModal');
        if (modal) {
            modal.style.display = 'none';
        }
    } catch (error) {
        console.error('Error closing modal:', error);
    }
}

function addPerson(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }
    
    try {
        // Ensure peopleList is an array
        if (!Array.isArray(peopleList)) {
            peopleList = [];
        }
        
        // Get all form values
        const fullName = (document.getElementById('newPersonFullName').value || '').trim();
        const title = (document.getElementById('newPersonTitle').value || '').trim();
        const biography = (document.getElementById('newPersonBiography').value || '').trim();
        const photo = (document.getElementById('newPersonPhoto').value || '').trim();
        const additionalRole = (document.getElementById('newPersonAdditionalRole').value || '').trim();
        const email = (document.getElementById('newPersonEmail').value || '').trim();
        const phone = (document.getElementById('newPersonPhone').value || '').trim();
        
        // Clear any previous error
        const errorDiv = document.getElementById('addPersonError');
        if (errorDiv) {
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
        }
        
        // Validate required fields with better messages
        if (!fullName || !title || !biography) {
            const missing = [];
            if (!fullName) missing.push('Full Name');
            if (!title) missing.push('Title/Role');
            if (!biography) missing.push('Biography');
            
            const msg = 'Please fill in all required fields: ' + missing.join(', ');
            
            // Show error in modal
            if (errorDiv) {
                errorDiv.textContent = msg;
                errorDiv.style.display = 'block';
            }
            
            if (typeof showToast === 'function') {
                showToast(msg, 'error');
            } else {
                alert(msg);
            }
            
            // Highlight missing fields
            if (!fullName) {
                const field = document.getElementById('newPersonFullName');
                if (field) {
                    field.style.border = '2px solid #ef4444';
                    field.focus();
                    setTimeout(() => field.style.border = '', 3000);
                }
            }
            if (!title) {
                const field = document.getElementById('newPersonTitle');
                if (field) {
                    field.style.border = '2px solid #ef4444';
                    if (!fullName) field.focus();
                    setTimeout(() => field.style.border = '', 3000);
                }
            }
            if (!biography) {
                const field = document.getElementById('newPersonBiography');
                if (field) {
                    field.style.border = '2px solid #ef4444';
                    if (!fullName && !title) field.focus();
                    setTimeout(() => field.style.border = '', 3000);
                }
            }
            
            return false;
        }
        
        // Validate biography length
        if (biography.length < 10) {
            const msg = 'Biography must be at least 10 characters long. Please provide 2-3 sentences.';
            
            // Show error in modal
            if (errorDiv) {
                errorDiv.textContent = msg;
                errorDiv.style.display = 'block';
            }
            
            if (typeof showToast === 'function') {
                showToast(msg, 'error');
            } else {
                alert(msg);
            }
            const field = document.getElementById('newPersonBiography');
            if (field) {
                field.style.border = '2px solid #ef4444';
                field.focus();
                setTimeout(() => field.style.border = '', 3000);
            }
            return false;
        }
        
        // Create person object
        const newPerson = {
            photo: photo,
            full_name: fullName,
            title: title,
            additional_role: additionalRole,
            biography: biography,
            email: email,
            phone: phone
        };
        
        // Add to list
        peopleList.push(newPerson);
        // Update global reference
        window.peopleList = peopleList;
        
        // Render immediately
        renderPeopleList();
        
        // Update people_json field
        updatePeopleJsonField();
        
        // Close modal and reset form
        closeAddPersonModal();
        document.getElementById('addPersonForm').reset();
        document.getElementById('newPersonPhoto').value = '';
        document.getElementById('newPersonPhotoPreview').innerHTML = `
            <div style="text-align: center;">
                <span style="font-size: 48px; color: #999;">🖼️</span>
                <p style="font-size: 14px; color: #999; margin-top: 10px;">No image</p>
            </div>
        `;
        
        // Show success
        if (typeof showToast === 'function') {
            showToast('Person added! Click "Save" to save all changes.', 'success');
        }
        
        return false;
    } catch (error) {
        console.error('Error adding person:', error);
        if (typeof showToast === 'function') {
            showToast('Error: ' + error.message, 'error');
        } else {
            alert('Error: ' + error.message);
        }
        return false;
    }
}

function deletePerson(index) {
    try {
        // Ensure peopleList is an array
        if (!Array.isArray(peopleList)) {
            peopleList = [];
            return;
        }
        
        // Validate index
        if (index < 0 || index >= peopleList.length) {
            console.error('Invalid index for deletePerson:', index);
            return;
        }
        
        if (!confirm('Are you sure you want to delete this person?')) {
            return;
        }
        
        // Remove person from list
        peopleList.splice(index, 1);
        // Update global reference
        window.peopleList = peopleList;
        
        // Re-render the list
        renderPeopleList();
        
        // Update people_json field
        updatePeopleJsonField();
        
        // Show success message
        if (typeof showToast === 'function') {
            showToast('Person deleted successfully! Don\'t forget to save the form.', 'success');
        }
    } catch (error) {
        console.error('Error deleting person:', error);
        if (typeof showToast === 'function') {
            showToast('Error deleting person: ' + error.message, 'error');
        } else {
            alert('Error deleting person: ' + error.message);
        }
    }
}

// Function to update people_json field whenever peopleList changes
function updatePeopleJsonField() {
    // Use global peopleList if available
    const currentPeopleList = window.peopleList || (typeof peopleList !== 'undefined' ? peopleList : []);
    
    const peopleJsonField = document.getElementById('people_json');
    if (peopleJsonField && Array.isArray(currentPeopleList)) {
        // Collect any form field data that might have been edited
        const form = document.getElementById('peopleForm');
        if (form) {
            const formData = new FormData(form);
            const formPeopleData = {};
            
            formData.forEach((value, key) => {
                const match = key.match(/^people\[(\d+)\]\[(.+)\]$/);
                if (match) {
                    const index = parseInt(match[1]);
                    const field = match[2];
                    if (!formPeopleData[index]) {
                        formPeopleData[index] = {};
                    }
                    formPeopleData[index][field] = value;
                }
            });
            
            // Merge peopleList with form field data
            const finalPeopleArray = [];
            currentPeopleList.forEach((person, index) => {
                const finalPerson = { ...person };
                
                // Override with form field data if it exists
                if (formPeopleData[index]) {
                    Object.keys(formPeopleData[index]).forEach(key => {
                        if (formPeopleData[index][key]) {
                            finalPerson[key] = formPeopleData[index][key];
                        }
                    });
                }
                
                // Validate required fields
                const fullName = (finalPerson.full_name || '').trim();
                const title = (finalPerson.title || '').trim();
                const biography = (finalPerson.biography || '').trim();
                
                if (fullName && title && biography) {
                    finalPeopleArray.push(finalPerson);
                }
            });
            
            peopleJsonField.value = JSON.stringify(finalPeopleArray);
        } else {
            // If no form, just use peopleList directly
            peopleJsonField.value = JSON.stringify(currentPeopleList);
        }
    } else {
        console.warn('updatePeopleJsonField: peopleJsonField not found or peopleList is not an array');
    }
}

function renderPeopleList() {
    const container = document.getElementById('peopleList');
    if (!container) {
        console.error('peopleList container not found');
        return;
    }
    
    // Ensure peopleList is an array
    if (!Array.isArray(peopleList)) {
        peopleList = [];
    }
    
    if (peopleList.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; background: #f9fafb; border-radius: 12px; color: #6b7280;">
                <p style="font-size: 16px; margin-bottom: 10px;">No team members added yet</p>
                <p>Click "Add Person" to add your first team member.</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    peopleList.forEach((person, index) => {
        const photoUrl = person.photo || '';
        html += `
            <div class="person-card" data-index="${index}" style="background: #f9fafb; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 2px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; display: flex; align-items: center; gap: 10px;">
                        <span>👥</span> Person ${index + 1} - ${escapeHtml(person.full_name || 'Unnamed')}
                    </h4>
                    <button type="button" onclick="deletePerson(${index})" class="btn" style="background: #ef4444; color: white; padding: 8px 16px;">
                        🗑️ Delete
                    </button>
                </div>
                
                <div style="display: grid; grid-template-columns: 300px 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Profile Photo</label>
                        <div style="margin-bottom: 15px;">
                            ${photoUrl ? `
                                <img src="${escapeHtml(photoUrl)}" alt="Profile photo" style="width: 100%; max-width: 300px; height: 300px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981; margin-bottom: 10px;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 100%; max-width: 300px; height: 300px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center;">
                                    <div style="text-align: center;">
                                        <span style="font-size: 48px; color: #999;">🖼️</span>
                                        <p style="font-size: 14px; color: #999; margin-top: 10px;">No image</p>
                                    </div>
                                </div>
                            ` : `
                                <div style="width: 100%; max-width: 300px; height: 300px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                    <div style="text-align: center;">
                                        <span style="font-size: 48px; color: #999;">🖼️</span>
                                        <p style="font-size: 14px; color: #999; margin-top: 10px;">No image</p>
                                    </div>
                                </div>
                            `}
                            <input type="text" name="people[${index}][photo]" id="person_photo_${index}" class="form-input" value="${escapeHtml(photoUrl)}" placeholder="Select image from media library" readonly style="margin-bottom: 10px;">
                            <button type="button" onclick="openMediaLibrary('person_photo_${index}')" class="btn btn-primary" style="width: 100%;">🖼️ Change Photo</button>
                            <small style="color: #6b7280; display: block; margin-top: 5px;">Recommended: Square image, minimum 400x400px</small>
                        </div>
                    </div>
                    
                    <div>
                        <div class="form-group">
                            <label class="form-label">Full Name <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="people[${index}][full_name]" class="form-input" value="${escapeHtml(person.full_name || '')}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Title/Role <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="people[${index}][title]" class="form-input" value="${escapeHtml(person.title || '')}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Additional Role <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                            <input type="text" name="people[${index}][additional_role]" class="form-input" value="${escapeHtml(person.additional_role || '')}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Biography <span style="color: #ef4444;">*</span></label>
                            <textarea name="people[${index}][biography]" class="form-input" rows="4" required>${escapeHtml(person.biography || '')}</textarea>
                            <small style="color: #6b7280; display: block; margin-top: 5px;">2-3 sentences recommended</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                            <input type="email" name="people[${index}][email]" class="form-input" value="${escapeHtml(person.email || '')}" placeholder="email@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone <span style="color: #6b7280; font-weight: normal;">(Optional)</span></label>
                            <input type="text" name="people[${index}][phone]" class="form-input" value="${escapeHtml(person.phone || '')}" placeholder="+256 123 456 789">
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Update people_json field after rendering
    updatePeopleJsonField();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Before form submission, prepare people_json from peopleList
const peopleForm = document.getElementById('peopleForm');
if (peopleForm) {
    peopleForm.addEventListener('submit', function(e) {
        // Build people array from peopleList (which includes both form fields and modal additions)
        const finalPeopleArray = [];
        
        // First collect from rendered form fields (if any exist)
        const formData = new FormData(this);
        const formPeopleData = {};
        
        formData.forEach((value, key) => {
            const match = key.match(/^people\[(\d+)\]\[(.+)\]$/);
            if (match) {
                const index = parseInt(match[1]);
                const field = match[2];
                if (!formPeopleData[index]) {
                    formPeopleData[index] = {};
                }
                formPeopleData[index][field] = value;
            }
        });
        
        // Use peopleList as base, merge with form field data
        const currentPeopleList = window.peopleList || (typeof peopleList !== 'undefined' ? peopleList : []);
        if (Array.isArray(currentPeopleList)) {
            currentPeopleList.forEach((person, index) => {
                // Start with person from peopleList
                const finalPerson = { ...person };
                
                // Override with form field data if it exists
                if (formPeopleData[index]) {
                    Object.keys(formPeopleData[index]).forEach(key => {
                        if (formPeopleData[index][key]) {
                            finalPerson[key] = formPeopleData[index][key];
                        }
                    });
                }
                
                // Validate required fields
                const fullName = (finalPerson.full_name || '').trim();
                const title = (finalPerson.title || '').trim();
                const biography = (finalPerson.biography || '').trim();
                
                if (fullName && title && biography) {
                    finalPeopleArray.push(finalPerson);
                }
            });
        }
        
        // Set people_json field with validation
        const peopleJsonField = document.getElementById('people_json');
        if (peopleJsonField) {
            // Validate before setting
            if (finalPeopleArray.length === 0) {
                console.warn('Warning: No valid people to save. All people entries are missing required fields.');
                if (typeof showToast === 'function') {
                    showToast('Warning: No valid people to save. Please ensure all people have Full Name, Title/Role, and Biography filled in.', 'error');
                }
            }
            
            peopleJsonField.value = JSON.stringify(finalPeopleArray);
        } else {
            console.error('people_json field not found!');
        }
    }, true);
    
    // Also update people_json when form fields change (for auto-save)
    const formInputs = peopleForm.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('change', function() {
            updatePeopleJsonField();
        });
        input.addEventListener('input', function() {
            // Debounce for performance
            clearTimeout(input._updateTimeout);
            input._updateTimeout = setTimeout(() => {
                updatePeopleJsonField();
            }, 500);
        });
    });
}

// Add shake animation for validation errors
if (!document.getElementById('validationStyles')) {
    const style = document.createElement('style');
    style.id = 'validationStyles';
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
}

// Close modal when clicking outside
document.getElementById('addPersonModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddPersonModal();
    }
});

// Initialize on page load - render people list if it exists
document.addEventListener('DOMContentLoaded', function() {
    // Ensure peopleList is an array
    if (!Array.isArray(peopleList)) {
        peopleList = [];
    }
    
    // If we have people from PHP, make sure they're rendered
    if (peopleList.length > 0) {
        renderPeopleList();
    }
    
    // Make sure the form submission handler is set up
    const peopleForm = document.getElementById('peopleForm');
    if (peopleForm) {
        // Ensure form action is correct
        const actionAttr = peopleForm.getAttribute('action');
        if (!actionAttr || actionAttr.includes('admin.php')) {
            peopleForm.setAttribute('action', 'api/content/people-behind-school.php');
        }
        
        if (!peopleForm.hasAttribute('data-handler-attached')) {
            peopleForm.setAttribute('data-handler-attached', 'true');
            // The submit handler is already attached above, but we can verify it exists
        }
    }
});

// Make functions globally available
window.addPerson = addPerson;
window.deletePerson = deletePerson;
window.renderPeopleList = renderPeopleList;
window.showAddPersonModal = showAddPersonModal;
window.closeAddPersonModal = closeAddPersonModal;
window.updatePeopleJsonField = updatePeopleJsonField;
window.peopleList = peopleList; // Make peopleList globally accessible

// CRITICAL: Ensure addPersonForm is NEVER auto-saved or submitted to server
document.addEventListener('DOMContentLoaded', function() {
    const addPersonForm = document.getElementById('addPersonForm');
    if (addPersonForm) {
        // Remove any action that might cause server submission
        addPersonForm.setAttribute('action', '#');
        addPersonForm.setAttribute('method', 'POST');
        
        // Ensure onsubmit handler is set
        if (!addPersonForm.onsubmit) {
            addPersonForm.onsubmit = function(e) {
                return addPerson(e);
            };
        }
        
        // Remove any listeners that might have been auto-attached
        const newForm = addPersonForm.cloneNode(true);
        addPersonForm.parentNode.replaceChild(newForm, addPersonForm);
        newForm.setAttribute('id', 'addPersonForm');
        newForm.setAttribute('onsubmit', 'return addPerson(event);');
        newForm.setAttribute('action', '#');
        newForm.setAttribute('method', 'POST');
        
        // Re-attach the onsubmit handler
        newForm.onsubmit = function(e) {
            return addPerson(e);
        };
    }
});
</script>
