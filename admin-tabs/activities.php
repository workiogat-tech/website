<?php
/**
 * Events Tab
 */
$activities = $db->fetchAll("SELECT * FROM activities WHERE is_active = 1 ORDER BY created_at DESC");
// Check if activities_settings table exists
$activitiesSettings = [];
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'activities_settings'");
    if ($tableExists) {
        $activitiesSettings = $db->fetchOne("SELECT * FROM activities_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
    }
} catch (Exception $e) {
    // Table doesn't exist, that's okay
}
?>
<div class="content-card" style="padding: 0;">
    <div style="background: linear-gradient(135deg, #fff7ed, #fef3c7); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px; border: 1px solid #fed7aa; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Events Management</h2>
            <div style="display: flex; gap: 8px;">
                <button onclick="window.open('events.php', '_blank')" style="padding: 8px 16px; background: #4b5563; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: background 0.2s;" onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#4b5563'">
                    <span>🔗</span> Preview
                </button>
                <button onclick="showAddActivityModal()" style="padding: 8px 16px; background: #9333ea; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: background 0.2s;" onmouseover="this.style.background='#7e22ce'" onmouseout="this.style.background='#9333ea'">
                    <span>➕</span> Add New
                </button>
            </div>
        </div>
        
        <div style="margin-bottom: 16px;">
            <p style="font-size: 14px; color: #6b7280; margin: 0;">
                Total events: <?php echo count($activities); ?>
                <span id="searchResultsText" style="display: none; margin-left: 8px; color: #9333ea;"></span>
            </p>
            <?php if (empty($activities)): ?>
                <p style="color: #6b7280; font-style: italic; margin-top: 8px;">
                    No activities found. Add some activities to get started.
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Search Bar -->
        <div style="margin-bottom: 16px;">
            <input type="text" id="activitiesSearch" placeholder="Search events..." 
                   style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;"
                   onkeyup="filterActivities()">
        </div>
    </div>
    
    <!-- Events Page Header Settings -->
    <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 12px; padding: 24px; border: 1px solid #f59e0b; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <span style="font-size: 24px;">📅</span>
            <h3 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0;">Events Page Header Settings</h3>
        </div>
        <p style="color: #374151; font-size: 14px; margin-bottom: 20px;">Configure the header image, title, and subtitle for the Events page.</p>
        
        <!-- Instructions -->
        <div style="background: white; border-radius: 8px; padding: 16px; margin-bottom: 20px; border-left: 4px solid #f59e0b;">
            <p style="font-size: 14px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">How to change the page header image:</p>
            <ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #6b7280; line-height: 1.8;">
                <li>Click the <span style="font-weight: 600; color: #f59e0b;">"Select Image"</span> button below</li>
                <li>Browse your media library or upload a new image</li>
                <li>Click on the image you want to use</li>
                <li>Click <span style="font-weight: 600; color: #f59e0b;">"Save Activities Page Settings"</span> to save your changes</li>
            </ol>
        </div>
        
        <form method="POST" action="api/content/activities-settings.php" id="activitiesHeroForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <div style="display: flex; align-items: start; gap: 16px; margin-bottom: 20px;">
                <?php 
                $heroImageUrl = normalizeImageUrl($activitiesSettings['hero_image'] ?? '');
                if (!empty($heroImageUrl)): ?>
                    <div style="flex-shrink: 0;">
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview" 
                             style="width: 200px; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #f59e0b; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display: none; width: 200px; height: 120px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center; position: absolute; top: 0; left: 0;">
                            <div style="text-align: center;">
                                <span style="font-size: 32px; color: #999;">🖼️</span>
                                <p style="font-size: 12px; color: #999; margin-top: 8px;">Failed to load</p>
                            </div>
                        </div>
                        <p style="text-align: center; font-size: 12px; color: #6b7280; margin-top: 8px; font-weight: 500;">Preview</p>
                    </div>
                <?php else: ?>
                    <div style="flex-shrink: 0; width: 200px; height: 120px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;">
                        <div style="text-align: center;">
                            <span style="font-size: 32px; color: #999;">🖼️</span>
                            <p style="font-size: 12px; color: #999; margin-top: 8px;">No image</p>
                        </div>
                    </div>
                <?php endif; ?>
                <div style="flex: 1;">
                    <input type="text" 
                           name="hero_image" 
                           id="activities_hero_image" 
                           class="form-input" 
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb; color: #6b7280; margin-bottom: 12px;"
                           value="<?php echo htmlspecialchars($activitiesSettings['hero_image'] ?? ''); ?>" 
                           placeholder="Select image from media library"
                           readonly>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <button type="button" 
                                onclick="openMediaLibrary('activities_hero_image')" 
                                style="padding: 12px 24px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(245, 158, 11, 0.4)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <span>🖼️</span> Select Image from Media Library
                        </button>
                        <?php if (!empty($activitiesSettings['hero_image'])): ?>
                            <button type="button" 
                                    onclick="document.getElementById('activities_hero_image').value='';" 
                                    style="padding: 12px 24px; background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(220, 38, 38, 0.4)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <span>🗑️</span> Remove Image
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Page Title and Subtitle -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Page Title</label>
                    <input type="text" 
                           name="title" 
                           class="form-input" 
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                           value="<?php echo htmlspecialchars($activitiesSettings['title'] ?? 'Events'); ?>"
                           placeholder="Events"
                           onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                           onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                </div>
                <div>
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Page Subtitle</label>
                    <input type="text" 
                           name="subtitle" 
                           class="form-input" 
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                           value="<?php echo htmlspecialchars($activitiesSettings['subtitle'] ?? 'Upcoming Events'); ?>"
                           placeholder="Upcoming Events"
                           onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                           onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <button type="submit" 
                        style="padding: 12px 24px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(245, 158, 11, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span>💾</span> Save Events Page Settings
                </button>
            </div>
        </form>
    </div>
    
    <!-- Desktop Table View -->
    <div style="display: none;" class="desktop-table-view lg:block">
        <div style="overflow-x: auto;">
            <table style="min-width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb;">
                    <tr>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">ACTIVITY</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">CATEGORY</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">DATE</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">TIME</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">STATUS</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody style="background: white;" id="activitiesTableBody">
                    <?php if (empty($activities)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                                No activities found. Click "Add New" to create one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <tr class="activity-row" data-activity-id="<?php echo $activity['id']; ?>" 
                                style="border-bottom: 1px solid #e5e7eb; transition: all 0.3s;"
                                onmouseover="this.style.background='linear-gradient(to right, #fff7ed, #fef3c7)'; this.style.transform='scale(1.01)'"
                                onmouseout="this.style.background='white'; this.style.transform='scale(1)'">
                                <td style="padding: 16px;">
                                    <div style="display: flex; align-items: center;">
                                        <div style="width: 40px; height: 40px; border-radius: 8px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; margin-right: 12px; overflow: hidden;">
                                            <?php 
                                            $activityImageUrl = normalizeImageUrl($activity['image'] ?? '');
                                            if (!empty($activityImageUrl)): ?>
                                                <img src="<?php echo htmlspecialchars($activityImageUrl); ?>" 
                                                     alt="<?php echo htmlspecialchars($activity['title']); ?>"
                                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;"
                                                     onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:20px;\'>📅</span>';">
                                            <?php else: ?>
                                                <span style="font-size: 20px; color: #6b7280;">📅</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="min-width: 0; flex: 1;">
                                            <div style="font-size: 14px; font-weight: 500; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($activity['title'] ?? ''); ?></div>
                                            <div style="font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;"><?php echo htmlspecialchars(substr($activity['description'] ?? '', 0, 50)); ?>...</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 16px; white-space: nowrap;">
                                    <?php if (!empty($activity['category'])): ?>
                                        <span style="padding: 4px 8px; display: inline-flex; font-size: 12px; font-weight: 600; border-radius: 9999px; background: #dbeafe; color: #1e40af;">
                                            <?php echo htmlspecialchars($activity['category']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: #9ca3af;">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 16px; white-space: nowrap; font-size: 14px; color: #111827;">
                                    <?php if (!empty($activity['date'])): ?>
                                        <?php echo date('M d, Y', strtotime($activity['date'])); ?>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">No date</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 16px; white-space: nowrap; font-size: 14px; color: #6b7280;">
                                    No time
                                </td>
                                <td style="padding: 16px; white-space: nowrap;">
                                    <span style="padding: 4px 8px; display: inline-flex; font-size: 12px; font-weight: 600; border-radius: 9999px; background: #fef3c7; color: #92400e;">
                                        Active
                                    </span>
                                </td>
                                <td style="padding: 16px; white-space: nowrap; font-size: 14px; font-weight: 500;">
                                    <div style="display: flex; gap: 8px;">
                                        <button onclick="editActivity(<?php echo $activity['id']; ?>)" style="color: #2563eb; background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: color 0.2s;" onmouseover="this.style.color='#1e40af'" onmouseout="this.style.color='#2563eb'">
                                            <span>✏️</span> Edit
                                        </button>
                                        <button onclick="deleteActivity(<?php echo $activity['id']; ?>)" style="color: #dc2626; background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: color 0.2s;" onmouseover="this.style.color='#b91c1c'" onmouseout="this.style.color='#dc2626'">
                                            <span>🗑️</span> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div style="display: block;" class="mobile-card-view lg:hidden">
        <div style="display: flex; flex-direction: column; gap: 16px;" id="activitiesCardContainer">
            <?php if (empty($activities)): ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    No activities found. Click "Add New" to create one.
                </div>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <div class="activity-card" data-activity-id="<?php echo $activity['id']; ?>"
                         style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; padding: 24px; transition: all 0.2s;"
                         onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'"
                         onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <div style="width: 40px; height: 40px; background: #3b82f6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 20px; color: white;">📅</span>
                            </div>
                            <div>
                                <h3 style="font-size: 18px; font-weight: bold; color: #111827; margin: 0;"><?php echo htmlspecialchars($activity['title'] ?? ''); ?></h3>
                                <p style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;"><?php echo htmlspecialchars($activity['description'] ?? ''); ?></p>
                            </div>
                        </div>
                        <p style="font-size: 14px; color: #6b7280; margin: 0 0 16px 0;">
                            <?php echo !empty($activity['date']) ? date('M d, Y', strtotime($activity['date'])) : 'No date'; ?>
                        </p>
                        <div style="display: flex; gap: 8px;">
                            <button onclick="editActivity(<?php echo $activity['id']; ?>)" style="flex: 1; padding: 12px; background: #2563eb; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                                Edit
                            </button>
                            <button onclick="deleteActivity(<?php echo $activity['id']; ?>)" style="flex: 1; padding: 12px; background: #dc2626; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div id="addActivityModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; font-weight: bold; margin: 0;">➕ Add New Activity</h3>
            <button onclick="closeAddActivityModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>
        <form id="addActivityForm" onsubmit="handleAddActivity(event)">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="4"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Event Image *</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image will be displayed prominently on the event card</p>
                <div style="display: flex; align-items: start; gap: 16px;">
                    <div style="flex: 1;">
                        <input type="text" name="image" id="activity_image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                        <div style="display: flex; gap: 10px; margin-top: 12px;">
                            <button type="button" onclick="openMediaLibrary('activity_image')" 
                                    class="btn btn-primary" style="flex: 1;">🖼️ Select Image</button>
                        </div>
                    </div>
                    <div id="activity_image_preview" style="flex-shrink: 0; width: 200px; height: 150px; background: #f9fafb; border-radius: 8px; border: 2px dashed #d1d5db; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <div style="text-align: center; color: #9ca3af;">
                            <span style="font-size: 32px;">🖼️</span>
                            <p style="font-size: 12px; margin-top: 8px;">No image</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Time</label>
                <input type="text" name="time" class="form-input" 
                       placeholder="e.g., 9:00 AM - 3:00 PM">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Event time range</small>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Event</button>
                <button type="button" onclick="closeAddActivityModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Activity Modal -->
<div id="editActivityModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 24px; font-weight: bold; margin: 0;">✏️ Edit Event</h3>
            <button onclick="closeEditActivityModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>
        <form id="editActivityForm" onsubmit="handleEditActivity(event)">
            <input type="hidden" id="edit_activity_id" name="id">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" id="edit_activity_title" name="title" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="edit_activity_description" name="description" class="form-input" rows="4"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" id="edit_activity_date" name="date" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" id="edit_activity_location" name="location" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" id="edit_activity_category" name="category" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Event Image *</label>
                <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image will be displayed prominently on the event card</p>
                <div style="display: flex; align-items: start; gap: 16px;">
                    <div style="flex: 1;">
                        <input type="text" id="edit_activity_image" name="image" class="form-input" 
                               placeholder="Select image from media library" readonly>
                        <div style="display: flex; gap: 10px; margin-top: 12px;">
                            <button type="button" onclick="openMediaLibrary('edit_activity_image')" 
                                    class="btn btn-primary" style="flex: 1;">🖼️ Select Image</button>
                        </div>
                    </div>
                    <div id="edit_activity_image_preview" style="flex-shrink: 0; width: 200px; height: 150px; background: #f9fafb; border-radius: 8px; border: 2px dashed #d1d5db; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <div style="text-align: center; color: #9ca3af;">
                            <span style="font-size: 32px;">🖼️</span>
                            <p style="font-size: 12px; margin-top: 8px;">No image</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Time</label>
                <input type="text" id="edit_activity_time" name="time" class="form-input" 
                       placeholder="e.g., 9:00 AM - 3:00 PM">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Event time range</small>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Update Event</button>
                <button type="button" onclick="closeEditActivityModal()" class="btn" style="background: #6b7280; color: white; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Responsive view switching
function updateView() {
    const isDesktop = window.innerWidth >= 1024;
    document.querySelector('.desktop-table-view').style.display = isDesktop ? 'block' : 'none';
    document.querySelector('.mobile-card-view').style.display = isDesktop ? 'none' : 'block';
}
window.addEventListener('resize', updateView);
updateView();

// Search/Filter functionality
function filterActivities() {
    const searchQuery = document.getElementById('activitiesSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.activity-row, .activity-card');
    const searchResultsText = document.getElementById('searchResultsText');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const title = row.querySelector('td:first-child div:first-child, h3')?.textContent.toLowerCase() || '';
        const description = row.querySelector('td:first-child div:last-child, p')?.textContent.toLowerCase() || '';
        const category = row.querySelector('td:nth-child(2) span, td:nth-child(2)')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchQuery) || description.includes(searchQuery) || category.includes(searchQuery)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    if (searchQuery) {
        searchResultsText.style.display = 'inline';
        searchResultsText.textContent = `Found ${visibleCount} results for "${searchQuery}"`;
    } else {
        searchResultsText.style.display = 'none';
    }
}

function showAddActivityModal() {
    document.getElementById('addActivityModal').style.display = 'flex';
    document.getElementById('addActivityForm').reset();
    // Reset image preview
    document.getElementById('activity_image_preview').innerHTML = '<div style="text-align: center; color: #9ca3af;"><span style="font-size: 32px;">🖼️</span><p style="font-size: 12px; margin-top: 8px;">No image</p></div>';
}

// Update image preview when image is selected
function updateActivityImagePreview(fieldName, imageUrl) {
    let previewId;
    if (fieldName === 'activity_image') {
        previewId = 'activity_image_preview';
    } else if (fieldName === 'edit_activity_image') {
        previewId = 'edit_activity_image_preview';
    } else {
        return;
    }
    
    const preview = document.getElementById(previewId);
    if (!preview) return;
    
    if (imageUrl) {
        const imgUrl = imageUrl.startsWith('http') ? imageUrl : ('/' + imageUrl.replace(/^\/+/, ''));
        preview.innerHTML = `<img src="${imgUrl}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='<div style=\'text-align: center; color: #9ca3af;\'><span style=\'font-size: 32px;\'>🖼️</span><p style=\'font-size: 12px; margin-top: 8px;\'>Failed to load</p></div>'">`;
    } else {
        preview.innerHTML = '<div style="text-align: center; color: #9ca3af;"><span style="font-size: 32px;">🖼️</span><p style="font-size: 12px; margin-top: 8px;">No image</p></div>';
    }
}

// Watch for image input changes and media library selection
document.addEventListener('DOMContentLoaded', function() {
    const activityImageInput = document.getElementById('activity_image');
    const editActivityImageInput = document.getElementById('edit_activity_image');
    
    // Watch for changes
    if (activityImageInput) {
        activityImageInput.addEventListener('change', function() {
            updateActivityImagePreview('activity_image', this.value);
        });
        // Also check on input event
        activityImageInput.addEventListener('input', function() {
            updateActivityImagePreview('activity_image', this.value);
        });
    }
    
    if (editActivityImageInput) {
        editActivityImageInput.addEventListener('change', function() {
            updateActivityImagePreview('edit_activity_image', this.value);
        });
        editActivityImageInput.addEventListener('input', function() {
            updateActivityImagePreview('edit_activity_image', this.value);
        });
    }
    
    // Override media library callback to update preview
    const originalSelectMediaImage = window.selectMediaImage;
    if (originalSelectMediaImage) {
        window.selectMediaImage = function(imageUrl) {
            originalSelectMediaImage(imageUrl);
            // Check if we're working with activity images
            const currentField = window.currentImageField || '';
            if (currentField === 'activity_image' || currentField === 'edit_activity_image') {
                setTimeout(() => {
                    const field = document.getElementById(currentField);
                    if (field && field.value) {
                        updateActivityImagePreview(currentField, field.value);
                    }
                }, 100);
            }
        };
    }
});

let currentEditActivityId = null;

function editActivity(id) {
    currentEditActivityId = id;
    // Fetch activity data
    fetch('api/content/activities.php?id=' + id)
        .then(response => response.json())
        .then(result => {
            if (result.error) {
                showToast('Error loading activity: ' + result.error, 'error');
                return;
            }
            
            const activity = result.data || result;
            if (!activity) {
                showToast('Activity not found', 'error');
                return;
            }
            
            // Populate edit form
            document.getElementById('editActivityForm').reset();
            document.getElementById('edit_activity_id').value = activity.id || id;
            document.getElementById('edit_activity_title').value = activity.title || '';
            document.getElementById('edit_activity_description').value = activity.description || '';
            document.getElementById('edit_activity_date').value = activity.date || '';
            document.getElementById('edit_activity_location').value = activity.location || '';
            document.getElementById('edit_activity_category').value = activity.category || '';
            document.getElementById('edit_activity_time').value = activity.time || '';
            document.getElementById('edit_activity_image').value = activity.image || '';
            
            // Update image preview
            const editPreview = document.getElementById('edit_activity_image_preview');
            if (activity.image) {
                const imgUrl = activity.image.startsWith('http') ? activity.image : ('/' + activity.image.replace(/^\/+/, ''));
                editPreview.innerHTML = `<img src="${imgUrl}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='<div style=\'text-align: center; color: #9ca3af;\'><span style=\'font-size: 32px;\'>🖼️</span><p style=\'font-size: 12px; margin-top: 8px;\'>No image</p></div>'">`;
            } else {
                editPreview.innerHTML = '<div style="text-align: center; color: #9ca3af;"><span style="font-size: 32px;">🖼️</span><p style="font-size: 12px; margin-top: 8px;">No image</p></div>';
            }
            
            // Show edit modal
            document.getElementById('editActivityModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading activity:', error);
            showToast('Failed to load activity. Please try again.', 'error');
        });
}

function closeAddActivityModal() {
    document.getElementById('addActivityModal').style.display = 'none';
}

function closeEditActivityModal() {
    document.getElementById('editActivityModal').style.display = 'none';
    currentEditActivityId = null;
}

function handleEditActivity(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/activities.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            showToast('Error: ' + result.error, 'error');
        } else {
            showToast(result.message || 'Activity updated successfully!', 'success');
            closeEditActivityModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update activity. Please try again.', 'error');
    });
}

function handleAddActivity(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('api/content/activities.php', {
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
            showToast(result.message || 'Activity added successfully!', 'success');
            closeAddActivityModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add activity. Please try again.');
    });
}

function deleteActivity(id) {
    if (!confirm('Are you sure you want to delete this activity?')) return;
    
    fetch('api/content/activities.php?id=' + id, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            showToast(result.message || 'Activity deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete activity. Please try again.');
    });
}

document.getElementById('addActivityModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddActivityModal();
});

document.getElementById('editActivityModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditActivityModal();
});
</script>
