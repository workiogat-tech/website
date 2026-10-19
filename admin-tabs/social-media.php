<?php
/**
 * Social Media Tab
 */
$socialMedia = $db->fetchAll("SELECT * FROM social_media WHERE is_active = 1 ORDER BY created_at DESC");
?>
<div class="content-card">
    <h2>🔗 Social Media</h2>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage social media links.</p>
        <button class="btn btn-primary" onclick="showAddSocialMediaModal()">➕ Add Social Media</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Platform</th>
                <th>URL</th>
                <th>Icon</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($socialMedia)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                        No social media links found. Click "Add Social Media" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($socialMedia as $social): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($social['platform'] ?? ''); ?></td>
                        <td><a href="<?php echo htmlspecialchars($social['url'] ?? ''); ?>" target="_blank" style="color: #3b82f6;"><?php echo htmlspecialchars($social['url'] ?? ''); ?></a></td>
                        <td><?php echo htmlspecialchars($social['icon'] ?? ''); ?></td>
                        <td>
                            <span style="padding: 4px 8px; background: <?php echo ($social['is_active'] ?? 1) ? '#28a745' : '#dc3545'; ?>; color: white; border-radius: 4px; font-size: 11px;">
                                <?php echo ($social['is_active'] ?? 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteSocialMedia(<?php echo $social['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Social Media Modal -->
<div id="addSocialMediaModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ Add New Social Media</h3>
        <form id="addSocialMediaForm" onsubmit="handleAddSocialMedia(event)">
            <div class="form-group">
                <label class="form-label">Platform *</label>
                <input type="text" name="platform" class="form-input" required placeholder="e.g., Facebook, Twitter, Instagram">
            </div>
            <div class="form-group">
                <label class="form-label">URL *</label>
                <input type="url" name="url" class="form-input" required placeholder="https://...">
            </div>
            <div class="form-group">
                <label class="form-label">Icon</label>
                <input type="text" name="icon" class="form-input" placeholder="e.g., 📘, 🐦, 📷">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Social Media</button>
                <button type="button" onclick="closeAddSocialMediaModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddSocialMediaModal() {
    document.getElementById('addSocialMediaModal').style.display = 'flex';
    document.getElementById('addSocialMediaForm').reset();
}

function closeAddSocialMediaModal() {
    document.getElementById('addSocialMediaModal').style.display = 'none';
}

function handleAddSocialMedia(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/social-media.php', {
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
            showToast(result.message || 'Social media added successfully!', 'success');
            closeAddSocialMediaModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add social media. Please try again.');
    });
}

function deleteSocialMedia(id) {
    if (!confirm('Are you sure you want to delete this social media link?')) return;
    
    fetch('api/content/social-media.php?id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            showToast(result.message || 'Social media deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete social media. Please try again.');
    });
}

document.getElementById('addSocialMediaModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddSocialMediaModal();
});
</script>
