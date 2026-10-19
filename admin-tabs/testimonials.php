<?php
/**
 * Testimonials Tab - With Page Header Image and Testimonial Images
 */
$testimonials = $db->fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC");
$testimonialsSettings = $db->fetchOne("SELECT * FROM testimonials_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>⭐ Testimonials</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage student and parent testimonials and page settings.</p>
    
    <!-- Page Header Image Section -->
    <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 25px; border-radius: 12px; border: 2px solid #f59e0b; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <span>🖼️</span> Testimonials Page Header Image
        </h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
            This is the large background image displayed at the top of the Testimonials page.
        </p>
        
        <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #f59e0b;">
            <p style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">How to change the page header image:</p>
            <ol style="margin-left: 20px; color: #666; font-size: 13px; line-height: 1.8;">
                <li>Click the <strong style="color: #f59e0b;">"Select Image"</strong> button below</li>
                <li>Browse your media library or upload a new image</li>
                <li>Click on the image you want to use</li>
                <li>Click <strong style="color: #f59e0b;">"Save Page Header Settings"</strong> to save your changes</li>
            </ol>
        </div>
        
        <form method="POST" action="api/content/testimonials-settings.php" id="testimonialsHeaderForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 20px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($testimonialsSettings['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <div style="flex-shrink: 0; position: relative;">
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Page header preview" 
                             style="width: 160px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #f59e0b; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
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
                           id="testimonials_hero_image" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($testimonialsSettings['hero_image'] ?? ''); ?>" 
                           placeholder="Image URL will appear here after selection"
                           readonly>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" 
                                onclick="openMediaLibrary('testimonials_hero_image')" 
                                class="btn btn-primary"
                                style="flex: 1; background: linear-gradient(135deg, #f59e0b, #d97706);">
                            🖼️ Select Image from Media Library
                        </button>
                        <?php if (!empty($testimonialsSettings['hero_image'])): ?>
                            <button type="button" 
                                    onclick="document.getElementById('testimonials_hero_image').value='';" 
                                    class="btn btn-danger"
                                    style="padding: 12px 20px;">
                                Remove Image
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
                <div class="form-group">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="title" class="form-input" 
                           value="<?php echo htmlspecialchars($testimonialsSettings['title'] ?? 'Testimonials'); ?>"
                           placeholder="Testimonials">
                </div>
                <div class="form-group">
                    <label class="form-label">Page Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" 
                           value="<?php echo htmlspecialchars($testimonialsSettings['subtitle'] ?? ''); ?>"
                           placeholder="What Our Community Says">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 20px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                💾 Save Page Header Settings
            </button>
        </form>
    </div>
    
    <!-- Slider Settings Section -->
    <div style="background: linear-gradient(135deg, #f3e8ff, #e9d5ff); padding: 25px; border-radius: 12px; border: 2px solid #9333ea; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <span>⚙️</span> Slider Settings
        </h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
            Configure how testimonials are displayed in the slider carousel.
        </p>
        
        <form method="POST" action="api/content/testimonials-settings.php" id="testimonialsSliderForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <!-- Enable Slider Toggle -->
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <span>Enable Slider</span>
                </label>
                <p style="color: #666; font-size: 13px; margin-bottom: 10px;">Show testimonials in a sliding carousel</p>
                <label style="position: relative; display: inline-block; width: 60px; height: 30px; cursor: pointer;">
                    <input type="checkbox" 
                           name="enable_slider" 
                           id="enable_slider"
                           value="1"
                           <?php echo (!isset($testimonialsSettings['enable_slider']) || $testimonialsSettings['enable_slider']) ? 'checked' : ''; ?>
                           style="opacity: 0; width: 0; height: 0;"
                           onchange="this.value = this.checked ? '1' : '0';">
                    <span style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo (!isset($testimonialsSettings['enable_slider']) || $testimonialsSettings['enable_slider']) ? '#9333ea' : '#ccc'; ?>; border-radius: 30px; transition: 0.3s;">
                        <span style="position: absolute; content: ''; height: 24px; width: 24px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; transform: translateX(<?php echo (!isset($testimonialsSettings['enable_slider']) || $testimonialsSettings['enable_slider']) ? '30px' : '0px'; ?>);"></span>
                    </span>
                </label>
            </div>
            
            <!-- Slide Direction -->
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label">Slide Direction</label>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" 
                            class="slide-direction-btn" 
                            data-direction="left"
                            onclick="setSlideDirection('left', this)"
                            style="flex: 1; padding: 12px 20px; border: 2px solid #9333ea; border-radius: 8px; background: <?php echo (!isset($testimonialsSettings['slide_direction']) || $testimonialsSettings['slide_direction'] === 'left') ? 'linear-gradient(135deg, #9333ea, #7c3aed)' : 'white'; ?>; color: <?php echo (!isset($testimonialsSettings['slide_direction']) || $testimonialsSettings['slide_direction'] === 'left') ? 'white' : '#9333ea'; ?>; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                        Left to Right →
                    </button>
                    <button type="button" 
                            class="slide-direction-btn" 
                            data-direction="right"
                            onclick="setSlideDirection('right', this)"
                            style="flex: 1; padding: 12px 20px; border: 2px solid #9333ea; border-radius: 8px; background: <?php echo (isset($testimonialsSettings['slide_direction']) && $testimonialsSettings['slide_direction'] === 'right') ? 'linear-gradient(135deg, #9333ea, #7c3aed)' : 'white'; ?>; color: <?php echo (isset($testimonialsSettings['slide_direction']) && $testimonialsSettings['slide_direction'] === 'right') ? 'white' : '#9333ea'; ?>; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                        Right to Left ←
                    </button>
                </div>
                <input type="hidden" name="slide_direction" id="slide_direction" value="<?php echo htmlspecialchars($testimonialsSettings['slide_direction'] ?? 'left'); ?>">
            </div>
            
            <!-- Slide Interval -->
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label">Slide Interval (ms)</label>
                <input type="number" 
                       name="slide_interval_ms" 
                       id="slide_interval_ms"
                       class="form-input" 
                       value="<?php echo htmlspecialchars($testimonialsSettings['slide_interval_ms'] ?? '3000'); ?>"
                       min="1000" 
                       max="10000" 
                       step="100"
                       required>
                <p style="color: #666; font-size: 12px; margin-top: 5px;">Time between slides (1000-10000 ms)</p>
            </div>
            
            <!-- Items Per View -->
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label">Items Per View</label>
                <input type="number" 
                       name="items_per_view" 
                       id="items_per_view"
                       class="form-input" 
                       value="<?php echo htmlspecialchars($testimonialsSettings['items_per_view'] ?? '3'); ?>"
                       min="1" 
                       max="6" 
                       required>
                <p style="color: #666; font-size: 12px; margin-top: 5px;">Number of testimonials visible at once (1-6)</p>
            </div>
            
            <!-- Card Width -->
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label">Card Width (px)</label>
                <input type="number" 
                       name="card_width" 
                       id="card_width"
                       class="form-input" 
                       value="<?php echo htmlspecialchars($testimonialsSettings['card_width'] ?? '280'); ?>"
                       min="200" 
                       max="600" 
                       required>
                <p style="color: #666; font-size: 12px; margin-top: 5px;">Width of each testimonial card in pixels (200-600)</p>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                <p style="color: #666; font-size: 14px; margin: 0;">
                    Total testimonials: <strong><?php echo count($testimonials); ?></strong>
                </p>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #9333ea, #7c3aed);">
                    💾 Save Settings
                </button>
            </div>
        </form>
    </div>
    
    <!-- Testimonials List -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage student and parent testimonials.</p>
        <button class="btn btn-primary" onclick="showAddTestimonialModal()">➕ Add Testimonial</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Position</th>
                <th>Content</th>
                <th>Rating</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($testimonials)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                        No testimonials found. Click "Add Testimonial" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($testimonials as $testimonial): ?>
                    <tr>
                        <td>
                            <?php 
                            $testimonialImageUrl = normalizeImageUrl($testimonial['image'] ?? '');
                            if (!empty($testimonialImageUrl)): ?>
                                <img src="<?php echo htmlspecialchars($testimonialImageUrl); ?>" 
                                     alt="<?php echo htmlspecialchars($testimonial['name']); ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; align-items: center; justify-content: center; color: #999; font-size: 20px;">⭐</div>
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #999; font-size: 20px;">⭐</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($testimonial['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($testimonial['position'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars(substr($testimonial['content'] ?? '', 0, 100)) . '...'; ?></td>
                        <td><?php echo str_repeat('⭐', $testimonial['rating'] ?? 5); ?></td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;" onclick="editTestimonial(<?php echo $testimonial['id']; ?>)">Edit</button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteTestimonial(<?php echo $testimonial['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Testimonial Modal -->
<div id="addTestimonialModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ Add New Testimonial</h3>
        <form id="addTestimonialForm" onsubmit="handleAddTestimonial(event)">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" name="position" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Content *</label>
                <textarea name="content" class="form-input" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Rating (1-5)</label>
                <input type="number" name="rating" class="form-input" min="1" max="5" value="5">
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="image" id="testimonial_image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('testimonial_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Testimonial</button>
                <button type="button" onclick="closeAddTestimonialModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Slider Settings Functions
function setSlideDirection(direction, button) {
    document.getElementById('slide_direction').value = direction;
    
    // Update button styles
    const buttons = document.querySelectorAll('.slide-direction-btn');
    buttons.forEach(btn => {
        if (btn.dataset.direction === direction) {
            btn.style.background = 'linear-gradient(135deg, #9333ea, #7c3aed)';
            btn.style.color = 'white';
        } else {
            btn.style.background = 'white';
            btn.style.color = '#9333ea';
        }
    });
}

// Toggle switch handler
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('enable_slider');
    if (toggle) {
        toggle.addEventListener('change', function() {
            const slider = this.nextElementSibling;
            if (this.checked) {
                slider.style.backgroundColor = '#9333ea';
                slider.querySelector('span').style.transform = 'translateX(30px)';
            } else {
                slider.style.backgroundColor = '#ccc';
                slider.querySelector('span').style.transform = 'translateX(0px)';
            }
        });
    }
});

function showAddTestimonialModal() {
    document.getElementById('addTestimonialModal').style.display = 'flex';
    document.getElementById('addTestimonialForm').reset();
}

function closeAddTestimonialModal() {
    document.getElementById('addTestimonialModal').style.display = 'none';
}

function handleAddTestimonial(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/testimonials.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Failed to parse response as JSON. Response was:', text.substring(0, 500));
            throw new Error('Server returned HTML instead of JSON. Check PHP error logs.');
        }
        return response.json();
    })
    .then(result => {
        if (result.error) {
            showToast('Error: ' + result.error, 'error');
        } else {
            showToast(result.message || 'Testimonial added successfully!', 'success');
            closeAddTestimonialModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        showToast('Save error: ' + error.message, 'error');
    });
}

function deleteTestimonial(id) {
    if (!confirm('Are you sure you want to delete this testimonial?')) return;
    
    fetch('api/content/testimonials.php?id=' + id, {
        method: 'DELETE'
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Failed to parse response as JSON. Response was:', text.substring(0, 500));
            throw new Error('Server returned HTML instead of JSON. Check PHP error logs.');
        }
        return response.json();
    })
    .then(result => {
        if (result.error) {
            showToast('Error: ' + result.error, 'error');
        } else {
            showToast(result.message || 'Testimonial deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Delete error: ' + error.message, 'error');
    });
}

document.getElementById('addTestimonialModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddTestimonialModal();
});

// Edit Testimonial Functionality
function editTestimonial(id) {
    fetch('api/content/testimonials.php?id=' + id)
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Failed to parse response as JSON. Response was:', text.substring(0, 500));
                throw new Error('Server returned HTML instead of JSON. Check PHP error logs.');
            }
            return response.json();
        })
        .then(result => {
            if (result.error) {
                showToast('Error loading testimonial: ' + result.error, 'error');
                return;
            }
            const testimonial = result.data || result;
            if (!testimonial) {
                showToast('Testimonial not found', 'error');
                return;
            }
            document.getElementById('editTestimonialForm').reset();
            document.getElementById('edit_testimonial_id').value = testimonial.id || id;
            document.getElementById('edit_testimonial_name').value = testimonial.name || '';
            document.getElementById('edit_testimonial_position').value = testimonial.position || '';
            document.getElementById('edit_testimonial_content').value = testimonial.content || '';
            document.getElementById('edit_testimonial_rating').value = testimonial.rating || 5;
            document.getElementById('edit_testimonial_image').value = testimonial.image || '';
            document.getElementById('editTestimonialModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading testimonial:', error);
            showToast('Failed to load testimonial: ' + error.message, 'error');
        });
}

function closeEditTestimonialModal() {
    document.getElementById('editTestimonialModal').style.display = 'none';
}

function handleEditTestimonial(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    fetch('api/content/testimonials.php', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Failed to parse response as JSON. Response was:', text.substring(0, 500));
            throw new Error('Server returned HTML instead of JSON. Check PHP error logs.');
        }
        return response.json();
    })
    .then(result => {
        if (result.error) {
            showToast('Error: ' + result.error, 'error');
        } else {
            showToast(result.message || 'Testimonial updated successfully!', 'success');
            closeEditTestimonialModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        showToast('Update error: ' + error.message, 'error');
    });
}
</script>

<!-- Edit Testimonial Modal -->
<div id="editTestimonialModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; font-weight: bold; margin: 0;">✏️ Edit Testimonial</h3>
            <button onclick="closeEditTestimonialModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>
        <form id="editTestimonialForm" onsubmit="handleEditTestimonial(event)">
            <input type="hidden" id="edit_testimonial_id" name="id">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" id="edit_testimonial_name" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" id="edit_testimonial_position" name="position" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Content *</label>
                <textarea id="edit_testimonial_content" name="content" class="form-input" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Rating (1-5)</label>
                <input type="number" id="edit_testimonial_rating" name="rating" class="form-input" min="1" max="5" value="5">
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" id="edit_testimonial_image" name="image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('edit_testimonial_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Update Testimonial</button>
                <button type="button" onclick="closeEditTestimonialModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editTestimonialModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditTestimonialModal();
});
</script>
