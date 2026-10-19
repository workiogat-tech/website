<?php
/**
 * Gallery Tab - Manage Gallery Images
 */
// Get gallery section or create it
$gallerySection = $db->fetchOne("SELECT * FROM media_sections WHERE name = 'Gallery' AND is_active = 1 LIMIT 1");
if (!$gallerySection) {
    // Create Gallery section if it doesn't exist
    try {
        $gallerySectionId = $db->insert(
            "INSERT INTO media_sections (name, description, color, is_active, created_at) VALUES (?, ?, ?, 1, NOW())",
            ['Gallery', 'School gallery images', '#10b981']
        );
        $gallerySection = $db->fetchOne("SELECT * FROM media_sections WHERE id = ?", [$gallerySectionId]);
    } catch (Exception $e) {
        error_log("Error creating Gallery section: " . $e->getMessage());
    }
}

$gallerySectionId = $gallerySection['id'] ?? null;
$galleryImages = [];
if ($gallerySectionId) {
    $galleryImages = $db->fetchAll(
        "SELECT m.* FROM media m WHERE m.section_id = ? ORDER BY m.created_at DESC",
        [$gallerySectionId]
    );
}

// Get gallery settings if table exists
$gallerySettings = [];
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'gallery_settings'");
    if ($tableExists) {
        $gallerySettings = $db->fetchOne("SELECT * FROM gallery_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
    }
} catch (Exception $e) {
    // Table doesn't exist, that's okay
}
?>
<div class="content-card">
    <h2>🖼️ School Gallery</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage gallery images displayed on the gallery page.</p>
    
    <!-- Hero Image Section -->
    <div style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); padding: 25px; border-radius: 12px; border: 2px solid #3b82f6; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <span>🖼️</span> Gallery Page Hero Image
        </h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
            This is the large background image displayed at the top of the Gallery page.
        </p>
        
        <form method="POST" action="api/content/gallery-settings.php" id="galleryHeroForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 20px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($gallerySettings['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <div style="flex-shrink: 0; position: relative;">
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview" 
                             style="width: 160px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #3b82f6; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
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
                           id="gallery_hero_image" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($gallerySettings['hero_image'] ?? ''); ?>" 
                           placeholder="Image URL will appear here after selection"
                           readonly>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" 
                                onclick="openMediaLibrary('gallery_hero_image')" 
                                class="btn btn-primary"
                                style="flex: 1; background: linear-gradient(135deg, #3b82f6, #2563eb);">
                            🖼️ Select Hero Image
                        </button>
                        <?php if (!empty($gallerySettings['hero_image'])): ?>
                            <button type="button" 
                                    onclick="document.getElementById('gallery_hero_image').value='';" 
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
                           value="<?php echo htmlspecialchars($gallerySettings['title'] ?? 'School Gallery'); ?>"
                           placeholder="School Gallery">
                </div>
                <div class="form-group">
                    <label class="form-label">Page Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" 
                           value="<?php echo htmlspecialchars($gallerySettings['subtitle'] ?? 'Photos and Memories'); ?>"
                           placeholder="Photos and Memories">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 15px; background: linear-gradient(135deg, #3b82f6, #2563eb);">
                💾 Save Hero Image Settings
            </button>
        </form>
    </div>
    
    <!-- Upload Section -->
    <div style="background: rgba(248, 249, 250, 0.7); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #e0e0e0;">
        <h3 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">📤 Upload Gallery Images</h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
            Upload images to the Gallery section. These will be displayed on the gallery page.
        </p>
        <div>
            <form id="galleryUploadForm" method="POST" action="api/media/upload.php" enctype="multipart/form-data" style="display: inline;">
                <input type="hidden" name="section_id" value="<?php echo $gallerySectionId ?? ''; ?>">
                <input type="file" id="galleryFileUpload" name="file[]" accept="image/*" multiple style="display: none;" onchange="handleGalleryUpload()">
                <label for="galleryFileUpload" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); cursor: pointer; display: inline-block;">
                    📤 Upload Gallery Images
                </label>
            </form>
        </div>
    </div>
    
    <!-- Gallery Images Grid -->
    <div>
        <h3 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 20px;">Gallery Images (<?php echo count($galleryImages); ?>)</h3>
        <?php if (empty($galleryImages)): ?>
            <div style="text-align: center; padding: 40px 20px; color: #999; background: rgba(248, 249, 250, 0.7); backdrop-filter: blur(10px); border-radius: 12px; border: 2px dashed #e0e0e0;">
                <div style="font-size: 48px; margin-bottom: 15px;">🖼️</div>
                <div style="font-size: 18px; margin-bottom: 10px; font-weight: 600;">No gallery images found</div>
                <div style="font-size: 14px;">
                    Upload images using the "Upload Gallery Images" button above.
                </div>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                <?php 
                foreach ($galleryImages as $item): 
                    $imageUrl = normalizeImageUrl($item['file_path'] ?? $item['file_url'] ?? '');
                    if (empty($imageUrl) && !empty($item['filename'])) {
                        $imageUrl = normalizeImageUrl($item['filename']);
                    }
                ?>
                    <div style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border-radius: 8px; padding: 8px; border: 2px solid #e0e0e0; transition: all 0.3s; position: relative;">
                        <div style="width: 100%; height: 150px; background: #f0f0f0; border-radius: 4px; margin-bottom: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative;">
                            <?php if ($imageUrl): ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                     alt="<?php echo htmlspecialchars($item['original_name'] ?? ''); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
                                     onclick="window.open('<?php echo htmlspecialchars($imageUrl); ?>', '_blank')"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 100%; height: 100%; background: #f0f0f0; align-items: center; justify-content: center; position: absolute; top: 0; left: 0;">
                                    <span style="color: #999; font-size: 24px;">🖼️</span>
                                </div>
                            <?php else: ?>
                                <span style="color: #999; font-size: 24px;">🖼️</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 10px; color: #666; margin-bottom: 4px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                             title="<?php echo htmlspecialchars($item['original_name'] ?? 'Unknown'); ?>">
                            <?php echo htmlspecialchars(mb_substr($item['original_name'] ?? 'Unknown', 0, 20)); ?>
                        </div>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?');">
                            <input type="hidden" name="action" value="delete_media">
                            <input type="hidden" name="media_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" 
                                    title="Delete"
                                    style="width: 100%; padding: 6px; font-size: 11px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 4px; cursor: pointer;">
                                🗑️ Delete
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function handleGalleryUpload() {
    const fileInput = document.getElementById('galleryFileUpload');
    const form = document.getElementById('galleryUploadForm');
    const files = fileInput.files;
    
    if (!files || files.length === 0) {
        alert('Please select at least one file to upload!');
        return;
    }
    
    const formData = new FormData(form);
    
    // Upload files one by one
    let uploadPromises = [];
    for (let i = 0; i < files.length; i++) {
        const singleFormData = new FormData();
        singleFormData.append('file', files[i]);
        singleFormData.append('section_id', form.querySelector('input[name="section_id"]').value);
        
        uploadPromises.push(
            fetch('api/media/upload.php', {
                method: 'POST',
                body: singleFormData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Upload failed');
                }
                return data;
            })
        );
    }
    
    const uploadLabel = document.querySelector('label[for="galleryFileUpload"]');
    const originalText = uploadLabel.textContent;
    uploadLabel.textContent = '⏳ Uploading...';
    uploadLabel.style.pointerEvents = 'none';
    uploadLabel.style.opacity = '0.7';
    
    Promise.all(uploadPromises)
        .then(results => {
            let successCount = 0;
            results.forEach(result => {
                if (result.success) {
                    successCount += (result.uploaded || []).length;
                }
            });
            
            alert(`Successfully uploaded ${successCount} image(s)!`);
            location.reload();
        })
        .catch(error => {
            alert('Upload error: ' + error.message);
            uploadLabel.textContent = originalText;
            uploadLabel.style.pointerEvents = 'auto';
            uploadLabel.style.opacity = '1';
        })
        .finally(() => {
            fileInput.value = '';
        });
}
</script>
