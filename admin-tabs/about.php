<?php
/**
 * About Section Management - EXACT replica from images
 */
$about = $db->fetchOne("SELECT * FROM about_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];

// Parse JSON fields
$coreValues = [];
$statistics = [];
if (!empty($about['core_values'])) {
    $coreValues = is_string($about['core_values']) ? json_decode($about['core_values'], true) : $about['core_values'];
    if (!is_array($coreValues)) $coreValues = [];
}
if (!empty($about['statistics'])) {
    $statistics = is_string($about['statistics']) ? json_decode($about['statistics'], true) : $about['statistics'];
    if (!is_array($statistics)) $statistics = [];
}

// Get Vision content
$visionMissionContent = null;
try {
    $visionMissionContent = $db->fetchOne("SELECT * FROM vision_mission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
} catch (Exception $e) {
    // Table might not exist
}

// Get Why Choose content
$whyChooseContent = null;
$whyChoosePoints = [];
try {
    $whyChooseContent = $db->fetchOne("SELECT * FROM why_choose_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $whyChoosePoints = $db->fetchAll("SELECT * FROM why_choose_points WHERE is_active = 1 ORDER BY order_index ASC");
} catch (Exception $e) {
    // Tables might not exist
}
?>
<div class="content-card" style="padding: 0;">
    <div style="padding: 30px;">
        <h2 style="font-size: 28px; font-weight: bold; color: #111827; margin: 0 0 30px 0;">About Section Management</h2>
        
        <form method="POST" action="api/content/about.php" id="aboutForm" onsubmit="handleAboutFormSubmit(event); return false;">
            <!-- Main Section -->
            <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Main Section</h3>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- Hero Caption -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Hero Caption</label>
                        <input type="text" 
                               name="hero_caption" 
                               id="about_hero_caption"
                               class="form-input" 
                               style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                               value="<?php echo htmlspecialchars($about['hero_caption'] ?? 'Know Us Better'); ?>"
                               placeholder="Know Us Better"
                               onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                        <small style="color: #6b7280; display: block; margin-top: 5px;">This appears as the caption above the title in the hero section</small>
                    </div>
                    
                    <!-- Section Title -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Section Title</label>
                        <input type="text" 
                               name="title" 
                               id="about_title"
                               class="form-input" 
                               style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                               value="<?php echo htmlspecialchars($about['title'] ?? 'About SJS Academy'); ?>"
                               placeholder="About SJS Academy"
                               onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                    </div>
                    
                    <!-- Main Content -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Main Content</label>
                        <textarea name="content" 
                                  id="about_content"
                                  class="form-input" 
                                  rows="4"
                                  style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical; transition: all 0.2s;"
                                  placeholder="We are dedicated to providing quality education and nurturing young minds."
                                  onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                                  onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"><?php echo htmlspecialchars($about['content'] ?? 'We are dedicated to providing quality education and nurturing young minds.'); ?></textarea>
                    </div>
                    
                    <!-- Header Image (for PageHero) -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Header Image (for PageHero)</label>
                        <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears in the header/hero section of the About Us page</p>
                        <div style="display: flex; align-items: start; gap: 16px;">
                            <div style="flex: 1;">
                                <input type="text" 
                                       name="hero_image" 
                                       id="about_hero_image" 
                                       class="form-input" 
                                       style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb; color: #6b7280; margin-bottom: 12px;"
                                       value="<?php echo htmlspecialchars($about['hero_image'] ?? ''); ?>" 
                                       placeholder="Select image from media library"
                                       readonly>
                                <button type="button" 
                                        onclick="openMediaLibrary('about_hero_image')" 
                                        style="width: 48px; height: 48px; background: #2563eb; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;"
                                        onmouseover="this.style.background='#1d4ed8'"
                                        onmouseout="this.style.background='#2563eb'">
                                    <span style="font-size: 20px; color: white;">🖼️</span>
                                </button>
                            </div>
                            <?php 
                            $heroImageUrl = normalizeImageUrl($about['hero_image'] ?? '');
                            if (!empty($heroImageUrl)): ?>
                                <div style="flex-shrink: 0;">
                                    <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                                         alt="Header image preview" 
                                         style="width: 300px; height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb;"
                                         onerror="this.onerror=null; this.style.display='none';">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Section Image (for Content Area) -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Section Image (for Content Area)</label>
                        <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears in the main content section of the About Us page</p>
                        <div style="display: flex; align-items: start; gap: 16px;">
                            <div style="flex: 1;">
                                <input type="text" 
                                       name="image" 
                                       id="about_image" 
                                       class="form-input" 
                                       style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb; color: #6b7280; margin-bottom: 12px;"
                                       value="<?php echo htmlspecialchars($about['image'] ?? ''); ?>" 
                                       placeholder="Select image from media library"
                                       readonly>
                                <button type="button" 
                                        onclick="openMediaLibrary('about_image')" 
                                        style="width: 48px; height: 48px; background: #2563eb; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;"
                                        onmouseover="this.style.background='#1d4ed8'"
                                        onmouseout="this.style.background='#2563eb'">
                                    <span style="font-size: 20px; color: white;">🖼️</span>
                                </button>
                            </div>
                            <?php 
                            $sectionImageUrl = normalizeImageUrl($about['image'] ?? '');
                            if (!empty($sectionImageUrl)): ?>
                                <div style="flex-shrink: 0;">
                                    <img src="<?php echo htmlspecialchars($sectionImageUrl); ?>" 
                                         alt="Section image preview" 
                                         style="width: 300px; height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb;"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; width: 300px; height: 200px; background: #f3f4f6; border-radius: 8px; border: 2px solid #e5e7eb; align-items: center; justify-content: center; color: #9ca3af;">
                                        Preview
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mission Section -->
            <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Mission Section</h3>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- Mission Title -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Mission Title</label>
                        <input type="text" 
                               name="mission_title" 
                               id="mission_title"
                               class="form-input" 
                               style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                               value="<?php echo htmlspecialchars($about['mission_title'] ?? 'Our Mission'); ?>"
                               placeholder="Our Mission"
                               onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                    </div>
                    
                    <!-- Mission Paragraph 1 -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Mission Paragraph 1</label>
                        <textarea name="mission_content" 
                                  id="mission_content"
                                  class="form-input" 
                                  rows="4"
                                  style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical; transition: all 0.2s;"
                                  placeholder="To provide a nurturing and challenging educational environment..."
                                  onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                                  onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"><?php echo htmlspecialchars($about['mission_content'] ?? 'To provide a nurturing and challenging educational environment that empowers students to achieve academic excellence, develop critical thinking skills, and become responsible global citizens.'); ?></textarea>
                    </div>
                    
                    <!-- Mission Paragraph 2 -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Mission Paragraph 2</label>
                        <textarea name="mission_paragraph2" 
                                  id="mission_paragraph2"
                                  class="form-input" 
                                  rows="4"
                                  style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical; transition: all 0.2s;"
                                  placeholder="We believe in holistic education..."
                                  onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                                  onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"><?php echo htmlspecialchars($about['mission_paragraph2'] ?? 'We believe in holistic education that balances academic rigor with character development, creativity, and social responsibility. Our dedicated faculty and state-of-the-art facilities create an environment where every student can thrive.'); ?></textarea>
                    </div>
                    
                    <!-- Mission Section Image -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Mission Section Image</label>
                        <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears in the Mission section (different from header and content area images)</p>
                        <div style="display: flex; align-items: start; gap: 16px;">
                            <div style="flex: 1;">
                                <input type="text" 
                                       name="about_image_2" 
                                       id="about_image_2" 
                                       class="form-input" 
                                       style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb; color: #6b7280; margin-bottom: 12px;"
                                       value="<?php echo htmlspecialchars($about['about_image_2'] ?? ''); ?>" 
                                       placeholder="Select image from media library"
                                       readonly>
                                <button type="button" 
                                        onclick="openMediaLibrary('about_image_2')" 
                                        style="width: 48px; height: 48px; background: #2563eb; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;"
                                        onmouseover="this.style.background='#1d4ed8'"
                                        onmouseout="this.style.background='#2563eb'">
                                    <span style="font-size: 20px; color: white;">🖼️</span>
                                </button>
                            </div>
                            <?php 
                            $missionImageUrl = normalizeImageUrl($about['about_image_2'] ?? '');
                            if (!empty($missionImageUrl)): ?>
                                <div style="flex-shrink: 0;">
                                    <img src="<?php echo htmlspecialchars($missionImageUrl); ?>" 
                                         alt="Mission image preview" 
                                         style="width: 300px; height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb;"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; width: 300px; height: 200px; background: #f3f4f6; border-radius: 8px; border: 2px solid #e5e7eb; align-items: center; justify-content: center; color: #9ca3af;">
                                        Preview
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Vision, Core Values, and Achievements Section (Combined - Matches Frontend Red Box) -->
            <div style="background: linear-gradient(135deg, #dc2626, #991b1b); border-radius: 12px; padding: 30px; border: 2px solid #991b1b; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h3 style="font-size: 22px; font-weight: 700; color: white; margin: 0 0 10px 0;">📊 Vision, Core Values & Achievements</h3>
                    <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 0;">This section controls the three-column display on the About Us page (matches the red box on frontend)</p>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <!-- Vision Column (Left) -->
                    <div style="background: white; border-radius: 8px; padding: 20px;">
                        <h4 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 15px 0; text-transform: uppercase;">💡 OUR VISION</h4>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 8px;">Vision Text</label>
                            <textarea name="vision_text" 
                                      id="vision_text"
                                      class="form-input" 
                                      rows="3"
                                      style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical;"
                                      placeholder="To build a developmental generation."><?php 
                                $visionText = '';
                                if ($visionMissionContent && !empty($visionMissionContent['vision_text'])) {
                                    $visionText = $visionMissionContent['vision_text'];
                                }
                                if (empty($visionText) && !empty($about['vision_text'])) {
                                    $visionText = $about['vision_text'];
                                }
                                echo htmlspecialchars($visionText ?: 'To build a developmental generation.'); 
                            ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Core Values Column (Middle) -->
                    <div style="background: white; border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; text-transform: uppercase;">⭐ OUR CORE VALUES</h4>
                            <button type="button" 
                                    onclick="addCoreValue()"
                                    style="padding: 6px 12px; background: #16a34a; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer;">
                                ➕ Add
                            </button>
                        </div>
                        <div id="coreValuesContainer" style="display: flex; flex-direction: column; gap: 10px;">
                            <?php if (empty($coreValues)): ?>
                                <p style="color: #9ca3af; font-style: italic; padding: 15px; text-align: center; background: #f9fafb; border-radius: 6px; font-size: 12px;">No values yet. Click "Add" to add core values.</p>
                            <?php else: ?>
                                <?php foreach ($coreValues as $index => $value): ?>
                                    <div class="core-value-item" style="display: flex; gap: 8px; align-items: center; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                                        <input type="text" 
                                               name="core_values[<?php echo $index; ?>][label]" 
                                               class="form-input" 
                                               style="flex: 1; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;"
                                               value="<?php echo htmlspecialchars($value['label'] ?? (is_string($value) ? $value : '')); ?>"
                                               placeholder="e.g., Faith, Discipline">
                                        <input type="hidden" name="core_values[<?php echo $index; ?>][bgColor]" value="<?php echo htmlspecialchars($value['bgColor'] ?? 'bg-blue-100'); ?>">
                                        <input type="hidden" name="core_values[<?php echo $index; ?>][textColor]" value="<?php echo htmlspecialchars($value['textColor'] ?? 'text-blue-800'); ?>">
                                        <button type="button" 
                                                onclick="removeCoreValue(this)"
                                                style="width: 32px; height: 32px; background: #dc2626; color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px;">
                                            🗑️
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Achievements Column (Right) -->
                    <div style="background: white; border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; text-transform: uppercase;">🏆 OUR ACHIEVEMENTS</h4>
                            <button type="button" 
                                    onclick="addStatistic()"
                                    style="padding: 6px 12px; background: #16a34a; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer;">
                                ➕ Add
                            </button>
                        </div>
                        <div id="statisticsContainer" style="display: flex; flex-direction: column; gap: 10px;">
                            <?php if (empty($statistics)): ?>
                                <p style="color: #9ca3af; font-style: italic; padding: 15px; text-align: center; background: #f9fafb; border-radius: 6px; font-size: 12px;">No achievements yet. Click "Add" to add achievements.</p>
                            <?php else: ?>
                                <?php foreach ($statistics as $index => $stat): ?>
                                    <div class="statistic-item" style="display: flex; flex-direction: column; gap: 8px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                                        <div style="display: flex; gap: 8px;">
                                            <input type="text" 
                                                   name="statistics[<?php echo $index; ?>][label]" 
                                                   class="form-input" 
                                                   style="flex: 1; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;"
                                                   value="<?php echo htmlspecialchars($stat['label'] ?? ''); ?>"
                                                   placeholder="Label (e.g., Students)">
                                            <input type="text" 
                                                   name="statistics[<?php echo $index; ?>][value]" 
                                                   class="form-input" 
                                                   style="width: 100px; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;"
                                                   value="<?php echo htmlspecialchars($stat['value'] ?? ''); ?>"
                                                   placeholder="2,500+">
                                        </div>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <select name="statistics[<?php echo $index; ?>][icon]" 
                                                    class="form-input" 
                                                    style="flex: 1; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; background: white; cursor: pointer;">
                                                <option value="Users" <?php echo ($stat['icon'] ?? 'Users') === 'Users' ? 'selected' : ''; ?>>👥 Users</option>
                                                <option value="BookOpen" <?php echo ($stat['icon'] ?? 'Users') === 'BookOpen' ? 'selected' : ''; ?>>📚 BookOpen</option>
                                                <option value="Award" <?php echo ($stat['icon'] ?? 'Users') === 'Award' ? 'selected' : ''; ?>>🏆 Award</option>
                                                <option value="Target" <?php echo ($stat['icon'] ?? 'Users') === 'Target' ? 'selected' : ''; ?>>🎯 Target</option>
                                            </select>
                                            <button type="button" 
                                                    onclick="removeStatistic(this)"
                                                    style="width: 32px; height: 32px; background: #dc2626; color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px;">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Why Choose SJS Section -->
            <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Why Choose SJS Section</h3>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- Why Choose Title -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Section Title</label>
                        <input type="text" 
                               name="why_choose_title" 
                               id="why_choose_title"
                               class="form-input" 
                               style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                               value="<?php echo htmlspecialchars($whyChooseContent['title'] ?? 'Why Choose SJS'); ?>"
                               placeholder="Why Choose SJS"
                               onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                    </div>
                    
                    <!-- Why Choose Image -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Why Choose Image</label>
                        <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">This image appears in the Why Choose SJS section</p>
                        <div style="display: flex; align-items: start; gap: 16px;">
                            <div style="flex: 1;">
                                <input type="text" 
                                       name="why_choose_image" 
                                       id="why_choose_image" 
                                       class="form-input" 
                                       style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb; color: #6b7280; margin-bottom: 12px;"
                                       value="<?php echo htmlspecialchars($whyChooseContent['image'] ?? ''); ?>" 
                                       placeholder="Select image from media library"
                                       readonly>
                                <button type="button" 
                                        onclick="openMediaLibrary('why_choose_image')" 
                                        style="width: 48px; height: 48px; background: #2563eb; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;"
                                        onmouseover="this.style.background='#1d4ed8'"
                                        onmouseout="this.style.background='#2563eb'">
                                    <span style="font-size: 20px; color: white;">🖼️</span>
                                </button>
                            </div>
                            <?php 
                            $whyChooseImageUrl = normalizeImageUrl($whyChooseContent['image'] ?? '');
                            if (!empty($whyChooseImageUrl)): ?>
                                <div style="flex-shrink: 0;">
                                    <img src="<?php echo htmlspecialchars($whyChooseImageUrl); ?>" 
                                         alt="Why Choose image preview" 
                                         style="width: 300px; height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb;"
                                         onerror="this.onerror=null; this.style.display='none';">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Why Choose Points -->
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151;">Why Choose Points</label>
                            <button type="button" 
                                    onclick="addWhyChoosePoint()"
                                    style="padding: 8px 16px; background: #16a34a; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.2s;"
                                    onmouseover="this.style.background='#15803d'"
                                    onmouseout="this.style.background='#16a34a'">
                                <span>➕</span> Add Point
                            </button>
                        </div>
                        <div id="whyChoosePointsContainer" style="display: flex; flex-direction: column; gap: 12px;">
                            <?php if (empty($whyChoosePoints)): ?>
                                <p style="color: #9ca3af; font-style: italic; padding: 15px; text-align: center; background: #f9fafb; border-radius: 6px; font-size: 12px;">No points yet. Click "Add Point" to add why choose points.</p>
                            <?php else: ?>
                                <?php foreach ($whyChoosePoints as $index => $point): ?>
                                    <div class="why-choose-point-item" data-point-id="<?php echo $point['id'] ?? ''; ?>" style="display: flex; gap: 12px; align-items: center; padding: 12px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        <input type="hidden" name="why_choose_points[<?php echo $index; ?>][id]" value="<?php echo htmlspecialchars($point['id'] ?? ''); ?>">
                                        <input type="text" 
                                               name="why_choose_points[<?php echo $index; ?>][point_text]" 
                                               class="form-input" 
                                               style="flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;"
                                               value="<?php echo htmlspecialchars($point['point_text'] ?? ''); ?>"
                                               placeholder="e.g., Strong spiritual foundation and values">
                                        <button type="button" 
                                                onclick="removeWhyChoosePoint(this)"
                                                style="width: 40px; height: 40px; background: #dc2626; color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; flex-shrink: 0;"
                                                onmouseover="this.style.background='#b91c1c'"
                                                onmouseout="this.style.background='#dc2626'">
                                            <span style="font-size: 16px;">🗑️</span>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section Preview -->
            <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Section Preview</h3>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- Header Image Preview -->
                    <div>
                        <h4 style="font-size: 14px; font-weight: 500; color: #374151; margin: 0 0 12px 0;">Header Image Preview</h4>
                        <?php if (!empty($heroImageUrl)): ?>
                            <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                                 alt="Header image preview" 
                                 style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;"
                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none; width: 100%; height: 200px; background: #f3f4f6; border-radius: 8px; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                No image
                            </div>
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #f3f4f6; border-radius: 8px; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                No image
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Section Image Preview -->
                    <div>
                        <h4 style="font-size: 14px; font-weight: 500; color: #374151; margin: 0 0 12px 0;">Section Image Preview</h4>
                        <?php if (!empty($sectionImageUrl)): ?>
                            <img src="<?php echo htmlspecialchars($sectionImageUrl); ?>" 
                                 alt="Section image preview" 
                                 style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;"
                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 100%; height: 200px; background: #f3f4f6; border-radius: 8px; border: 1px solid #e5e7eb; align-items: center; justify-content: center; color: #9ca3af;">
                                No image
                            </div>
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #f3f4f6; border-radius: 8px; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                No image
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: flex; gap: 12px; margin-top: 30px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="submit" 
                        style="padding: 12px 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(37, 99, 235, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span>💾</span> Save Changes
                </button>
                <button type="button" 
                        onclick="window.open('about.php', '_blank')"
                        style="padding: 12px 24px; background: #4b5563; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                        onmouseover="this.style.background='#374151'; this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.background='#4b5563'; this.style.transform='translateY(0)'">
                    <span>🔗</span> Preview
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let coreValueIndex = <?php echo count($coreValues); ?>;
let statisticIndex = <?php echo count($statistics); ?>;
let whyChoosePointIndex = <?php echo count($whyChoosePoints); ?>;

function addWhyChoosePoint() {
    const container = document.getElementById('whyChoosePointsContainer');
    const div = document.createElement('div');
    div.className = 'why-choose-point-item';
    div.style.cssText = 'display: flex; gap: 12px; align-items: center; padding: 12px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;';
    div.innerHTML = `
        <input type="hidden" name="why_choose_points[${whyChoosePointIndex}][id]" value="">
        <input type="text" 
               name="why_choose_points[${whyChoosePointIndex}][point_text]" 
               class="form-input" 
               style="flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;"
               placeholder="e.g., Strong spiritual foundation and values">
        <button type="button" 
                onclick="removeWhyChoosePoint(this)"
                style="width: 40px; height: 40px; background: #dc2626; color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; flex-shrink: 0;"
                onmouseover="this.style.background='#b91c1c'"
                onmouseout="this.style.background='#dc2626'">
            <span style="font-size: 16px;">🗑️</span>
        </button>
    `;
    container.appendChild(div);
    whyChoosePointIndex++;
}

function removeWhyChoosePoint(button) {
    const item = button.closest('.why-choose-point-item');
    if (item) {
        item.remove();
        // Show empty message if no items left
        const container = document.getElementById('whyChoosePointsContainer');
        if (container && container.children.length === 0) {
            container.innerHTML = '<p style="color: #9ca3af; font-style: italic; padding: 15px; text-align: center; background: #f9fafb; border-radius: 6px; font-size: 12px;">No points yet. Click "Add Point" to add why choose points.</p>';
        }
    }
}

function addCoreValue() {
    const container = document.getElementById('coreValuesContainer');
    // Remove empty message if exists
    const emptyMsg = container.querySelector('p[style*="No values yet"]');
    if (emptyMsg) emptyMsg.remove();
    
    const div = document.createElement('div');
    div.className = 'core-value-item';
    div.style.cssText = 'display: flex; gap: 8px; align-items: center; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;';
    div.innerHTML = `
        <input type="text" 
               name="core_values[${coreValueIndex}][label]" 
               class="form-input" 
               style="flex: 1; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;"
               placeholder="e.g., Faith, Discipline">
        <input type="hidden" name="core_values[${coreValueIndex}][bgColor]" value="bg-blue-100">
        <input type="hidden" name="core_values[${coreValueIndex}][textColor]" value="text-blue-800">
        <button type="button" 
                onclick="removeCoreValue(this)"
                style="width: 32px; height: 32px; background: #dc2626; color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px;">
            🗑️
        </button>
    `;
    container.appendChild(div);
    coreValueIndex++;
}

function removeCoreValue(button) {
    const item = button.closest('.core-value-item');
    if (item) {
        item.remove();
        // Show empty message if no items left
        const container = document.getElementById('coreValuesContainer');
        if (container && container.children.length === 0) {
            container.innerHTML = '<p style="color: #9ca3af; font-style: italic; padding: 15px; text-align: center; background: #f9fafb; border-radius: 6px; font-size: 12px;">No values yet. Click "Add" to add core values.</p>';
        }
    }
}

function addStatistic() {
    const container = document.getElementById('statisticsContainer');
    // Remove empty message if exists
    const emptyMsg = container.querySelector('p[style*="No achievements yet"]');
    if (emptyMsg) emptyMsg.remove();
    
    const div = document.createElement('div');
    div.className = 'statistic-item';
    div.style.cssText = 'display: flex; flex-direction: column; gap: 8px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;';
    div.innerHTML = `
        <div style="display: flex; gap: 8px;">
            <input type="text" 
                   name="statistics[${statisticIndex}][label]" 
                   class="form-input" 
                   style="flex: 1; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;"
                   placeholder="Label (e.g., Students)">
            <input type="text" 
                   name="statistics[${statisticIndex}][value]" 
                   class="form-input" 
                   style="width: 100px; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;"
                   placeholder="2,500+">
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <select name="statistics[${statisticIndex}][icon]" 
                    class="form-input" 
                    style="flex: 1; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; background: white; cursor: pointer;">
                <option value="Users">👥 Users</option>
                <option value="BookOpen">📚 BookOpen</option>
                <option value="Award">🏆 Award</option>
                <option value="Target">🎯 Target</option>
            </select>
            <button type="button" 
                    onclick="removeStatistic(this)"
                    style="width: 32px; height: 32px; background: #dc2626; color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px;">
                🗑️
            </button>
        </div>
    `;
    container.appendChild(div);
    statisticIndex++;
}

function removeStatistic(button) {
    const item = button.closest('.statistic-item');
    if (item) {
        item.remove();
        // Show empty message if no items left
        const container = document.getElementById('statisticsContainer');
        if (container && container.children.length === 0) {
            container.innerHTML = '<p style="color: #9ca3af; font-style: italic; padding: 15px; text-align: center; background: #f9fafb; border-radius: 6px; font-size: 12px;">No achievements yet. Click "Add" to add achievements.</p>';
        }
    }
}

// Update section image preview when URL changes
function updateSectionImagePreview() {
    const imageInput = document.getElementById('about_image');
    if (!imageInput) return;
    
    const imageUrl = imageInput.value;
    const previewContainer = imageInput.closest('div').nextElementSibling;
    
    if (previewContainer && imageUrl) {
        const img = previewContainer.querySelector('img');
        if (img) {
            img.src = imageUrl;
            img.style.display = 'block';
            const placeholder = previewContainer.querySelector('div[style*="background: #f3f4f6"]');
            if (placeholder) placeholder.style.display = 'none';
        } else {
            // Create image if it doesn't exist
            const newImg = document.createElement('img');
            newImg.src = imageUrl;
            newImg.alt = 'Section image preview';
            newImg.style.cssText = 'width: 300px; height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb;';
            newImg.onerror = function() {
                this.style.display = 'none';
                const placeholder = previewContainer.querySelector('div[style*="background: #f3f4f6"]');
                if (placeholder) placeholder.style.display = 'flex';
            };
            previewContainer.innerHTML = '';
            previewContainer.appendChild(newImg);
        }
    }
}

// Override openMediaLibrary to update preview after selection
const originalOpenMediaLibrary = window.openMediaLibrary;
window.openMediaLibrary = function(fieldName) {
    if (typeof originalOpenMediaLibrary === 'function') {
        originalOpenMediaLibrary(fieldName);
    }
    
    // Set up listener for when image is selected
    if (fieldName === 'about_image') {
        setTimeout(() => {
            const imageInput = document.getElementById('about_image');
            if (imageInput) {
                imageInput.addEventListener('change', updateSectionImagePreview);
                imageInput.addEventListener('input', updateSectionImagePreview);
            }
        }, 100);
    }
};

function handleAboutFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    // Collect core values
    const coreValues = [];
    form.querySelectorAll('.core-value-item').forEach((item, index) => {
        const label = item.querySelector('input[name*="[label]"]').value;
        const bgColor = item.querySelector('input[name*="[bgColor]"]').value;
        const textColor = item.querySelector('input[name*="[textColor]"]').value;
        if (label) {
            coreValues.push({ label, bgColor, textColor });
        }
    });
    
    // Collect statistics
    const statistics = [];
    form.querySelectorAll('.statistic-item').forEach((item, index) => {
        const label = item.querySelector('input[name*="[label]"]').value;
        const value = item.querySelector('input[name*="[value]"]').value;
        const icon = item.querySelector('select[name*="[icon]"]').value;
        if (label) {
            statistics.push({ label, value, icon });
        }
    });
    
    // Collect why choose points
    const whyChoosePoints = [];
    form.querySelectorAll('.why-choose-point-item').forEach((item, index) => {
        const pointText = item.querySelector('input[name*="[point_text]"]').value.trim();
        const pointId = item.querySelector('input[name*="[id]"]').value;
        if (pointText) {
            whyChoosePoints.push({
                id: pointId || null,
                point_text: pointText
            });
        }
    });
    
    // Build data object
    const data = {
        hero_caption: formData.get('hero_caption') || '',
        title: formData.get('title') || '',
        content: formData.get('content') || '',
        image: formData.get('image') || '',
        hero_image: formData.get('hero_image') || '',
        about_image_2: formData.get('about_image_2') || '',
        mission_title: formData.get('mission_title') || '',
        mission_content: formData.get('mission_content') || '',
        mission_paragraph2: formData.get('mission_paragraph2') || '',
        vision_text: formData.get('vision_text') || '',
        why_choose_title: formData.get('why_choose_title') || '',
        why_choose_image: formData.get('why_choose_image') || '',
        why_choose_points: whyChoosePoints,
        core_values: coreValues,
        statistics: statistics
    };
    
    // Show loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span>💾</span> Saving...';
    submitBtn.disabled = true;
    
    fetch('api/content/about.php', {
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
            showToast(result.message || 'About content saved successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save about content. Please try again.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
</script>
