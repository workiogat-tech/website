<?php
$announcements = $db->fetchAll("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 20");
$announcementsSettings = $db->fetchOne("SELECT * FROM announcements_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>📢 Announcements</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage announcements and page settings.</p>
    
    <div style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); padding: 25px; border-radius: 12px; border: 2px solid #10b981; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px;">🖼️ Announcements Page Header Image</h3>
        <form method="POST" action="api/content/announcements-settings.php" id="announcementsSettingsForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 20px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($announcementsSettings['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <div style="flex-shrink: 0; position: relative;">
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview"
                             style="width: 160px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981;"
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display: none; width: 160px; height: 96px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center; position: absolute; top: 0; left: 0;">
                            <div style="text-align: center;">
                                <span style="font-size: 24px; color: #999;">🖼️</span>
                                <p style="font-size: 11px; color: #999; margin-top: 5px;">Failed to load</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div style="flex: 1;">
                    <input type="text" name="hero_image" id="announcements_hero_image" class="form-input" 
                           value="<?php echo htmlspecialchars($announcementsSettings['hero_image'] ?? ''); ?>" readonly>
                    <button type="button" onclick="openMediaLibrary('announcements_hero_image')" 
                            class="btn btn-primary" style="margin-top: 10px; background: linear-gradient(135deg, #16a34a, #059669);">
                        🖼️ Select Image
                    </button>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="title" class="form-input" 
                           value="<?php echo htmlspecialchars($announcementsSettings['title'] ?? 'Announcements'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Page Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" 
                           value="<?php echo htmlspecialchars($announcementsSettings['subtitle'] ?? ''); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 20px;">💾 Save Settings</button>
        </form>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage announcements.</p>
        <button class="btn btn-primary" onclick="showAddAnnouncementModal()">➕ Add Announcement</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr><th>Image</th><th>Title</th><th>Published</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (empty($announcements)): ?>
                <tr><td colspan="4" style="text-align: center; padding: 40px; color: #999;">No announcements found.</td></tr>
            <?php else: ?>
                <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td>
                            <?php 
                            $announcementImageUrl = normalizeImageUrl($announcement['image'] ?? '');
                            if (!empty($announcementImageUrl)): ?>
                                <img src="<?php echo htmlspecialchars($announcementImageUrl); ?>" 
                                     alt="Announcement image"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 60px; height: 60px; background: #e0e0e0; border-radius: 8px; align-items: center; justify-content: center;">📢</div>
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; background: #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">📢</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($announcement['title'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($announcement['published_at'] ?? $announcement['created_at'] ?? ''); ?></td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteAnnouncement(<?php echo $announcement['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Announcement Modal -->
<div id="addAnnouncementModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ Add New Announcement</h3>
        <form id="addAnnouncementForm" onsubmit="handleAddAnnouncement(event)">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-input" rows="5"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Published Date</label>
                <input type="datetime-local" name="published_at" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="image" id="announcement_image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('announcement_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Announcement</button>
                <button type="button" onclick="closeAddAnnouncementModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddAnnouncementModal() {
    document.getElementById('addAnnouncementModal').style.display = 'flex';
    document.getElementById('addAnnouncementForm').reset();
}

function closeAddAnnouncementModal() {
    document.getElementById('addAnnouncementModal').style.display = 'none';
}

function handleAddAnnouncement(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/announcements.php', {
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
            showToast(result.message || 'Announcement added successfully!', 'success');
            closeAddAnnouncementModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add announcement. Please try again.');
    });
}

function deleteAnnouncement(id) {
    if (!confirm('Are you sure you want to delete this announcement?')) return;
    
    fetch('api/content/announcements.php?id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            showToast(result.message || 'Announcement deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete announcement. Please try again.');
    });
}

document.getElementById('addAnnouncementModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddAnnouncementModal();
});
</script>
