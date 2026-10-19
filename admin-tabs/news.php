<?php
/**
 * News Tab - With Page Header Image and News Images
 */
$news = $db->fetchAll("SELECT * FROM news WHERE is_active = 1 ORDER BY created_at DESC LIMIT 20");
$newsSettings = $db->fetchOne("SELECT * FROM news_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>📰 News</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage news articles and page settings.</p>
    
    <!-- Page Header Image Section -->
    <div style="background: linear-gradient(135deg, #cffafe, #a5f3fc); padding: 25px; border-radius: 12px; border: 2px solid #0891b2; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <span>🖼️</span> News Page Header Image
        </h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
            This is the large background image displayed at the top of the News page.
        </p>
        
        <form method="POST" action="api/content/news-settings.php" id="newsHeaderForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 20px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($newsSettings['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <div style="flex-shrink: 0; position: relative;">
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Page header preview" 
                             style="width: 160px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #0891b2; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
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
                           id="news_hero_image" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($newsSettings['hero_image'] ?? ''); ?>" 
                           placeholder="Image URL will appear here after selection"
                           readonly>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" 
                                onclick="openMediaLibrary('news_hero_image')" 
                                class="btn btn-primary"
                                style="flex: 1; background: linear-gradient(135deg, #0891b2, #0e7490);">
                            🖼️ Select Image from Media Library
                        </button>
                        <?php if (!empty($newsSettings['hero_image'])): ?>
                            <button type="button" 
                                    onclick="document.getElementById('news_hero_image').value='';" 
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
                           value="<?php echo htmlspecialchars($newsSettings['title'] ?? 'News & Updates'); ?>"
                           placeholder="News & Updates">
                </div>
                <div class="form-group">
                    <label class="form-label">Page Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" 
                           value="<?php echo htmlspecialchars($newsSettings['subtitle'] ?? ''); ?>"
                           placeholder="Stay updated with our latest news">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 20px; background: linear-gradient(135deg, #0891b2, #0e7490);">
                💾 Save Page Header Settings
            </button>
        </form>
    </div>
    
    <!-- News List -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="color: #666;">Manage news articles.</p>
        <button class="btn btn-primary" onclick="showAddNewsModal()">➕ Add News</button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Author</th>
                <th>Published</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($news)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                        No news articles found. Click "Add News" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($news as $item): ?>
                    <tr>
                        <td>
                            <?php 
                            $newsImageUrl = normalizeImageUrl($item['image'] ?? '');
                            if (!empty($newsImageUrl)): ?>
                                <img src="<?php echo htmlspecialchars($newsImageUrl); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 60px; height: 60px; background: #e0e0e0; border-radius: 8px; align-items: center; justify-content: center; color: #999; font-size: 24px;">📰</div>
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; background: #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 24px;">📰</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['title'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['author'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['published_at'] ?? $item['created_at'] ?? ''); ?></td>
                        <td>
                            <button class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteNews(<?php echo $item['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add News Modal -->
<div id="addNewsModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ Add New News</h3>
        <form id="addNewsForm" onsubmit="handleAddNews(event)">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-input" rows="6"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Excerpt</label>
                <textarea name="excerpt" class="form-input" rows="2" placeholder="Leave empty to auto-generate from content"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Author</label>
                    <input type="text" name="author" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Published Date</label>
                    <input type="datetime-local" name="published_at" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <input type="text" name="image" id="news_image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('news_image')" 
                            class="btn btn-primary">🖼️ Select</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save News</button>
                <button type="button" onclick="closeAddNewsModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddNewsModal() {
    document.getElementById('addNewsModal').style.display = 'flex';
    document.getElementById('addNewsForm').reset();
}

function closeAddNewsModal() {
    document.getElementById('addNewsModal').style.display = 'none';
}

function handleAddNews(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/news.php', {
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
            showToast(result.message || 'News added successfully!', 'success');
            closeAddNewsModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add news. Please try again.');
    });
}

function deleteNews(id) {
    if (!confirm('Are you sure you want to delete this news article?')) return;
    
    fetch('api/content/news.php?id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            showToast(result.message || 'News deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete news. Please try again.');
    });
}

document.getElementById('addNewsModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddNewsModal();
});
</script>
