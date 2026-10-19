<?php
/**
 * Home Sections Tab - With Why Choose Image
 */
$homeSections = $db->fetchOne("SELECT * FROM home_sections_content WHERE section_name = 'why-choose' AND is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$sections = $db->fetchAll("SELECT * FROM home_sections_content WHERE is_active = 1 ORDER BY order_index ASC");
?>
<div class="content-card">
    <h2>🌐 Home Sections</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage homepage sections.</p>
    
    <!-- Why Choose Section Image -->
    <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); padding: 25px; border-radius: 12px; border: 2px solid #0ea5e9; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px;">🖼️ Why Choose Section Image</h3>
        <form method="POST" action="api/content/home-sections.php" id="homeSectionsForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <input type="hidden" name="section_name" value="why-choose">
            <div style="display: flex; align-items: center; gap: 15px;">
                <?php 
                $imageUrl = normalizeImageUrl($homeSections['image'] ?? '');
                if (!empty($imageUrl)): ?>
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                         alt="Why Choose Image"
                         style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #0ea5e9;"
                         onerror="this.onerror=null; this.style.display='none';">
                <?php endif; ?>
                <div style="flex: 1;">
                    <input type="text" name="image" id="why_choose_image" class="form-input" 
                           value="<?php echo htmlspecialchars($homeSections['image'] ?? ''); ?>" 
                           placeholder="Select image from media library" readonly>
                </div>
                <button type="button" onclick="openMediaLibrary('why_choose_image')" 
                        class="btn btn-primary" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                    🖼️ Select Image
                </button>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 15px; background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                💾 Save Image
            </button>
        </form>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage homepage sections.</p>
        <button class="btn btn-primary" onclick="showAddSectionModal()">➕ Add Section</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Section Name</th>
                <th>Title</th>
                <th>Order</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sections)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                        No sections found. Click "Add Section" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($sections as $section): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($section['section_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($section['title'] ?? ''); ?></td>
                        <td><?php echo $section['order_index'] ?? 0; ?></td>
                        <td>
                            <span style="padding: 4px 8px; background: <?php echo ($section['is_active'] ?? 1) ? '#28a745' : '#dc3545'; ?>; color: white; border-radius: 4px; font-size: 11px;">
                                <?php echo ($section['is_active'] ?? 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteHomeSection(<?php echo $section['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Section Modal -->
<div id="addSectionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ Add New Home Section</h3>
        <form id="addSectionForm" onsubmit="handleAddSection(event)">
            <div class="form-group">
                <label class="form-label">Section Name *</label>
                <input type="text" name="section_name" class="form-input" required placeholder="e.g., why-choose, features">
            </div>
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-input" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="image" id="section_image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('section_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Section</button>
                <button type="button" onclick="closeAddSectionModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddSectionModal() {
    document.getElementById('addSectionModal').style.display = 'flex';
    document.getElementById('addSectionForm').reset();
}

function closeAddSectionModal() {
    document.getElementById('addSectionModal').style.display = 'none';
}

function handleAddSection(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/home-sections.php', {
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
            showToast(result.message || 'Home section added successfully!', 'success');
            closeAddSectionModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add home section. Please try again.');
    });
}

function deleteHomeSection(id) {
    if (!confirm('Are you sure you want to delete this home section?')) return;
    
    fetch('api/content/home-sections.php?id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            showToast(result.message || 'Home section deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete home section. Please try again.');
    });
}

document.getElementById('addSectionModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddSectionModal();
});
</script>
