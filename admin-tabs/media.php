<?php
/**
 * Media Library Tab - Fully Functional
 */
$selectedSection = $_GET['section'] ?? null;

// POST requests are now handled in admin.php before HTML output

// Get all media sections
$mediaSections = $db->fetchAll("SELECT * FROM media_sections WHERE is_active = 1 ORDER BY name ASC");

// Get media - filtered by section if selected
if ($selectedSection) {
    $media = $db->fetchAll("SELECT m.*, ms.name as section_name, ms.color as section_color 
                            FROM media m 
                            LEFT JOIN media_sections ms ON m.section_id = ms.id 
                            WHERE m.section_id = ? 
                            ORDER BY m.created_at DESC 
                            LIMIT 50", [$selectedSection]);
} else {
    $media = $db->fetchAll("SELECT m.*, ms.name as section_name, ms.color as section_color 
                           FROM media m 
                           LEFT JOIN media_sections ms ON m.section_id = ms.id 
                           ORDER BY m.created_at DESC 
                           LIMIT 50");
}

// Calculate stats
$totalFiles = count($media);
$totalSize = 0;
foreach ($media as $item) {
    $totalSize += $item['file_size'] ?? 0;
}
$totalSizeMB = round($totalSize / 1024 / 1024, 2);
?>
<div class="content-card">
    <?php if (isset($_SESSION['media_success'])): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #10b981;">
            ✅ <?php echo htmlspecialchars($_SESSION['media_success']); unset($_SESSION['media_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['media_error'])): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ef4444;">
            ❌ <?php echo htmlspecialchars($_SESSION['media_error']); unset($_SESSION['media_error']); ?>
        </div>
    <?php endif; ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2>🖼️ Media Library</h2>
            <p style="color: #666; margin-top: 5px; font-size: 14px;">
                <?php echo $totalFiles; ?> file<?php echo $totalFiles != 1 ? 's' : ''; ?> uploaded • Total size: <?php echo $totalSizeMB; ?> MB
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary" onclick="showNewSectionModal()" style="background: linear-gradient(135deg, #16a34a, #059669);">
                ➕ New Section
            </button>
        </div>
    </div>
    
    <!-- Upload Section -->
    <div style="background: rgba(248, 249, 250, 0.7); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #e0e0e0;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <span style="font-weight: 600; color: #333;">Upload to Section:</span>
                <select id="uploadSection" style="padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 8px; min-width: 150px;">
                    <option value="">Select Section</option>
                    <?php foreach ($mediaSections as $section): ?>
                        <option value="<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <span id="sectionInfo" style="font-size: 12px; color: #666; display: none;"></span>
            </div>
            <div>
                <form id="uploadForm" method="POST" action="api/media/upload.php" enctype="multipart/form-data" style="display: inline;">
                    <input type="hidden" name="section_id" id="uploadSectionId" value="">
                    <input type="file" id="fileUpload" name="file[]" accept="image/*" multiple style="display: none;" onchange="handleFileUpload()">
                    <label for="fileUpload" class="btn btn-primary" style="background: linear-gradient(135deg, #2563eb, #4f46e5); cursor: pointer; display: inline-block;">
                        📤 Upload Media (Multiple Files)
                    </label>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Section Filter -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
            <span style="font-size: 16px;">🔍</span>
            <span style="font-weight: 600; color: #333; font-size: 14px;">Filter by Section:</span>
        </div>
        <div style="display: flex; flex-wrap: gap: 8px;">
            <a href="?tab=media" 
               style="padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 12px; transition: all 0.3s; <?php echo $selectedSection === null ? 'background: #2563eb; color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : 'background: rgba(255, 255, 255, 0.7); color: #666; border: 1px solid #e0e0e0;'; ?>">
                All Files
            </a>
            <?php foreach ($mediaSections as $section): 
                $isActive = $selectedSection == $section['id'];
                $sectionColor = $section['color'] ?? '#3B82F6';
            ?>
                <a href="?tab=media&section=<?php echo $section['id']; ?>" 
                   onclick="setUploadSection(<?php echo $section['id']; ?>); return true;"
                   style="padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 12px; transition: all 0.3s; background: <?php echo $sectionColor; ?>; color: white; display: inline-flex; align-items: center; gap: 6px; <?php echo $isActive ? 'box-shadow: 0 2px 4px rgba(0,0,0,0.2); opacity: 1;' : 'opacity: 0.9;'; ?>"
                   onmouseover="this.style.opacity='1'; this.style.transform='scale(1.05)'"
                   onmouseout="this.style.opacity='<?php echo $isActive ? '1' : '0.9'; ?>'; this.style.transform='scale(1)'">
                    <span style="font-size: 14px;">📁</span>
                    <span><?php echo htmlspecialchars($section['name']); ?></span>
                    <button onclick="event.preventDefault(); event.stopPropagation(); showEditSectionModal(<?php echo $section['id']; ?>, '<?php echo htmlspecialchars(addslashes($section['name'])); ?>', '<?php echo htmlspecialchars($section['color']); ?>')" 
                            style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 2px 4px; border-radius: 3px; cursor: pointer; font-size: 10px; margin-left: 4px; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;"
                            title="Edit Section">
                        ✏️
                    </button>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete section \'<?php echo htmlspecialchars(addslashes($section['name'])); ?>\'? This will only delete the section if it has no files.');">
                        <input type="hidden" name="action" value="delete_section">
                        <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                        <button type="submit" 
                                onclick="event.preventDefault(); event.stopPropagation(); if(confirm('Are you sure you want to delete section \'<?php echo htmlspecialchars(addslashes($section['name'])); ?>\'? This will only delete the section if it has no files.')) { this.parentElement.submit(); }"
                                style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 2px 4px; border-radius: 3px; cursor: pointer; font-size: 10px; margin-left: 2px; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;"
                                title="Delete Section">
                            🗑️
                        </button>
                    </form>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Media Grid -->
    <?php if (empty($media)): ?>
        <div style="text-align: center; padding: 40px 20px; color: #999; background: rgba(248, 249, 250, 0.7); backdrop-filter: blur(10px); border-radius: 12px; border: 2px dashed #e0e0e0;">
            <div style="font-size: 48px; margin-bottom: 15px;">🖼️</div>
            <div style="font-size: 18px; margin-bottom: 10px; font-weight: 600;">No media files found</div>
            <div style="font-size: 14px;">
                <?php if ($selectedSection): ?>
                    This section is empty. Upload files to this section.
                <?php else: ?>
                    Select a section and click "Upload Media" to add files.
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 8px;">
            <?php foreach ($media as $item): 
                // Use normalizeImageUrl helper for consistent path resolution
                $imageUrl = $item['file_path'] ?? $item['file_url'] ?? '';
                if (empty($imageUrl) && !empty($item['filename'])) {
                    $imageUrl = $item['filename'];
                }
                $imageUrl = normalizeImageUrl($imageUrl);
                
                $fileSize = $item['file_size'] ? round($item['file_size'] / 1024 / 1024, 2) . ' MB' : 'Unknown size';
                $sectionName = $item['section_name'] ?? null;
                $sectionColor = $item['section_color'] ?? '#3B82F6';
            ?>
                <div style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border-radius: 6px; padding: 4px; border: 1px solid #e0e0e0; transition: all 0.3s; position: relative;"
                     onmouseover="this.style.borderColor='#2563eb'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'; this.style.transform='translateY(-2px)'"
                     onmouseout="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none'; this.style.transform='translateY(0)'">
                    
                    <!-- Section Badge -->
                    <?php if ($sectionName): ?>
                        <div style="position: absolute; top: 3px; right: 3px; z-index: 10;">
                            <span style="padding: 1px 3px; background: <?php echo $sectionColor . '20'; ?>; color: <?php echo $sectionColor; ?>; border-radius: 2px; font-size: 7px; font-weight: 600; display: inline-flex; align-items: center; gap: 1px;">
                                <span style="font-size: 7px;">📁</span>
                                <?php echo htmlspecialchars(mb_substr($sectionName, 0, 5)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Image Preview -->
                    <div style="width: 100%; height: 100px; background: #f0f0f0; border-radius: 3px; margin-bottom: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative;">
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
                    
                    <!-- File Info -->
                    <div style="font-size: 9px; color: #666; margin-bottom: 2px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.2;" 
                         title="<?php echo htmlspecialchars($item['original_name'] ?? 'Unknown'); ?>">
                        <?php echo htmlspecialchars(mb_substr($item['original_name'] ?? 'Unknown', 0, 15)); ?>
                    </div>
                    <div style="font-size: 8px; color: #999; margin-bottom: 4px; line-height: 1.2;">
                        <?php echo $fileSize; ?>
                    </div>
                    
                    <!-- Action Buttons - Compact Row -->
                    <div style="display: flex; gap: 2px; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 2px;">
                            <button onclick="window.open('<?php echo htmlspecialchars($imageUrl); ?>', '_blank')" 
                                    title="View"
                                    style="width: 16px; height: 16px; padding: 0; font-size: 8px; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; line-height: 1;"
                                    onmouseover="this.style.transform='scale(1.15)'; this.style.boxShadow='0 1px 4px rgba(16,185,129,0.4)'"
                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                                👁️
                            </button>
                            <button onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($imageUrl); ?>').then(() => { const btn = this; btn.textContent='✓'; setTimeout(() => btn.textContent='📋', 1000); })" 
                                    title="Copy URL"
                                    style="width: 16px; height: 16px; padding: 0; font-size: 8px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; line-height: 1;"
                                    onmouseover="this.style.transform='scale(1.15)'; this.style.boxShadow='0 1px 4px rgba(37,99,235,0.4)'"
                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                                📋
                            </button>
                        </div>
                        <div style="display: flex; gap: 2px;">
                            <button onclick="showMoveMediaModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['original_name'] ?? ''); ?>', <?php echo $item['section_id'] ?? 'null'; ?>)" 
                                    title="Move to Section"
                                    style="width: 16px; height: 16px; padding: 0; font-size: 8px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; line-height: 1;"
                                    onmouseover="this.style.transform='scale(1.15)'; this.style.boxShadow='0 1px 4px rgba(245,158,11,0.4)'"
                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                                📦
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this file? This action cannot be undone.');">
                                <input type="hidden" name="action" value="delete_media">
                                <input type="hidden" name="media_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" 
                                        title="Delete"
                                        style="width: 16px; height: 16px; padding: 0; font-size: 8px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; line-height: 1;"
                                        onmouseover="this.style.transform='scale(1.15)'; this.style.boxShadow='0 1px 4px rgba(239,68,68,0.4)'"
                                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- New Section Modal -->
<div id="newSectionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">➕ New Section</h3>
        <form method="POST" action="?tab=media">
            <input type="hidden" name="action" value="create_section">
            <div class="form-group">
                <label class="form-label">Section Name</label>
                <input type="text" name="name" class="form-input" required placeholder="e.g., Branding, Hero, Activities">
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <input type="color" name="color" value="#3B82F6" style="width: 100%; height: 50px; border-radius: 8px; border: 2px solid #e0e0e0; cursor: pointer;">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Create Section</button>
                <button type="button" onclick="closeNewSectionModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Section Modal -->
<div id="editSectionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">✏️ Edit Section</h3>
        <form method="POST" action="?tab=media">
            <input type="hidden" name="action" value="edit_section">
            <input type="hidden" name="section_id" id="editSectionId">
            <div class="form-group">
                <label class="form-label">Section Name</label>
                <input type="text" name="name" id="editSectionName" class="form-input" required placeholder="e.g., Branding, Hero, Activities">
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <input type="color" name="color" id="editSectionColor" value="#3B82F6" style="width: 100%; height: 50px; border-radius: 8px; border: 2px solid #e0e0e0; cursor: pointer;">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Update Section</button>
                <button type="button" onclick="closeEditSectionModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Move Media Modal -->
<div id="moveMediaModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 10px;">📦 Move File</h3>
        <p style="color: #666; margin-bottom: 20px; font-size: 14px;" id="moveMediaFileName"></p>
        <form method="POST" action="?tab=media">
            <input type="hidden" name="action" value="move_media">
            <input type="hidden" name="media_id" id="moveMediaId">
            <div class="form-group">
                <label class="form-label">Move to Section</label>
                <select name="new_section_id" id="moveMediaSection" class="form-input" required>
                    <option value="">-- No Section (Uncategorized) --</option>
                    <?php foreach ($mediaSections as $section): ?>
                        <option value="<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Move File</button>
                <button type="button" onclick="closeMoveMediaModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showNewSectionModal() {
    document.getElementById('newSectionModal').style.display = 'flex';
}
function closeNewSectionModal() {
    document.getElementById('newSectionModal').style.display = 'none';
}
function setUploadSection(sectionId) {
    const uploadSection = document.getElementById('uploadSection');
    if (uploadSection) {
        uploadSection.value = sectionId;
        uploadSection.dispatchEvent(new Event('change'));
    }
}

function showEditSectionModal(id, name, color) {
    document.getElementById('editSectionId').value = id;
    document.getElementById('editSectionName').value = name;
    document.getElementById('editSectionColor').value = color;
    document.getElementById('editSectionModal').style.display = 'flex';
}

function closeEditSectionModal() {
    document.getElementById('editSectionModal').style.display = 'none';
}

function showMoveMediaModal(mediaId, fileName, currentSectionId) {
    document.getElementById('moveMediaId').value = mediaId;
    document.getElementById('moveMediaFileName').textContent = 'File: ' + fileName;
    const sectionSelect = document.getElementById('moveMediaSection');
    sectionSelect.value = currentSectionId || '';
    document.getElementById('moveMediaModal').style.display = 'flex';
}

function closeMoveMediaModal() {
    document.getElementById('moveMediaModal').style.display = 'none';
}
function handleFileUpload() {
    const fileInput = document.getElementById('fileUpload');
    const sectionSelect = document.getElementById('uploadSection');
    const sectionIdInput = document.getElementById('uploadSectionId');
    
    // Check if section is selected
    if (!sectionSelect.value) {
        alert('Please select a section first!');
        fileInput.value = '';
        return;
    }
    
    // Check if files are selected
    const files = fileInput.files;
    if (!files || files.length === 0) {
        alert('Please select at least one file to upload!');
        return;
    }
    
    // Validate file types before upload
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const invalidFiles = [];
    for (let i = 0; i < files.length; i++) {
        if (!allowedTypes.includes(files[i].type)) {
            invalidFiles.push(files[i].name + ' (' + files[i].type + ')');
        }
        if (files[i].size > 50 * 1024 * 1024) {
            invalidFiles.push(files[i].name + ' (file too large: ' + Math.round(files[i].size / 1024 / 1024) + 'MB)');
        }
    }
    if (invalidFiles.length > 0) {
        alert('Some files are invalid:\n' + invalidFiles.join('\n') + '\n\nPlease select valid image files (JPEG, PNG, GIF, WEBP) under 50MB.');
        fileInput.value = '';
        return;
    }
    
    sectionIdInput.value = sectionSelect.value;
    
    // Upload files one by one
    let uploadPromises = [];
    for (let i = 0; i < files.length; i++) {
        const formData = new FormData();
        // Use 'file' (not 'file[]') to match PHP's expected format
        formData.append('file', files[i]);
        formData.append('section_id', sectionSelect.value);
        
        uploadPromises.push(
            fetch('api/media/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    // Include detailed errors if available
                    let errorMsg = data.error || 'Upload failed';
                    if (data.errors && data.errors.length > 0) {
                        errorMsg += '\n\nDetails:\n' + data.errors.join('\n');
                    }
                    throw new Error(errorMsg);
                }
                return data;
            })
            .then(data => {
                if (!data.success) {
                    let errorMsg = data.error || 'Upload failed';
                    if (data.errors && data.errors.length > 0) {
                        errorMsg += '\n\nDetails:\n' + data.errors.join('\n');
                    }
                    throw new Error(errorMsg);
                }
                return data;
            })
        );
    }
    
    // Show loading message
    const uploadLabel = document.querySelector('label[for="fileUpload"]');
    const originalText = uploadLabel.textContent;
    uploadLabel.textContent = '⏳ Uploading...';
    uploadLabel.style.pointerEvents = 'none';
    uploadLabel.style.opacity = '0.7';
    
    Promise.all(uploadPromises)
        .then(results => {
            let successCount = 0;
            let errorCount = 0;
            let allErrors = [];
            
            results.forEach(result => {
                if (result.success) {
                    successCount += (result.uploaded || []).length;
                }
                if (result.errors && result.errors.length > 0) {
                    errorCount += result.errors.length;
                    allErrors = allErrors.concat(result.errors);
                }
            });
            
            let message = '';
            if (successCount > 0) {
                message = `Successfully uploaded ${successCount} file(s)!`;
                if (errorCount > 0) {
                    message += `\n\n${errorCount} file(s) failed:\n${allErrors.join('\n')}`;
                }
            } else {
                message = `Upload failed for all files:\n${allErrors.join('\n')}`;
            }
            
            alert(message);
            if (successCount > 0) {
                location.reload();
            }
        })
        .catch(error => {
            let errorMessage = 'Upload error: ' + error.message;
            // Show error in alert and also log to console for debugging
            console.error('Upload error details:', error);
            alert(errorMessage);
            uploadLabel.textContent = originalText;
            uploadLabel.style.pointerEvents = 'auto';
            uploadLabel.style.opacity = '1';
        })
        .finally(() => {
            fileInput.value = '';
        });
}

// Set upload section from URL parameter on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const sectionParam = urlParams.get('section');
    const uploadSection = document.getElementById('uploadSection');
    
    if (sectionParam && uploadSection) {
        uploadSection.value = sectionParam;
        // Trigger change event to update UI
        uploadSection.dispatchEvent(new Event('change'));
    }
});

document.getElementById('uploadSection')?.addEventListener('change', function() {
    const sectionInfo = document.getElementById('sectionInfo');
    if (this.value) {
        const sectionName = this.options[this.selectedIndex].text;
        sectionInfo.textContent = 'Files will be uploaded to: ' + sectionName;
        sectionInfo.style.display = 'inline';
    } else {
        sectionInfo.style.display = 'none';
    }
});

// Close modals when clicking outside
document.getElementById('newSectionModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeNewSectionModal();
    }
});

document.getElementById('editSectionModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditSectionModal();
    }
});

document.getElementById('moveMediaModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeMoveMediaModal();
    }
});
</script>
