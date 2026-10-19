<?php
/**
 * Navigation Menu Management Tab
 * Manage header navigation menu items
 */
$db = getDB();

// Get all navigation items (parent items first, then children)
$allNavItems = $db->fetchAll("SELECT * FROM navigation_items WHERE is_active = 1 ORDER BY parent_id ASC, order_index ASC");
$parentItems = [];
$childItems = [];

foreach ($allNavItems as $item) {
    if (empty($item['parent_id'])) {
        $parentItems[] = $item;
    } else {
        if (!isset($childItems[$item['parent_id']])) {
            $childItems[$item['parent_id']] = [];
        }
        $childItems[$item['parent_id']][] = $item;
    }
}
?>
<div class="content-card">
    <h2>🧭 Navigation Menu Management</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage your website's header navigation menu. Add, edit, or reorder menu items.</p>
    
    <!-- Add New Menu Item Button -->
    <div style="margin-bottom: 30px;">
        <button class="btn btn-primary" onclick="showAddNavModal()">➕ Add Menu Item</button>
    </div>
    
    <!-- Navigation Items List -->
    <div id="navItemsList">
        <?php if (empty($parentItems)): ?>
            <div style="text-align: center; padding: 40px; background: #f9fafb; border-radius: 12px; color: #6b7280;">
                <p style="font-size: 18px; margin-bottom: 10px;">No navigation items yet</p>
                <p>Click "Add Menu Item" to create your first menu item.</p>
            </div>
        <?php else: ?>
            <?php foreach ($parentItems as $parent): ?>
                <div class="nav-item-card" data-id="<?php echo $parent['id']; ?>" style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 20px;"><?php echo htmlspecialchars($parent['icon'] ?? '📄'); ?></span>
                                <div>
                                    <h4 style="margin: 0; font-size: 18px; color: #111827;">
                                        <?php echo htmlspecialchars($parent['label']); ?>
                                    </h4>
                                    <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">
                                        <?php echo htmlspecialchars($parent['url'] ?? 'No URL'); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Child Items -->
                            <?php if (isset($childItems[$parent['id']]) && !empty($childItems[$parent['id']])): ?>
                                <div style="margin-top: 15px; padding-left: 40px; border-left: 3px solid #e5e7eb;">
                                    <?php foreach ($childItems[$parent['id']] as $child): ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f9fafb; border-radius: 8px; margin-bottom: 8px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <span style="font-size: 16px;"><?php echo htmlspecialchars($child['icon'] ?? '→'); ?></span>
                                                <div>
                                                    <strong style="color: #374151;"><?php echo htmlspecialchars($child['label']); ?></strong>
                                                    <span style="color: #9ca3af; font-size: 12px; margin-left: 10px;">
                                                        <?php echo htmlspecialchars($child['url'] ?? ''); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div style="display: flex; gap: 10px;">
                                                <button class="btn btn-sm" onclick="editNavItem(<?php echo $child['id']; ?>)" style="padding: 6px 12px; font-size: 12px;">✏️ Edit</button>
                                                <button class="btn btn-sm" onclick="deleteNavItem(<?php echo $child['id']; ?>)" style="padding: 6px 12px; font-size: 12px; background: #ef4444; color: white;">🗑️ Delete</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <button class="btn btn-sm" onclick="addChildNavItem(<?php echo $parent['id']; ?>)" style="padding: 8px 16px; font-size: 14px; background: #10b981; color: white;">➕ Add Sub-item</button>
                            <button class="btn btn-sm" onclick="editNavItem(<?php echo $parent['id']; ?>)" style="padding: 8px 16px; font-size: 14px;">✏️ Edit</button>
                            <button class="btn btn-sm" onclick="deleteNavItem(<?php echo $parent['id']; ?>)" style="padding: 8px 16px; font-size: 14px; background: #ef4444; color: white;">🗑️ Delete</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Navigation Item Modal -->
<div id="navItemModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="padding: 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 24px; font-weight: bold; color: #333;" id="navModalTitle">Add Menu Item</h3>
            <button onclick="closeNavModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">✕</button>
        </div>
        
        <form id="navItemForm" onsubmit="saveNavItem(event)" style="padding: 20px;">
            <input type="hidden" id="navItemId" name="id">
            <input type="hidden" id="navItemParentId" name="parent_id">
            
            <div class="form-group">
                <label class="form-label">Menu Label *</label>
                <input type="text" id="navItemLabel" name="label" class="form-input" required placeholder="e.g., Home, About Us">
            </div>
            
            <div class="form-group">
                <label class="form-label">URL</label>
                <input type="text" id="navItemUrl" name="url" class="form-input" placeholder="e.g., index.php, about.php, #">
            </div>
            
            <div class="form-group">
                <label class="form-label">Icon (Emoji or Icon Name)</label>
                <input type="text" id="navItemIcon" name="icon" class="form-input" placeholder="e.g., 🏠, 📖, or leave empty">
            </div>
            
            <div class="form-group">
                <label class="form-label">Order Index</label>
                <input type="number" id="navItemOrder" name="order_index" class="form-input" value="0" min="0">
                <small style="color: #6b7280;">Lower numbers appear first</small>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="button" onclick="closeNavModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Menu Item</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentNavParentId = null;

function showAddNavModal() {
    currentNavParentId = null;
    document.getElementById('navItemId').value = '';
    document.getElementById('navItemParentId').value = '';
    document.getElementById('navItemLabel').value = '';
    document.getElementById('navItemUrl').value = '';
    document.getElementById('navItemIcon').value = '';
    document.getElementById('navItemOrder').value = '0';
    document.getElementById('navModalTitle').textContent = 'Add Menu Item';
    document.getElementById('navItemModal').style.display = 'flex';
}

function addChildNavItem(parentId) {
    currentNavParentId = parentId;
    document.getElementById('navItemId').value = '';
    document.getElementById('navItemParentId').value = parentId;
    document.getElementById('navItemLabel').value = '';
    document.getElementById('navItemUrl').value = '';
    document.getElementById('navItemIcon').value = '';
    document.getElementById('navItemOrder').value = '0';
    document.getElementById('navModalTitle').textContent = 'Add Sub-menu Item';
    document.getElementById('navItemModal').style.display = 'flex';
}

function editNavItem(id) {
    fetch(`api/content/navigation.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            
            document.getElementById('navItemId').value = data.id || '';
            document.getElementById('navItemParentId').value = data.parent_id || '';
            document.getElementById('navItemLabel').value = data.label || '';
            document.getElementById('navItemUrl').value = data.url || '';
            document.getElementById('navItemIcon').value = data.icon || '';
            document.getElementById('navItemOrder').value = data.order_index || 0;
            document.getElementById('navModalTitle').textContent = 'Edit Menu Item';
            document.getElementById('navItemModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load menu item');
        });
}

function deleteNavItem(id) {
    if (!confirm('Are you sure you want to delete this menu item? This will also delete all sub-items.')) {
        return;
    }
    
    fetch('api/content/navigation.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Menu item deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to delete menu item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to delete menu item', 'error');
    });
}

function saveNavItem(event) {
    event.preventDefault();
    
    const formData = {
        id: document.getElementById('navItemId').value || null,
        parent_id: document.getElementById('navItemParentId').value || null,
        label: document.getElementById('navItemLabel').value,
        url: document.getElementById('navItemUrl').value,
        icon: document.getElementById('navItemIcon').value,
        order_index: parseInt(document.getElementById('navItemOrder').value) || 0
    };
    
    fetch('api/content/navigation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Menu item saved successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to save menu item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to save menu item', 'error');
    });
}

function closeNavModal() {
    document.getElementById('navItemModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('navItemModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeNavModal();
    }
});
</script>
