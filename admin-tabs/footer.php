<?php
/**
 * Footer Tab - Enhanced Footer Management
 */
$footer = $db->fetchOne("SELECT * FROM footer_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];

// Get footer quick links from database
$footerQuickLinks = [];
try {
    $footerQuickLinks = $db->fetchAll("SELECT * FROM footer_quick_links WHERE is_active = 1 ORDER BY order_index ASC");
} catch (Exception $e) {
    // Table might not exist
}

// Get footer hours
$footerHours = [];
try {
    $footerHours = $db->fetchAll("SELECT * FROM footer_hours WHERE is_active = 1 ORDER BY order_index ASC");
} catch (Exception $e) {
    // Table might not exist
}

// Decode JSON fields
$socialLinks = [];
$quickLinks = [];
if ($footer) {
    if (!empty($footer['social_links'])) {
        $socialLinks = json_decode($footer['social_links'], true) ?: [];
    }
    if (!empty($footer['quick_links'])) {
        $quickLinks = json_decode($footer['quick_links'], true) ?: [];
    }
}
?>
<div class="content-card">
    <h2>⬇️ Footer Content Management</h2>
    <p style="margin-bottom: 30px; color: #666;">Customize all footer content including links, hours, and social media.</p>
    
    <!-- Tabs for different footer sections -->
    <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #e5e7eb; flex-wrap: wrap;">
        <button class="section-tab-btn active" onclick="showFooterSection('basic')">Basic Content</button>
        <button class="section-tab-btn" onclick="showFooterSection('quick-links')">Quick Links</button>
        <button class="section-tab-btn" onclick="showFooterSection('hours')">Hours of Operation</button>
        <button class="section-tab-btn" onclick="showFooterSection('social')">Social Media</button>
    </div>
    
    <!-- Basic Content Section -->
    <div id="footer-basic-section" class="footer-section-content">
        <form method="POST" action="api/content/footer.php" id="footerForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div class="form-group">
                <label class="form-label">Copyright Text</label>
                <input type="text" name="copyright_text" class="form-input" 
                       value="<?php echo htmlspecialchars($footer['copyright_text'] ?? ''); ?>"
                       placeholder="© 2024 Excellence Academy. All rights reserved.">
            </div>
            
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
        </form>
    </div>
    
    <!-- Quick Links Section -->
    <div id="footer-quick-links-section" class="footer-section-content" style="display: none;">
        <div style="margin-bottom: 20px;">
            <button class="btn btn-primary" onclick="showAddQuickLinkModal()">➕ Add Quick Link</button>
        </div>
        
        <div id="quickLinksList">
            <?php if (empty($footerQuickLinks)): ?>
                <div style="text-align: center; padding: 40px; background: #f9fafb; border-radius: 12px; color: #6b7280;">
                    <p>No quick links yet. Click "Add Quick Link" to create one.</p>
                </div>
            <?php else: ?>
                <?php foreach ($footerQuickLinks as $link): ?>
                    <div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo htmlspecialchars($link['label']); ?></strong>
                            <span style="color: #6b7280; margin-left: 10px;"><?php echo htmlspecialchars($link['url'] ?? ''); ?></span>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn btn-sm" onclick="editQuickLink(<?php echo $link['id']; ?>)" style="padding: 6px 12px; font-size: 12px;">✏️ Edit</button>
                            <button class="btn btn-sm" onclick="deleteQuickLink(<?php echo $link['id']; ?>)" style="padding: 6px 12px; font-size: 12px; background: #ef4444; color: white;">🗑️ Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Hours Section -->
    <div id="footer-hours-section" class="footer-section-content" style="display: none;">
        <div style="margin-bottom: 20px;">
            <button class="btn btn-primary" onclick="showAddHoursModal()">➕ Add Hours Entry</button>
        </div>
        
        <div id="hoursList">
            <?php if (empty($footerHours)): ?>
                <div style="text-align: center; padding: 40px; background: #f9fafb; border-radius: 12px; color: #6b7280;">
                    <p>No hours entries yet. Click "Add Hours Entry" to create one.</p>
                </div>
            <?php else: ?>
                <?php foreach ($footerHours as $hour): ?>
                    <div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo htmlspecialchars($hour['category']); ?></strong>
                            <span style="color: #6b7280; margin-left: 10px;">
                                <?php echo htmlspecialchars($hour['day_range'] ?? ''); ?>: <?php echo htmlspecialchars($hour['hours'] ?? ''); ?>
                            </span>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn btn-sm" onclick="editHours(<?php echo $hour['id']; ?>)" style="padding: 6px 12px; font-size: 12px;">✏️ Edit</button>
                            <button class="btn btn-sm" onclick="deleteHours(<?php echo $hour['id']; ?>)" style="padding: 6px 12px; font-size: 12px; background: #ef4444; color: white;">🗑️ Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Social Media Section -->
    <div id="footer-social-section" class="footer-section-content" style="display: none;">
        <p style="margin-bottom: 20px; color: #666;">Manage social media links in the <a href="?tab=social-media" style="color: #2563eb; text-decoration: underline;">Social Media</a> tab.</p>
    </div>
</div>

<script>
function showFooterSection(section) {
    // Hide all sections
    document.querySelectorAll('.footer-section-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.section-tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show selected section
    document.getElementById('footer-' + section + '-section').style.display = 'block';
    event.target.classList.add('active');
}

function showAddQuickLinkModal() {
    // Simple prompt for now - can be enhanced with a modal
    const label = prompt('Enter link label:');
    if (!label) return;
    const url = prompt('Enter link URL:');
    if (!url) return;
    
    fetch('api/content/footer-links.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ label, url, section: 'quick-links', order_index: 0 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Quick link added successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to add link', 'error');
        }
    });
}

function editQuickLink(id) {
    const label = prompt('Enter new label:');
    if (!label) return;
    const url = prompt('Enter new URL:');
    if (!url) return;
    
    fetch('api/content/footer-links.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, label, url })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Quick link updated successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to update link', 'error');
        }
    });
}

function deleteQuickLink(id) {
    if (!confirm('Delete this quick link?')) return;
    
    fetch('api/content/footer-links.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Quick link deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to delete link', 'error');
        }
    });
}

function showAddHoursModal() {
    const category = prompt('Enter category (e.g., Students, Staff):');
    if (!category) return;
    const dayRange = prompt('Enter day range (e.g., Monday - Friday):');
    if (!dayRange) return;
    const hours = prompt('Enter hours (e.g., 7:00 AM – 5:00 PM):');
    if (!hours) return;
    
    fetch('api/content/footer-hours.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category, day_range: dayRange, hours, order_index: 0 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Hours entry added successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to add hours', 'error');
        }
    });
}

function editHours(id) {
    const category = prompt('Enter category:');
    if (!category) return;
    const dayRange = prompt('Enter day range:');
    if (!dayRange) return;
    const hours = prompt('Enter hours:');
    if (!hours) return;
    
    fetch('api/content/footer-hours.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, category, day_range: dayRange, hours })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Hours entry updated successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to update hours', 'error');
        }
    });
}

function deleteHours(id) {
    if (!confirm('Delete this hours entry?')) return;
    
    fetch('api/content/footer-hours.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Hours entry deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to delete hours', 'error');
        }
    });
}
</script>

<style>
.section-tab-btn {
    padding: 10px 20px;
    background: #f3f4f6;
    border: none;
    border-radius: 8px 8px 0 0;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
}

.section-tab-btn:hover {
    background: #e5e7eb;
}

.section-tab-btn.active {
    background: white;
    border-bottom: 2px solid #2563eb;
    color: #2563eb;
}

.footer-section-content {
    background: white;
    padding: 20px;
    border-radius: 12px;
}
</style>
