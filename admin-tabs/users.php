<?php
/**
 * Users Management Tab
 */
$users = $db->fetchAll("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<div class="content-card">
    <h2>👥 User Management</h2>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage system users.</p>
        <button class="btn btn-primary" onclick="showAddUserModal()">➕ Add User</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                        No users found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                        <td>
                            <span style="padding: 4px 8px; background: <?php echo $user['role'] === 'admin' ? '#dc3545' : '#28a745'; ?>; color: white; border-radius: 4px; font-size: 11px;">
                                <?php echo strtoupper($user['role'] ?? 'user'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['created_at'] ?? ''); ?></td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</button>
                            <?php if ($user['id'] != $currentUser['id']): ?>
                                <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div id="addUserModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ Add New User</h3>
        <form id="addUserForm" onsubmit="handleAddUser(event)">
            <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-input" required minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-input">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save User</button>
                <button type="button" onclick="closeAddUserModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddUserModal() {
    document.getElementById('addUserModal').style.display = 'flex';
    document.getElementById('addUserForm').reset();
}

function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
}

function handleAddUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/users.php', {
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
            showToast(result.message || 'User added successfully!', 'success');
            closeAddUserModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add user. Please try again.');
    });
}

function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    fetch('api/content/users.php?id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            showToast(result.message || 'User deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete user. Please try again.');
    });
}

document.getElementById('addUserModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddUserModal();
});
</script>
