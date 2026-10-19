<?php
/**
 * Programs Tab
 */
$programs = $db->fetchAll("SELECT * FROM programs WHERE is_active = 1 ORDER BY created_at DESC");
?>
<div class="content-card">
    <h2>📚 Programs</h2>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage school programs.</p>
        <button class="btn btn-primary" onclick="showAddProgramModal()">➕ Add Program</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($programs)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                        No programs found. Click "Add Program" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($programs as $program): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($program['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars(substr($program['description'] ?? '', 0, 100)) . '...'; ?></td>
                        <td><?php echo htmlspecialchars($program['duration'] ?? ''); ?></td>
                        <td>
                            <span style="padding: 4px 8px; background: <?php echo ($program['is_active'] ?? 1) ? '#28a745' : '#dc3545'; ?>; color: white; border-radius: 4px; font-size: 11px;">
                                <?php echo ($program['is_active'] ?? 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteProgram(<?php echo $program['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Program Modal -->
<div id="addProgramModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ Add New Program</h3>
        <form id="addProgramForm" onsubmit="handleAddProgram(event)">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Duration</label>
                <input type="text" name="duration" class="form-input" placeholder="e.g., 4 years">
            </div>
            <div class="form-group">
                <label class="form-label">Requirements</label>
                <textarea name="requirements" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="image" id="program_image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('program_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Program</button>
                <button type="button" onclick="closeAddProgramModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddProgramModal() {
    document.getElementById('addProgramModal').style.display = 'flex';
    document.getElementById('addProgramForm').reset();
}

function closeAddProgramModal() {
    document.getElementById('addProgramModal').style.display = 'none';
}

function handleAddProgram(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/programs.php', {
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
            showToast(result.message || 'Program added successfully!', 'success');
            closeAddProgramModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        showToast('Save error: ' + error.message, 'error');
    });
}

function deleteProgram(id) {
    if (!confirm('Are you sure you want to delete this program?')) return;
    
    fetch('api/content/programs.php?id=' + id, {
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
            showToast(result.message || 'Program deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Delete error: ' + error.message, 'error');
    });
}

document.getElementById('addProgramModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddProgramModal();
});
</script>
