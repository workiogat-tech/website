<?php
/**
 * School Background Tab
 */
$background = $db->fetchOne("SELECT * FROM school_background_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
?>
<div class="content-card">
    <h2>🏛️ School Background</h2>
    <p style="margin-bottom: 30px; color: #666;">Manage school background and history content.</p>
    
    <form method="POST" action="api/content/school-background.php" id="backgroundForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <!-- Hero Section -->
        <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); padding: 25px; border-radius: 12px; border: 2px solid #0ea5e9; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">🎯 Page Header (Hero Section)</h3>
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="title" class="form-input" 
                       value="<?php echo htmlspecialchars($background['title'] ?? 'School Background'); ?>"
                       placeholder="School Background">
                <small style="color: #6b7280; display: block; margin-top: 5px;">This appears as the main title in the hero section</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Caption (Subtitle)</label>
                <input type="text" name="subtitle" class="form-input" 
                       value="<?php echo htmlspecialchars($background['subtitle'] ?? 'Know Us Better'); ?>"
                       placeholder="Know Us Better">
                <small style="color: #6b7280; display: block; margin-top: 5px;">This appears as the caption above the title in the hero section</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Image (for Page Header)</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears in the header/hero section of the School Background page</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $heroImageUrl = normalizeImageUrl($background['hero_image'] ?? '');
                    if (!empty($heroImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview"
                             style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="hero_image" id="background_hero_image" class="form-input" 
                               value="<?php echo htmlspecialchars($background['hero_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('background_hero_image')" 
                            class="btn btn-primary">🖼️ Select Image</button>
                </div>
            </div>
        </div>
        
        <!-- Our History Section -->
        <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); padding: 25px; border-radius: 12px; border: 2px solid #0ea5e9; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">📜 Section 1: Our History (Text Left, Image Right)</h3>
            <div class="form-group">
                <label class="form-label">History Section Title</label>
                <input type="text" name="history_title" class="form-input" 
                       value="<?php 
                       $historySection = !empty($background['history_section']) ? (is_string($background['history_section']) ? json_decode($background['history_section'], true) : $background['history_section']) : [];
                       echo htmlspecialchars($historySection['title'] ?? 'Our History'); 
                       ?>"
                       placeholder="Our History">
            </div>
            <div class="form-group">
                <label class="form-label">History Content</label>
                <textarea name="history_content" class="form-input" rows="6" 
                          placeholder="SJS was founded in 1985 and is proudly celebrating 40 years of excellence in 2025..."><?php 
                $historySection = !empty($background['history_section']) ? (is_string($background['history_section']) ? json_decode($background['history_section'], true) : $background['history_section']) : [];
                echo htmlspecialchars($historySection['content'] ?? 'SJS was founded in 1985 and is proudly celebrating 40 years of excellence in 2025. The school sits on land donated by the Mukono Diocese Church of Uganda, under the leadership of the Late Archbishop Livingstone Mpalanyi Nkoyooyo, the first Bishop of Mukono.

It began with just seven students at SJS Boarding Primary School, guided by Headteacher Mr. Mugwano Noah. The first Headteacher, Late Mr. Batte Abraham, worked alongside the first Board Chairperson, Rev. Canon Yusufu Tubwomwe, to lay the school\'s strong foundation.'); 
                ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">History Section Image</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears on the RIGHT side of the "Our History" section</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $historyImageUrl = normalizeImageUrl($background['history_image'] ?? '');
                    if (!empty($historyImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($historyImageUrl); ?>" 
                             alt="History image preview"
                             style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="history_image" id="background_history_image" class="form-input" 
                               value="<?php echo htmlspecialchars($background['history_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('background_history_image')" 
                            class="btn btn-primary">🖼️ Select Image</button>
                </div>
            </div>
        </div>
        
        <!-- Main Content Section -->
        <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 25px; border-radius: 12px; border: 2px solid #f59e0b; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">📖 Section 2: Main Content (Image Left, Text Right)</h3>
            <div class="form-group">
                <label class="form-label">Content Section Title</label>
                <input type="text" name="content_title" id="content_title" class="form-input" 
                       value="<?php echo htmlspecialchars($background['content_title'] ?? $background['subtitle'] ?? 'our history and foundation'); ?>"
                       placeholder="our history and foundation">
                <small style="color: #6b7280; display: block; margin-top: 5px;">This is the title for the main content section (different from hero subtitle)</small>
            </div>
            <div class="form-group">
                <label class="form-label">Content Text</label>
                <textarea name="content" class="form-input" rows="6" 
                          placeholder="SJS was established with a clear mission to provide quality, holistic education..."><?php echo htmlspecialchars($background['content'] ?? 'SJS was established with a clear mission to provide quality, holistic education that nurtures academic excellence, discipline, and moral values. From its humble beginnings, the school has grown through commitment, student support, and a passion for shaping young minds. Today, SJS stands as a trusted institution dedicated to developing confident, responsible, and well-rounded learners.'); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Content Section Image</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears on the LEFT side of the main content section</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $contentImageUrl = normalizeImageUrl($background['content_image'] ?? '');
                    if (!empty($contentImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($contentImageUrl); ?>" 
                             alt="Content image preview"
                             style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="content_image" id="background_content_image" class="form-input" 
                               value="<?php echo htmlspecialchars($background['content_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('background_content_image')" 
                            class="btn btn-primary">🖼️ Select Image</button>
                </div>
            </div>
        </div>
        
        <!-- Video Section (Optional) -->
        <div style="background: #f9fafb; padding: 25px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 30px;">
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 20px;">🎥 Video Section (Optional)</h3>
            <div class="form-group">
                <label class="form-label">Video URL</label>
                <input type="text" name="video_url" class="form-input" 
                       value="<?php echo htmlspecialchars($background['video_url'] ?? ''); ?>"
                       placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://vimeo.com/VIDEO_ID">
                <small style="color: #6b7280; display: block; margin-top: 5px; line-height: 1.6;">
                    <strong>⚠️ Important:</strong> Use a specific <strong>video URL</strong>, not a channel URL.<br>
                    <strong>YouTube:</strong> <code>https://www.youtube.com/watch?v=VIDEO_ID</code> or <code>https://youtu.be/VIDEO_ID</code><br>
                    <strong>Vimeo:</strong> <code>https://vimeo.com/VIDEO_ID</code><br>
                    <strong style="color: #dc2626;">❌ Don't use:</strong> Channel URLs like <code>@channelname</code> - these won't work!
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Video Embed Code (Alternative)</label>
                <textarea name="video_embed_code" class="form-input" rows="4" 
                          placeholder='<iframe src="https://www.youtube.com/embed/VIDEO_ID" frameborder="0" allowfullscreen></iframe>'><?php echo htmlspecialchars($background['video_embed_code'] ?? ''); ?></textarea>
                <small style="color: #6b7280; display: block; margin-top: 5px; line-height: 1.6;">
                    <strong>Alternative:</strong> Paste full HTML embed code (iframe tag) if you prefer custom embed instead of URL.<br>
                    <strong>Example:</strong> <code>&lt;iframe src="https://www.youtube.com/embed/VIDEO_ID" frameborder="0" allowfullscreen&gt;&lt;/iframe&gt;</code>
                </small>
            </div>
        </div>
        
        <!-- Key Milestones Section -->
        <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 25px; border-radius: 12px; border: 2px solid #f59e0b; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: #333; margin: 0;">🏆 Key Milestones</h3>
                <button type="button" onclick="addMilestone()" class="btn btn-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    ➕ Add Milestone
                </button>
            </div>
            
            <div id="milestonesContainer">
                <?php 
                $milestones = !empty($background['milestones']) ? (is_string($background['milestones']) ? json_decode($background['milestones'], true) : $background['milestones']) : [];
                if (!is_array($milestones)) $milestones = [];
                if (empty($milestones)) {
                    // Default milestones
                    $milestones = [
                        ['title' => 'Established', 'description' => 'Founded with a vision to provide quality education', 'icon_color' => '#ef4444'],
                        ['title' => 'Growth', 'description' => 'Currently serving over 2,500 students', 'icon_color' => '#3b82f6'],
                        ['title' => 'Excellence', 'description' => 'Recognized for academic and moral excellence', 'icon_color' => '#f59e0b']
                    ];
                }
                foreach ($milestones as $index => $milestone): 
                    $milestone = array_merge(['title' => '', 'description' => '', 'icon_color' => '#ef4444'], $milestone);
                ?>
                    <div class="milestone-item-admin" data-index="<?php echo $index; ?>" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #fde68a;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Milestone <?php echo $index + 1; ?></h4>
                            <button type="button" onclick="removeMilestone(<?php echo $index; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                                🗑️ Remove
                            </button>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label class="form-label">Title</label>
                            <input type="text" name="milestones[<?php echo $index; ?>][title]" class="form-input" 
                                   value="<?php echo htmlspecialchars($milestone['title']); ?>" 
                                   placeholder="e.g., Established">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label class="form-label">Description</label>
                            <textarea name="milestones[<?php echo $index; ?>][description]" class="form-input" rows="2" 
                                      placeholder="e.g., Founded with a vision to provide quality education"><?php echo htmlspecialchars($milestone['description']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Icon Color</label>
                            <input type="color" name="milestones[<?php echo $index; ?>][icon_color]" class="form-input" 
                                   value="<?php echo htmlspecialchars($milestone['icon_color']); ?>" 
                                   style="height: 40px; width: 100px;">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
    </form>
</div>

<script>
let milestoneIndex = <?php echo count($milestones); ?>;

function addMilestone() {
    const container = document.getElementById('milestonesContainer');
    const milestoneHtml = `
        <div class="milestone-item-admin" data-index="${milestoneIndex}" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #fde68a;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Milestone ${milestoneIndex + 1}</h4>
                <button type="button" onclick="removeMilestone(${milestoneIndex})" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">
                    🗑️ Remove
                </button>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Title</label>
                <input type="text" name="milestones[${milestoneIndex}][title]" class="form-input" 
                       placeholder="e.g., Established">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Description</label>
                <textarea name="milestones[${milestoneIndex}][description]" class="form-input" rows="2" 
                          placeholder="e.g., Founded with a vision to provide quality education"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Icon Color</label>
                <input type="color" name="milestones[${milestoneIndex}][icon_color]" class="form-input" 
                       value="#ef4444" style="height: 40px; width: 100px;">
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', milestoneHtml);
    milestoneIndex++;
    
    // Scroll to the new milestone
    const newMilestone = container.lastElementChild;
    if (newMilestone) {
        newMilestone.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function removeMilestone(index) {
    const milestoneItem = document.querySelector(`.milestone-item-admin[data-index="${index}"]`);
    if (milestoneItem) {
        milestoneItem.remove();
        reindexMilestones();
    }
}

function reindexMilestones() {
    const container = document.getElementById('milestonesContainer');
    const milestones = container.querySelectorAll('.milestone-item-admin');
    milestones.forEach((milestone, newIndex) => {
        milestone.setAttribute('data-index', newIndex);
        const title = milestone.querySelector('h4');
        if (title) {
            title.textContent = `Milestone ${newIndex + 1}`;
        }
        
        // Update all input names
        const inputs = milestone.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/milestones\[\d+\]/, `milestones[${newIndex}]`);
            }
        });
        
        // Update remove button
        const removeBtn = milestone.querySelector('button[onclick*="removeMilestone"]');
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeMilestone(${newIndex})`);
        }
    });
    milestoneIndex = milestones.length;
}

// Handle form submission - convert milestones array properly
document.getElementById('backgroundForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Collect milestones data
    const milestones = [];
    const milestoneItems = document.querySelectorAll('.milestone-item-admin');
    
    milestoneItems.forEach((item) => {
        const title = item.querySelector('input[name*="[title]"]')?.value?.trim();
        const description = item.querySelector('textarea[name*="[description]"]')?.value?.trim();
        const iconColor = item.querySelector('input[name*="[icon_color]"]')?.value?.trim() || '#ef4444';
        
        if (title || description) {
            milestones.push({
                title: title || '',
                description: description || '',
                icon_color: iconColor
            });
        }
    });
    
    // Collect history section
    const historyTitle = document.querySelector('input[name="history_title"]')?.value || 'Our History';
    const historyContent = document.querySelector('textarea[name="history_content"]')?.value || '';
    const historySection = {
        title: historyTitle,
        content: historyContent
    };
    
    // Get content_title (for main content section title)
    const contentTitle = document.querySelector('input[name="content_title"]')?.value || '';
    
    // Prepare data
    const formData = new FormData(this);
    const data = {
        title: formData.get('title') || '',
        subtitle: formData.get('subtitle') || '',
        content: formData.get('content') || '',
        content_title: contentTitle || formData.get('subtitle') || 'our history and foundation',
        hero_image: formData.get('hero_image') || '',
        history_image: formData.get('history_image') || '',
        content_image: formData.get('content_image') || '',
        video_url: formData.get('video_url') || '',
        video_embed_code: formData.get('video_embed_code') || '',
        history_section: historySection,
        milestones: milestones
    };
    
    // Send AJAX request
    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse response:', text);
            throw new Error('Server returned invalid response');
        }
        
        if (result.error) {
            showToast(result.error, 'error');
        } else {
            showToast(result.message || 'School background content saved successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to save content. Please try again.', 'error');
    });
    
    return false;
});
</script>
