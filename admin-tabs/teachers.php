<?php
/**
 * Teachers Tab - With Page Header Image and Teacher Images
 */
$teachers = $db->fetchAll("SELECT * FROM teachers WHERE is_active = 1 ORDER BY created_at DESC");
$teachersSettings = $db->fetchOne("SELECT * FROM teachers_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>👨‍🏫 Teachers</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage teaching staff and page settings.</p>
    
    <!-- Page Header Image Section -->
    <div style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); padding: 25px; border-radius: 12px; border: 2px solid #10b981; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <span>🖼️</span> Teachers Page Header Image
        </h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
            This is the large background image displayed at the top of the Teachers page.
        </p>
        
        <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #10b981;">
            <p style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">How to change the page header image:</p>
            <ol style="margin-left: 20px; color: #666; font-size: 13px; line-height: 1.8;">
                <li>Click the <strong style="color: #10b981;">"Select Image"</strong> button below</li>
                <li>Browse your media library or upload a new image</li>
                <li>Click on the image you want to use</li>
                <li>Click <strong style="color: #10b981;">"Save Page Header Settings"</strong> to save your changes</li>
            </ol>
        </div>
        
        <form method="POST" action="api/content/teachers-settings.php" id="teachersHeaderForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 20px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($teachersSettings['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <div style="flex-shrink: 0; position: relative;">
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Page header preview" 
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
                           id="teachers_hero_image" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($teachersSettings['hero_image'] ?? ''); ?>" 
                           placeholder="Image URL will appear here after selection"
                           readonly>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" 
                                onclick="openMediaLibrary('teachers_hero_image')" 
                                class="btn btn-primary"
                                style="flex: 1; background: linear-gradient(135deg, #16a34a, #059669);">
                            🖼️ Select Image from Media Library
                        </button>
                        <?php if (!empty($teachersSettings['hero_image'])): ?>
                            <button type="button" 
                                    onclick="document.getElementById('teachers_hero_image').value='';" 
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
                           value="<?php echo htmlspecialchars($teachersSettings['title'] ?? 'Our Teachers'); ?>"
                           placeholder="Our Teachers">
                </div>
                <div class="form-group">
                    <label class="form-label">Page Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" 
                           value="<?php echo htmlspecialchars($teachersSettings['subtitle'] ?? ''); ?>"
                           placeholder="Meet Our Expert Faculty Members">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 20px; background: linear-gradient(135deg, #16a34a, #059669);">
                💾 Save Page Header Settings
            </button>
        </form>
    </div>
    
    <!-- Teachers List -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage teaching staff.</p>
        <button class="btn btn-primary" onclick="showAddTeacherModal()">➕ Add Teacher</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Position</th>
                <th>Department</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($teachers)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                        No teachers found. Click "Add Teacher" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($teachers as $teacher): ?>
                    <tr>
                        <td>
                            <?php 
                            $teacherImageUrl = normalizeImageUrl($teacher['image'] ?? '');
                            if (!empty($teacherImageUrl)): ?>
                                <img src="<?php echo htmlspecialchars($teacherImageUrl); ?>" 
                                     alt="<?php echo htmlspecialchars($teacher['name']); ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; align-items: center; justify-content: center; color: #999; font-size: 20px;">👨‍🏫</div>
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #999;">👤</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($teacher['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($teacher['position'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($teacher['department'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($teacher['email'] ?? ''); ?></td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;" onclick="editTeacher(<?php echo $teacher['id']; ?>)">Edit</button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteTeacher(<?php echo $teacher['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Teacher Modal -->
<div id="addTeacherModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; font-weight: bold; margin: 0;">➕ Add New Teacher</h3>
            <button onclick="closeAddTeacherModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>
        <form id="addTeacherForm" onsubmit="handleAddTeacher(event)">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" name="position" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-input">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="image" id="teacher_image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('teacher_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Teacher</button>
                <button type="button" onclick="closeAddTeacherModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddTeacherModal() {
    document.getElementById('addTeacherModal').style.display = 'flex';
    document.getElementById('addTeacherForm').reset();
}

function closeAddTeacherModal() {
    document.getElementById('addTeacherModal').style.display = 'none';
}

function handleAddTeacher(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/teachers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            showToast(result.message || 'Teacher added successfully!', 'success');
            closeAddTeacherModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add teacher. Please try again.');
    });
}

function deleteTeacher(id) {
    if (!confirm('Are you sure you want to delete this teacher?')) return;
    
    fetch('api/content/teachers.php?id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            showToast(result.message || 'Teacher deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete teacher. Please try again.');
    });
}

document.getElementById('addTeacherModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddTeacherModal();
});

// Edit Teacher Functionality
let currentEditTeacherId = null;

function editTeacher(id) {
    currentEditTeacherId = id;
    fetch('api/content/teachers.php?id=' + id)
        .then(response => response.json())
        .then(result => {
            if (result.error) {
                showToast('Error loading teacher: ' + result.error, 'error');
                return;
            }
            const teacher = result.data || result;
            if (!teacher) {
                showToast('Teacher not found', 'error');
                return;
            }
            document.getElementById('editTeacherForm').reset();
            document.getElementById('edit_teacher_id').value = teacher.id || id;
            document.getElementById('edit_teacher_name').value = teacher.name || '';
            document.getElementById('edit_teacher_position').value = teacher.position || '';
            document.getElementById('edit_teacher_department').value = teacher.department || '';
            document.getElementById('edit_teacher_email').value = teacher.email || '';
            document.getElementById('edit_teacher_phone').value = teacher.phone || '';
            document.getElementById('edit_teacher_bio').value = teacher.bio || '';
            document.getElementById('edit_teacher_image').value = teacher.image || '';
            document.getElementById('editTeacherModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading teacher:', error);
            showToast('Failed to load teacher. Please try again.', 'error');
        });
}

function closeEditTeacherModal() {
    document.getElementById('editTeacherModal').style.display = 'none';
    currentEditTeacherId = null;
}

function handleEditTeacher(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    fetch('api/content/teachers.php', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            showToast('Error: ' + result.error, 'error');
        } else {
            showToast(result.message || 'Teacher updated successfully!', 'success');
            closeEditTeacherModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update teacher. Please try again.', 'error');
    });
}
</script>

<!-- Edit Teacher Modal -->
<div id="editTeacherModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; font-weight: bold; margin: 0;">✏️ Edit Teacher</h3>
            <button onclick="closeEditTeacherModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>
        <form id="editTeacherForm" onsubmit="handleEditTeacher(event)">
            <input type="hidden" id="edit_teacher_id" name="id">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" id="edit_teacher_name" name="name" class="form-input" required>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" id="edit_teacher_position" name="position" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" id="edit_teacher_department" name="department" class="form-input">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="edit_teacher_email" name="email" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" id="edit_teacher_phone" name="phone" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea id="edit_teacher_bio" name="bio" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" id="edit_teacher_image" name="image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('edit_teacher_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Update Teacher</button>
                <button type="button" onclick="closeEditTeacherModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editTeacherModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditTeacherModal();
});
</script>
