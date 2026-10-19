<?php
/**
 * Facilities Tab - With Page Header Image and Facility Images
 */
$facilities = $db->fetchAll("SELECT * FROM facilities WHERE is_active = 1 ORDER BY created_at DESC");
$facilitiesSettings = $db->fetchOne("SELECT * FROM facilities_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>🏢 Facilities</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage school facilities and page settings.</p>
    
    <!-- Page Header Image Section -->
    <div style="background: linear-gradient(135deg, #f3e8ff, #fce7f3); padding: 25px; border-radius: 12px; border: 2px solid #a855f7; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <span>🖼️</span> Facilities Page Header Image
        </h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
            This is the large background image displayed at the top of the Facilities page.
        </p>
        
        <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #a855f7;">
            <p style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">How to change the page header image:</p>
            <ol style="margin-left: 20px; color: #666; font-size: 13px; line-height: 1.8;">
                <li>Click the <strong style="color: #a855f7;">"Select Image"</strong> button below</li>
                <li>Browse your media library or upload a new image</li>
                <li>Click on the image you want to use</li>
                <li>Click <strong style="color: #a855f7;">"Save Page Header Settings"</strong> to save your changes</li>
            </ol>
        </div>
        
        <form method="POST" action="api/content/facilities-settings.php" id="facilitiesHeaderForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 20px;">
                <div id="facilities_hero_image_preview" style="flex-shrink: 0; position: relative;">
                    <?php 
                    $heroImageUrl = normalizeImageUrl($facilitiesSettings['hero_image'] ?? '');
                    $hasInitialImage = !empty($heroImageUrl);
                    ?>
                    <!-- Placeholder (shown when no image) -->
                    <div id="facilities_hero_image_placeholder" style="width: 160px; height: 96px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; display: <?php echo $hasInitialImage ? 'none' : 'flex'; ?>; align-items: center; justify-content: center; position: absolute; top: 0; left: 0;">
                        <div style="text-align: center;">
                            <span style="font-size: 24px; color: #999;">🖼️</span>
                            <p style="font-size: 11px; color: #999; margin-top: 5px;">No image</p>
                        </div>
                    </div>
                    <!-- Preview Image -->
                    <img id="facilities_hero_image_img" 
                         src="<?php echo $hasInitialImage ? htmlspecialchars($heroImageUrl) : ''; ?>" 
                         alt="Page header preview" 
                         style="width: 160px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #a855f7; box-shadow: 0 4px 6px rgba(0,0,0,0.1); <?php echo $hasInitialImage ? '' : 'display: none;'; ?>"
                         onerror="this.onerror=null; this.style.display='none'; if(document.getElementById('facilities_hero_image_placeholder')) document.getElementById('facilities_hero_image_placeholder').style.display='flex'; if(document.getElementById('facilities_hero_image_error')) document.getElementById('facilities_hero_image_error').style.display='flex';"
                         onload="this.style.display='block'; if(document.getElementById('facilities_hero_image_placeholder')) document.getElementById('facilities_hero_image_placeholder').style.display='none'; if(document.getElementById('facilities_hero_image_error')) document.getElementById('facilities_hero_image_error').style.display='none'; if(document.getElementById('facilities_hero_image_label')) document.getElementById('facilities_hero_image_label').style.display='block';">
                    <!-- Error Message (shown when image fails to load) -->
                    <div id="facilities_hero_image_error" style="display: none; width: 160px; height: 96px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center; position: absolute; top: 0; left: 0;">
                        <div style="text-align: center;">
                            <span style="font-size: 24px; color: #999;">🖼️</span>
                            <p style="font-size: 11px; color: #999; margin-top: 5px;">Failed to load</p>
                        </div>
                    </div>
                    <!-- Preview Label -->
                    <p id="facilities_hero_image_label" style="text-align: center; font-size: 11px; color: #666; margin-top: 5px; display: <?php echo $hasInitialImage ? 'block' : 'none'; ?>;">Preview</p>
                </div>
                <div style="flex: 1;">
                    <input type="text" 
                           name="hero_image" 
                           id="facilities_hero_image" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($facilitiesSettings['hero_image'] ?? ''); ?>" 
                           placeholder="Image URL will appear here after selection"
                           readonly>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" 
                                onclick="openMediaLibrary('facilities_hero_image')" 
                                class="btn btn-primary"
                                style="flex: 1; background: linear-gradient(135deg, #9333ea, #ec4899);">
                            🖼️ Select Image from Media Library
                        </button>
                        <button type="button" 
                                id="facilities_hero_image_remove" 
                                onclick="removeFacilitiesHeroImage()" 
                                class="btn btn-danger"
                                style="padding: 12px 20px; <?php echo empty($facilitiesSettings['hero_image']) ? 'display: none;' : ''; ?>">
                            Remove Image
                        </button>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
                <div class="form-group">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="title" class="form-input" 
                           value="<?php echo htmlspecialchars($facilitiesSettings['title'] ?? 'Our Facilities'); ?>"
                           placeholder="Our Facilities">
                </div>
                <div class="form-group">
                    <label class="form-label">Page Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" 
                           value="<?php echo htmlspecialchars($facilitiesSettings['subtitle'] ?? ''); ?>"
                           placeholder="World-Class Facilities for Learning and Growth">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 20px; background: linear-gradient(135deg, #9333ea, #ec4899);">
                💾 Save Page Header Settings
            </button>
        </form>
    </div>
    
    <!-- Section Header and Features -->
    <div style="background: linear-gradient(135deg, #e0f2fe, #dbeafe); padding: 25px; border-radius: 12px; border: 2px solid #3b82f6; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span>📋</span> Section Header & Features
        </h3>
        
        <form method="POST" action="api/content/facilities-settings.php" id="facilitiesFeaturesForm" onsubmit="return handleFeaturesFormSubmit(event, this);">
            <!-- Section Header -->
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #3b82f6;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 15px;">Section Header</h4>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Section Title</label>
                    <input type="text" name="title" class="form-input" 
                           value="<?php echo htmlspecialchars($facilitiesSettings['title'] ?? 'World-Class Facilities'); ?>"
                           placeholder="World-Class Facilities">
                </div>
                <div class="form-group">
                    <label class="form-label">Section Subtitle</label>
                    <textarea name="subtitle" class="form-input" rows="3" 
                              placeholder="Our modern facilities provide students with the best environment for learning, creativity, and personal development across all areas of study."><?php echo htmlspecialchars($facilitiesSettings['subtitle'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <!-- Features Management -->
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #3b82f6;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Features</h4>
                    <button type="button" onclick="addFeature()" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669);">
                        ➕ Add Feature
                    </button>
                </div>
                
                <div id="featuresContainer">
                    <?php 
                    $features = [];
                    if (!empty($facilitiesSettings['features'])) {
                        $decoded = json_decode($facilitiesSettings['features'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $features = $decoded;
                        }
                    }
                    
                    if (empty($features)) {
                        // Default empty feature
                        $features = [];
                    }
                    
                    foreach ($features as $index => $feature): 
                        $feature = array_merge([
                            'title' => '',
                            'icon' => 'Wifi',
                            'description' => '',
                            'bgColor' => 'bg-blue-100',
                            'iconColor' => 'text-blue-600'
                        ], $feature);
                    ?>
                        <div class="feature-item" data-index="<?php echo $index; ?>" style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                <h5 style="font-size: 14px; font-weight: 600; color: #666; margin: 0;">Feature <?php echo $index + 1; ?></h5>
                                <button type="button" onclick="removeFeature(<?php echo $index; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                                    🗑️ Remove
                                </button>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div class="form-group">
                                    <label class="form-label">Feature Title *</label>
                                    <input type="text" name="features[<?php echo $index; ?>][title]" class="form-input" 
                                           value="<?php echo htmlspecialchars($feature['title']); ?>" 
                                           placeholder="e.g., High-Speed WiFi" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Icon *</label>
                                    <select name="features[<?php echo $index; ?>][icon]" class="form-input" required>
                                        <option value="Wifi" <?php echo $feature['icon'] === 'Wifi' ? 'selected' : ''; ?>>Wifi</option>
                                        <option value="Shield" <?php echo $feature['icon'] === 'Shield' ? 'selected' : ''; ?>>Shield</option>
                                        <option value="Zap" <?php echo $feature['icon'] === 'Zap' ? 'selected' : ''; ?>>Zap</option>
                                        <option value="Users" <?php echo $feature['icon'] === 'Users' ? 'selected' : ''; ?>>Users</option>
                                        <option value="Book" <?php echo $feature['icon'] === 'Book' ? 'selected' : ''; ?>>Book</option>
                                        <option value="GraduationCap" <?php echo $feature['icon'] === 'GraduationCap' ? 'selected' : ''; ?>>Graduation Cap</option>
                                        <option value="Monitor" <?php echo $feature['icon'] === 'Monitor' ? 'selected' : ''; ?>>Monitor</option>
                                        <option value="Heart" <?php echo $feature['icon'] === 'Heart' ? 'selected' : ''; ?>>Heart</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Background Color</label>
                                    <input type="text" name="features[<?php echo $index; ?>][bgColor]" class="form-input" 
                                           value="<?php echo htmlspecialchars($feature['bgColor']); ?>" 
                                           placeholder="bg-blue-100">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Icon Color</label>
                                    <input type="text" name="features[<?php echo $index; ?>][iconColor]" class="form-input" 
                                           value="<?php echo htmlspecialchars($feature['iconColor']); ?>" 
                                           placeholder="text-blue-600">
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top: 15px;">
                                <label class="form-label">Description *</label>
                                <textarea name="features[<?php echo $index; ?>][description]" class="form-input" rows="2" 
                                          placeholder="Describe the feature..." required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="emptyFeaturesMessage" style="<?php echo !empty($features) ? 'display: none;' : ''; ?> text-align: center; padding: 40px; color: #999; background: #f9fafb; border-radius: 8px; border: 2px dashed #e5e7eb;">
                    <p style="margin: 0;">No features added yet. Click "+ Add Feature" to get started.</p>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 20px; background: linear-gradient(135deg, #3b82f6, #2563eb); width: 100%;">
                💾 Save Section Header & Features
            </button>
        </form>
    </div>
    
    <!-- Facilities List -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage school facilities.</p>
        <button class="btn btn-primary" onclick="showAddFacilityModal()">➕ Add Facility</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Label</th>
                <th>Capacity</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($facilities)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                        No facilities found. Click "Add Facility" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($facilities as $facility): ?>
                    <tr>
                        <td>
                            <?php 
                            $facilityImageUrl = normalizeImageUrl($facility['image'] ?? '');
                            if (!empty($facilityImageUrl)): ?>
                                <img src="<?php echo htmlspecialchars($facilityImageUrl); ?>" 
                                     alt="<?php echo htmlspecialchars($facility['name']); ?>"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 60px; height: 60px; background: #e0e0e0; border-radius: 8px; align-items: center; justify-content: center; color: #999; font-size: 24px;">🏢</div>
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; background: #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 24px;">🏢</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($facility['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($facility['label'] ?? '-'); ?></td>
                        <td><?php echo !empty($facility['capacity']) ? htmlspecialchars($facility['capacity']) . ' people' : '-'; ?></td>
                        <td><?php echo htmlspecialchars(substr($facility['description'] ?? '', 0, 100)) . '...'; ?></td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;" onclick="editFacility(<?php echo $facility['id']; ?>)">Edit</button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteFacility(<?php echo $facility['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Facility Modal -->
<div id="addFacilityModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ Add New Facility</h3>
        <form id="addFacilityForm" onsubmit="handleAddFacility(event)">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-input" required placeholder="e.g., Clinic, Washroom">
            </div>
            <div class="form-group">
                <label class="form-label">Image Label (Overlay)</label>
                <input type="text" name="label" class="form-input" placeholder="e.g., Nurse Office, Recreation (shown on image)">
                <p style="color: #666; font-size: 12px; margin-top: 5px;">This label appears as an overlay on the image</p>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="4" placeholder="Describe the facility..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Capacity (Number of People)</label>
                <input type="number" name="capacity" class="form-input" min="1" placeholder="e.g., 20">
                <p style="color: #666; font-size: 12px; margin-top: 5px;">Maximum number of people the facility can accommodate</p>
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="image" id="facility_image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('facility_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Facility</button>
                <button type="button" onclick="closeAddFacilityModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddFacilityModal() {
    document.getElementById('addFacilityModal').style.display = 'flex';
    document.getElementById('addFacilityForm').reset();
}

function closeAddFacilityModal() {
    document.getElementById('addFacilityModal').style.display = 'none';
}

function handleAddFacility(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/facilities.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            // Suppress "Fatal error" messages
            const errorMsg = result.error.toLowerCase().includes('fatal error') || result.error.toLowerCase().includes('server error: fatal')
                ? 'An error occurred. Please try again later.'
                : result.error;
            console.error('Facility error:', result.error);
            // Only show alert if it's not a fatal error
            if (!result.error.toLowerCase().includes('fatal error')) {
                alert('Error: ' + errorMsg);
            }
        } else {
            showToast(result.message || 'Facility added successfully!', 'success');
            closeAddFacilityModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Suppress fatal error messages
        if (!error.message || (!error.message.toLowerCase().includes('fatal error') && !error.message.toLowerCase().includes('server error: fatal'))) {
            alert('Failed to add facility. Please try again.');
        }
    });
}

function deleteFacility(id) {
    if (!confirm('Are you sure you want to delete this facility?')) return;
    
    fetch('api/content/facilities.php?id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            // Suppress "Fatal error" messages
            const errorMsg = result.error.toLowerCase().includes('fatal error') || result.error.toLowerCase().includes('server error: fatal')
                ? 'An error occurred. Please try again later.'
                : result.error;
            console.error('Facility delete error:', result.error);
            // Only show alert if it's not a fatal error
            if (!result.error.toLowerCase().includes('fatal error')) {
                alert('Error: ' + errorMsg);
            }
        } else {
            showToast(result.message || 'Facility deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Suppress fatal error messages
        if (!error.message || (!error.message.toLowerCase().includes('fatal error') && !error.message.toLowerCase().includes('server error: fatal'))) {
            alert('Failed to delete facility. Please try again.');
        }
    });
}

document.getElementById('addFacilityModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddFacilityModal();
});

// Edit Facility Functionality
function editFacility(id) {
    fetch('api/content/facilities.php?id=' + id)
        .then(response => response.json())
        .then(result => {
            if (result.error) {
                showToast('Error loading facility: ' + result.error, 'error');
                return;
            }
            const facility = result.data || result;
            if (!facility) {
                showToast('Facility not found', 'error');
                return;
            }
            document.getElementById('editFacilityForm').reset();
            document.getElementById('edit_facility_id').value = facility.id || id;
            document.getElementById('edit_facility_name').value = facility.name || '';
            document.getElementById('edit_facility_label').value = facility.label || '';
            document.getElementById('edit_facility_description').value = facility.description || '';
            document.getElementById('edit_facility_capacity').value = facility.capacity || '';
            document.getElementById('edit_facility_image').value = facility.image || '';
            document.getElementById('editFacilityModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading facility:', error);
            showToast('Failed to load facility. Please try again.', 'error');
        });
}

function closeEditFacilityModal() {
    document.getElementById('editFacilityModal').style.display = 'none';
}

function handleEditFacility(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    fetch('api/content/facilities.php', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            // Suppress "Fatal error" messages
            const errorMsg = result.error.toLowerCase().includes('fatal error') || result.error.toLowerCase().includes('server error: fatal')
                ? 'An error occurred. Please try again later.'
                : result.error;
            console.error('Facility update error:', result.error);
            // Only show toast if it's not a fatal error
            if (!result.error.toLowerCase().includes('fatal error')) {
                showToast('Error: ' + errorMsg, 'error');
            }
        } else {
            showToast(result.message || 'Facility updated successfully!', 'success');
            closeEditFacilityModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Suppress fatal error messages
        if (!error.message || (!error.message.toLowerCase().includes('fatal error') && !error.message.toLowerCase().includes('server error: fatal'))) {
            showToast('Failed to update facility. Please try again.', 'error');
        }
    });
}
</script>

<!-- Edit Facility Modal -->
<div id="editFacilityModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; font-weight: bold; margin: 0;">✏️ Edit Facility</h3>
            <button onclick="closeEditFacilityModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>
        <form id="editFacilityForm" onsubmit="handleEditFacility(event)">
            <input type="hidden" id="edit_facility_id" name="id">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" id="edit_facility_name" name="name" class="form-input" required placeholder="e.g., Clinic, Washroom">
            </div>
            <div class="form-group">
                <label class="form-label">Image Label (Overlay)</label>
                <input type="text" id="edit_facility_label" name="label" class="form-input" placeholder="e.g., Nurse Office, Recreation (shown on image)">
                <p style="color: #666; font-size: 12px; margin-top: 5px;">This label appears as an overlay on the image</p>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="edit_facility_description" name="description" class="form-input" rows="4" placeholder="Describe the facility..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Capacity (Number of People)</label>
                <input type="number" id="edit_facility_capacity" name="capacity" class="form-input" min="1" placeholder="e.g., 20">
                <p style="color: #666; font-size: 12px; margin-top: 5px;">Maximum number of people the facility can accommodate</p>
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" id="edit_facility_image" name="image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('edit_facility_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Update Facility</button>
                <button type="button" onclick="closeEditFacilityModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editFacilityModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditFacilityModal();
});
</script>

<script>
// Update preview when image is selected
function updateFacilitiesHeroImagePreview(imageUrl) {
    const previewImg = document.getElementById('facilities_hero_image_img');
    const placeholder = document.getElementById('facilities_hero_image_placeholder');
    const errorDiv = document.getElementById('facilities_hero_image_error');
    const label = document.getElementById('facilities_hero_image_label');
    const removeBtn = document.getElementById('facilities_hero_image_remove');
    
    if (!imageUrl || imageUrl.trim() === '') {
        // Clear preview
        if (previewImg) {
            previewImg.style.display = 'none';
            previewImg.src = '';
        }
        if (placeholder) {
            placeholder.style.display = 'flex';
        }
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
        if (label) {
            label.style.display = 'none';
        }
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
        return;
    }
    
    // Normalize the URL (handle relative paths)
    let normalizedUrl = imageUrl;
    if (!normalizedUrl.startsWith('http://') && !normalizedUrl.startsWith('https://')) {
        if (!normalizedUrl.startsWith('/')) {
            normalizedUrl = '/' + normalizedUrl;
        }
    }
    
    // Show preview
    if (previewImg) {
        previewImg.src = normalizedUrl;
        previewImg.style.display = 'block';
        previewImg.onerror = function() {
            this.style.display = 'none';
            if (placeholder) placeholder.style.display = 'flex';
            if (errorDiv) errorDiv.style.display = 'flex';
        };
        previewImg.onload = function() {
            this.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
            if (errorDiv) errorDiv.style.display = 'none';
            if (label) label.style.display = 'block';
        };
    }
    if (placeholder) {
        placeholder.style.display = 'none';
    }
    if (label) {
        label.style.display = 'block';
    }
    if (removeBtn) {
        removeBtn.style.display = 'block';
    }
}

function removeFacilitiesHeroImage() {
    const input = document.getElementById('facilities_hero_image');
    if (input) {
        input.value = '';
        updateFacilitiesHeroImagePreview('');
    }
}

// Listen for changes to the input field
(function() {
    function setupFacilitiesPreview() {
        const input = document.getElementById('facilities_hero_image');
        if (!input) {
            // Retry if input not found yet
            setTimeout(setupFacilitiesPreview, 100);
            return;
        }
        
        // Update preview when input changes (e.g., from media library selection)
        input.addEventListener('change', function() {
            updateFacilitiesHeroImagePreview(this.value);
        });
        
        // Also listen for input events (for programmatic changes)
        input.addEventListener('input', function() {
            updateFacilitiesHeroImagePreview(this.value);
        });
        
        // Override selectImage function to update preview for facilities_hero_image
        // Wait a bit to ensure admin.php's selectImage is loaded
        function hookSelectImage() {
            if (typeof window.selectImage === 'function') {
                const originalSelectImage = window.selectImage;
                window.selectImage = function(imageUrl) {
                    const result = originalSelectImage.apply(this, arguments);
                    // If this is for facilities_hero_image, update preview immediately
                    if (typeof window.currentImageField !== 'undefined' && window.currentImageField === 'facilities_hero_image') {
                        // Update preview with the selected image URL
                        updateFacilitiesHeroImagePreview(imageUrl);
                    }
                    return result;
                };
            } else {
                // Retry if selectImage not loaded yet
                setTimeout(hookSelectImage, 100);
            }
        }
        hookSelectImage();
        
        // Fallback: poll for value changes to catch any programmatic updates
        let lastValue = input.value;
        setInterval(function() {
            const currentValue = input.value;
            if (currentValue !== lastValue) {
                lastValue = currentValue;
                updateFacilitiesHeroImagePreview(currentValue);
            }
        }, 300);
    }
    
    // Start setup when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupFacilitiesPreview);
    } else {
        setupFacilitiesPreview();
    }
})();

// Features Management Functions
let featureIndex = <?php echo count($features ?? []); ?>;

function addFeature() {
    const container = document.getElementById('featuresContainer');
    const emptyMessage = document.getElementById('emptyFeaturesMessage');
    
    const featureHtml = `
        <div class="feature-item" data-index="${featureIndex}" style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h5 style="font-size: 14px; font-weight: 600; color: #666; margin: 0;">Feature ${featureIndex + 1}</h5>
                <button type="button" onclick="removeFeature(${featureIndex})" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                    🗑️ Remove
                </button>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Feature Title *</label>
                    <input type="text" name="features[${featureIndex}][title]" class="form-input" 
                           placeholder="e.g., High-Speed WiFi" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Icon *</label>
                    <select name="features[${featureIndex}][icon]" class="form-input" required>
                        <option value="Wifi">Wifi</option>
                        <option value="Shield">Shield</option>
                        <option value="Zap">Zap</option>
                        <option value="Users">Users</option>
                        <option value="Book">Book</option>
                        <option value="GraduationCap">Graduation Cap</option>
                        <option value="Monitor">Monitor</option>
                        <option value="Heart">Heart</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Background Color</label>
                    <input type="text" name="features[${featureIndex}][bgColor]" class="form-input" 
                           value="bg-blue-100" placeholder="bg-blue-100">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Icon Color</label>
                    <input type="text" name="features[${featureIndex}][iconColor]" class="form-input" 
                           value="text-blue-600" placeholder="text-blue-600">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label class="form-label">Description *</label>
                <textarea name="features[${featureIndex}][description]" class="form-input" rows="2" 
                          placeholder="Describe the feature..." required></textarea>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', featureHtml);
    featureIndex++;
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    // Scroll to the new feature
    const newFeature = container.lastElementChild;
    if (newFeature) {
        newFeature.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function removeFeature(index) {
    const featureItem = document.querySelector(`.feature-item[data-index="${index}"]`);
    if (featureItem) {
        featureItem.remove();
        
        // Check if any features remain
        const container = document.getElementById('featuresContainer');
        const emptyMessage = document.getElementById('emptyFeaturesMessage');
        const remainingFeatures = container.querySelectorAll('.feature-item');
        
        if (remainingFeatures.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        }
        
        // Reindex remaining features
        reindexFeatures();
    }
}

function reindexFeatures() {
    const container = document.getElementById('featuresContainer');
    const features = container.querySelectorAll('.feature-item');
    features.forEach((feature, newIndex) => {
        feature.setAttribute('data-index', newIndex);
        const title = feature.querySelector('h5');
        if (title) {
            title.textContent = `Feature ${newIndex + 1}`;
        }
        
        // Update all input names
        const inputs = feature.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/features\[\d+\]/, `features[${newIndex}]`);
            }
        });
        
        // Update remove button
        const removeBtn = feature.querySelector('button[onclick*="removeFeature"]');
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeFeature(${newIndex})`);
        }
    });
    
    featureIndex = features.length;
}

// Handle features form submission
function handleFeaturesFormSubmit(event, form) {
    event.preventDefault();
    
    // Collect features data
    const features = [];
    const featureItems = document.querySelectorAll('.feature-item');
    
    featureItems.forEach((item) => {
        const title = item.querySelector('input[name*="[title]"]')?.value?.trim();
        const icon = item.querySelector('select[name*="[icon]"]')?.value;
        const description = item.querySelector('textarea[name*="[description]"]')?.value?.trim();
        const bgColor = item.querySelector('input[name*="[bgColor]"]')?.value?.trim() || 'bg-blue-100';
        const iconColor = item.querySelector('input[name*="[iconColor]"]')?.value?.trim() || 'text-blue-600';
        
        if (title && icon && description) {
            features.push({
                title: title,
                icon: icon,
                description: description,
                bgColor: bgColor,
                iconColor: iconColor
            });
        }
    });
    
    // Prepare data
    const data = {
        title: form.querySelector('input[name="title"]')?.value || '',
        subtitle: form.querySelector('textarea[name="subtitle"]')?.value || '',
        features: features
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
            showToast(result.message || 'Settings saved successfully!', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to save settings. Please try again.', 'error');
    });
    
    return false;
}
</script>
