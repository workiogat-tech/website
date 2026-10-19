<?php
$sponsorship = $db->fetchOne("SELECT * FROM sponsorship_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$additionalSlides = [];
if (!empty($sponsorship['additional_slides'])) {
    $additionalSlides = is_string($sponsorship['additional_slides']) ? json_decode($sponsorship['additional_slides'], true) : $sponsorship['additional_slides'];
    if (!is_array($additionalSlides)) $additionalSlides = [];
}

// Parse steps if they exist
$steps = [];
if (!empty($sponsorship['sponsorship_steps'])) {
    $steps = is_string($sponsorship['sponsorship_steps']) ? json_decode($sponsorship['sponsorship_steps'], true) : $sponsorship['sponsorship_steps'];
    if (!is_array($steps)) $steps = [];
}
// Default steps if none exist
if (empty($steps)) {
    $steps = [
        ['title' => 'Choose Your Child', 'description' => 'Browse profiles and select a child whose story touches your heart.'],
        ['title' => 'Make Your Pledge', 'description' => 'Choose your sponsorship level and set up your monthly contribution.'],
        ['title' => 'Stay Connected', 'description' => 'Receive regular updates, photos, and letters from your sponsored child.'],
        ['title' => 'See the Impact', 'description' => 'Watch your sponsored child grow, learn, and achieve their dreams.']
    ];
}

// Parse sponsorship levels if they exist
$sponsorshipLevels = [];
if (!empty($sponsorship['sponsorship_levels'])) {
    $sponsorshipLevels = is_string($sponsorship['sponsorship_levels']) ? json_decode($sponsorship['sponsorship_levels'], true) : $sponsorship['sponsorship_levels'];
    if (!is_array($sponsorshipLevels)) $sponsorshipLevels = [];
}
// Default levels if none exist
if (empty($sponsorshipLevels)) {
    $sponsorshipLevels = [
        [
            'name' => 'Full Scholarship',
            'amount' => 'UGX 500,000',
            'description' => 'Complete tuition, books, and uniform for one academic year',
            'benefits' => ['Full tuition coverage', 'Textbooks and supplies', 'School uniform', 'Monthly progress reports']
        ],
        [
            'name' => 'Partial Scholarship',
            'amount' => 'UGX 250,000',
            'description' => 'Half tuition coverage for one academic year',
            'benefits' => ['50% tuition coverage', 'Textbooks', 'Quarterly progress reports', 'Student communication']
        ],
        [
            'name' => 'Book & Uniform',
            'amount' => 'UGX 100,000',
            'description' => 'Essential supplies and uniform for one student',
            'benefits' => ['Complete textbook set', 'School uniform', 'School bag', 'Semester progress report']
        ]
    ];
}

// Parse success stories if they exist
$successStories = [];
if (!empty($sponsorship['success_stories'])) {
    $successStories = is_string($sponsorship['success_stories']) ? json_decode($sponsorship['success_stories'], true) : $sponsorship['success_stories'];
    if (!is_array($successStories)) $successStories = [];
}
?>
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0 0 8px 0;">❤️ Sponsorship Management</h2>
            <p style="color: #6b7280; margin: 0;">Manage all sponsorship page content including CTA section, steps, and buttons.</p>
        </div>
        <button onclick="window.open('sponsorship.php', '_blank')" style="padding: 8px 16px; background: #4b5563; color: white; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <span>🔗</span> Preview
        </button>
    </div>
    
    <form method="POST" action="api/content/sponsorship.php" id="sponsorshipForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <!-- Basic Content Section -->
        <div style="background: #f9fafb; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">📄 Basic Content</h3>
            
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="title" class="form-input" 
                       value="<?php echo htmlspecialchars($sponsorship['title'] ?? 'Sponsorship'); ?>"
                       placeholder="Sponsorship">
            </div>
            
            <div class="form-group">
                <label class="form-label">Page Subtitle</label>
                <input type="text" name="subtitle" class="form-input" 
                       value="<?php echo htmlspecialchars($sponsorship['subtitle'] ?? 'Support Our Students'); ?>"
                       placeholder="Support Our Students">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="5" placeholder="Enter page description..."><?php echo htmlspecialchars($sponsorship['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hero Image</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <?php 
                    $heroImageUrl = normalizeImageUrl($sponsorship['hero_image'] ?? '');
                    if (!empty($heroImageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($heroImageUrl); ?>" 
                             alt="Hero image preview"
                             style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                             onerror="this.onerror=null; this.style.display='none';">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <input type="text" name="hero_image" id="sponsorship_hero_image" class="form-input" 
                               value="<?php echo htmlspecialchars($sponsorship['hero_image'] ?? ''); ?>" 
                               placeholder="Select image from media library" readonly>
                    </div>
                    <button type="button" onclick="openMediaLibrary('sponsorship_hero_image')" 
                            class="btn btn-primary">🖼️ Select Image</button>
                </div>
            </div>
        </div>
        
        <!-- CTA Section -->
        <div style="background: #fef2f2; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #fecaca;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">🎯 Call-to-Action Section</h3>
            
            <div class="form-group">
                <label class="form-label">CTA Title</label>
                <input type="text" name="cta_title" class="form-input" 
                       value="<?php echo htmlspecialchars($sponsorship['cta_title'] ?? 'Ready to Change a Life?'); ?>"
                       placeholder="Ready to Change a Life?">
            </div>
            
            <div class="form-group">
                <label class="form-label">CTA Subtitle</label>
                <textarea name="cta_subtitle" class="form-input" rows="3" placeholder="Join our sponsorship program today..."><?php echo htmlspecialchars($sponsorship['cta_subtitle'] ?? 'Join our sponsorship program today and be part of a child\'s journey to a brighter future.'); ?></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Apply Button Text</label>
                    <input type="text" name="apply_button_text" class="form-input" 
                           value="<?php echo htmlspecialchars($sponsorship['apply_button_text'] ?? 'Apply for Sponsorship'); ?>"
                           placeholder="Apply for Sponsorship">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sponsor Button Text</label>
                    <input type="text" name="sponsor_button_text" class="form-input" 
                           value="<?php echo htmlspecialchars($sponsorship['sponsor_button_text'] ?? 'Sponsor a Child'); ?>"
                           placeholder="Sponsor a Child">
                </div>
            </div>
        </div>
        
        <!-- Section Titles -->
        <div style="background: #fef3c7; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #fde68a;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">📝 Section Titles & Descriptions</h3>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 15px 0;">Transformations That Inspire Section</h4>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Section Title</label>
                    <input type="text" name="transformations_title" class="form-input" 
                           value="<?php echo htmlspecialchars($sponsorship['transformations_title'] ?? 'Transformations That Inspire'); ?>"
                           placeholder="Transformations That Inspire">
                </div>
                <div class="form-group">
                    <label class="form-label">Section Description</label>
                    <textarea name="transformations_subtitle" class="form-input" rows="2" 
                              placeholder="See the incredible impact..."><?php echo htmlspecialchars($sponsorship['transformations_subtitle'] ?? 'See the incredible impact of sponsorship through the eyes of our children. Every story is a testament to the power of education and hope.'); ?></textarea>
                </div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 15px 0;">Choose Your Impact Level Section</h4>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Section Title</label>
                    <input type="text" name="impact_level_title" class="form-input" 
                           value="<?php echo htmlspecialchars($sponsorship['impact_level_title'] ?? 'Choose Your Impact Level'); ?>"
                           placeholder="Choose Your Impact Level">
                </div>
                <div class="form-group">
                    <label class="form-label">Section Description</label>
                    <textarea name="impact_level_subtitle" class="form-input" rows="2" 
                              placeholder="Every contribution makes a difference..."><?php echo htmlspecialchars($sponsorship['impact_level_subtitle'] ?? 'Every contribution makes a difference. Choose the sponsorship level that fits your heart and budget.'); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Sponsorship Levels Section -->
        <div style="background: #fce7f3; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #fbcfe8;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">💰 Sponsorship Levels (3 Cards)</h3>
                <button type="button" onclick="addSponsorshipLevel()" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px;">➕ Add Level</button>
            </div>
            
            <div id="sponsorshipLevelsContainer">
                <?php foreach ($sponsorshipLevels as $index => $level): ?>
                    <div class="level-item" data-level-index="<?php echo $index; ?>" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Level <?php echo $index + 1; ?></h4>
                            <button type="button" onclick="removeSponsorshipLevel(<?php echo $index; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">🗑️ Remove</button>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                            <div class="form-group">
                                <label class="form-label">Level Name</label>
                                <input type="text" name="sponsorship_levels[<?php echo $index; ?>][name]" class="form-input" 
                                       value="<?php echo htmlspecialchars($level['name'] ?? ''); ?>" 
                                       placeholder="e.g., Full Scholarship">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Amount</label>
                                <input type="text" name="sponsorship_levels[<?php echo $index; ?>][amount]" class="form-input" 
                                       value="<?php echo htmlspecialchars($level['amount'] ?? ''); ?>" 
                                       placeholder="e.g., UGX 500,000">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label class="form-label">Description</label>
                            <textarea name="sponsorship_levels[<?php echo $index; ?>][description]" class="form-input" rows="2" 
                                      placeholder="Enter level description..."><?php echo htmlspecialchars($level['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Benefits (one per line)</label>
                            <textarea name="sponsorship_levels[<?php echo $index; ?>][benefits_text]" class="form-input" rows="4" 
                                      placeholder="Full tuition coverage&#10;Textbooks and supplies&#10;School uniform"><?php echo htmlspecialchars(isset($level['benefits']) && is_array($level['benefits']) ? implode("\n", $level['benefits']) : ($level['benefits'] ?? '')); ?></textarea>
                            <small style="color: #6b7280; font-size: 12px;">Enter each benefit on a new line</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Steps Section -->
        <div style="background: #f0f9ff; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #bae6fd;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">📋 How Your Sponsorship Works (4 Steps)</h3>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Section Title</label>
                    <input type="text" name="how_it_works_title" class="form-input" 
                           value="<?php echo htmlspecialchars($sponsorship['how_it_works_title'] ?? 'How Your Sponsorship Works'); ?>"
                           placeholder="How Your Sponsorship Works">
                </div>
                <div class="form-group">
                    <label class="form-label">Section Description</label>
                    <textarea name="how_it_works_subtitle" class="form-input" rows="2" 
                              placeholder="Our sponsorship process..."><?php echo htmlspecialchars($sponsorship['how_it_works_subtitle'] ?? 'Our sponsorship process is simple, transparent, and impactful. Here\'s how you can change a child\'s life.'); ?></textarea>
                </div>
            </div>
            
            <div id="sponsorshipStepsContainer">
                <?php foreach ($steps as $index => $step): ?>
                    <div class="step-item" data-step-index="<?php echo $index; ?>" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Step <?php echo $index + 1; ?></h4>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label class="form-label">Step Title</label>
                            <input type="text" name="sponsorship_steps[<?php echo $index; ?>][title]" class="form-input" 
                                   value="<?php echo htmlspecialchars($step['title'] ?? ''); ?>" 
                                   placeholder="e.g., Choose Your Child">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Step Description</label>
                            <textarea name="sponsorship_steps[<?php echo $index; ?>][description]" class="form-input" rows="2" 
                                      placeholder="Enter step description..."><?php echo htmlspecialchars($step['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Partnering Section -->
        <div style="background: #f0fdf4; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #bbf7d0;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">🤝 Partnering Section</h3>
            
            <div class="form-group">
                <label class="form-label">Partner Organization Link (Sukrop Foundation)</label>
                <input type="url" name="sukrop_link" class="form-input" 
                       value="<?php echo htmlspecialchars($sponsorship['sukrop_link'] ?? 'https://sukrop.org/'); ?>"
                       placeholder="https://sukrop.org/">
            </div>
        </div>
        
        <!-- Additional Slides -->
        <div style="background: #faf5ff; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e9d5ff;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">🖼️ Additional Slide Images</h3>
            <button type="button" onclick="openMediaLibrary('sponsorship_slides')" 
                    class="btn btn-primary" style="margin-bottom: 15px;">➕ Add Slide Image</button>
            <div id="sponsorshipSlidesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                <?php foreach ($additionalSlides as $index => $slide): ?>
                    <div style="position: relative; border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px;">
                        <?php $slideUrl = normalizeImageUrl($slide); ?>
                        <img src="<?php echo htmlspecialchars($slideUrl); ?>" 
                             alt="Slide image"
                             style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;"
                             onerror="this.onerror=null; this.style.display='none';">
                        <input type="hidden" name="additional_slides[]" value="<?php echo htmlspecialchars($slide); ?>">
                        <button type="button" onclick="this.parentElement.remove()" 
                                class="btn btn-danger" style="width: 100%; padding: 5px; font-size: 12px;">🗑️ Remove</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Success Stories Section -->
        <div style="background: #ecfdf5; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #86efac;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">🌟 Success Stories (Before & After)</h3>
                <button type="button" onclick="addSuccessStory()" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px;">➕ Add Story</button>
            </div>
            
            <p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">Show how sponsorship has transformed lives with before and after stories.</p>
            
            <div id="successStoriesContainer">
                <?php foreach ($successStories as $index => $story): ?>
                    <div class="story-item" data-story-index="<?php echo $index; ?>" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                            <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Story <?php echo $index + 1; ?></h4>
                            <button type="button" onclick="removeSuccessStory(<?php echo $index; ?>)" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">🗑️ Remove</button>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                            <div class="form-group">
                                <label class="form-label">Student Name</label>
                                <input type="text" name="success_stories[<?php echo $index; ?>][name]" class="form-input" 
                                       value="<?php echo htmlspecialchars($story['name'] ?? ''); ?>" 
                                       placeholder="e.g., Sarah M.">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="text" name="success_stories[<?php echo $index; ?>][age]" class="form-input" 
                                       value="<?php echo htmlspecialchars($story['age'] ?? ''); ?>" 
                                       placeholder="e.g., 12">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 15px;">
                            <!-- Before Section -->
                            <div style="background: #fef2f2; padding: 15px; border-radius: 8px; border: 1px solid #fecaca;">
                                <h5 style="font-size: 14px; font-weight: 600; color: #991b1b; margin: 0 0 15px 0;">📷 Before</h5>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label class="form-label">Before Image</label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php 
                                        $beforeImageUrl = normalizeImageUrl($story['before']['image'] ?? '');
                                        if (!empty($beforeImageUrl)): ?>
                                            <img src="<?php echo htmlspecialchars($beforeImageUrl); ?>" 
                                                 alt="Before image preview"
                                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #fecaca;"
                                                 onerror="this.onerror=null; this.style.display='none';">
                                        <?php endif; ?>
                                        <div style="flex: 1;">
                                            <input type="text" name="success_stories[<?php echo $index; ?>][before_image]" 
                                                   id="story_<?php echo $index; ?>_before_image" 
                                                   class="form-input" 
                                                   value="<?php echo htmlspecialchars($story['before']['image'] ?? ''); ?>" 
                                                   placeholder="Select image" readonly>
                                        </div>
                                        <button type="button" onclick="openMediaLibrary('story_<?php echo $index; ?>_before_image')" 
                                                class="btn btn-primary" style="padding: 8px 12px; font-size: 12px;">🖼️</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Before Description</label>
                                    <textarea name="success_stories[<?php echo $index; ?>][before_description]" class="form-input" rows="3" 
                                              placeholder="Describe the situation before sponsorship..."><?php echo htmlspecialchars($story['before']['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- After Section -->
                            <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; border: 1px solid #bbf7d0;">
                                <h5 style="font-size: 14px; font-weight: 600; color: #166534; margin: 0 0 15px 0;">✨ After</h5>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label class="form-label">After Image</label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php 
                                        $afterImageUrl = normalizeImageUrl($story['after']['image'] ?? '');
                                        if (!empty($afterImageUrl)): ?>
                                            <img src="<?php echo htmlspecialchars($afterImageUrl); ?>" 
                                                 alt="After image preview"
                                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #bbf7d0;"
                                                 onerror="this.onerror=null; this.style.display='none';">
                                        <?php endif; ?>
                                        <div style="flex: 1;">
                                            <input type="text" name="success_stories[<?php echo $index; ?>][after_image]" 
                                                   id="story_<?php echo $index; ?>_after_image" 
                                                   class="form-input" 
                                                   value="<?php echo htmlspecialchars($story['after']['image'] ?? ''); ?>" 
                                                   placeholder="Select image" readonly>
                                        </div>
                                        <button type="button" onclick="openMediaLibrary('story_<?php echo $index; ?>_after_image')" 
                                                class="btn btn-primary" style="padding: 8px 12px; font-size: 12px;">🖼️</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">After Description</label>
                                    <textarea name="success_stories[<?php echo $index; ?>][after_description]" class="form-input" rows="3" 
                                              placeholder="Describe the transformation after sponsorship..."><?php echo htmlspecialchars($story['after']['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Success Story / Testimonial</label>
                            <textarea name="success_stories[<?php echo $index; ?>][story]" class="form-input" rows="3" 
                                      placeholder="Tell the full story of transformation..."><?php echo htmlspecialchars($story['story'] ?? ''); ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Coordinator Info -->
        <div style="background: #fffbeb; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #fde68a;">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">📧 Coordinator Information</h3>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Coordinator Email</label>
                    <input type="email" name="coordinator_email" class="form-input" 
                           value="<?php echo htmlspecialchars($sponsorship['coordinator_email'] ?? ''); ?>"
                           placeholder="coordinator@school.com">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Coordinator Phone</label>
                    <input type="text" name="coordinator_phone" class="form-input" 
                           value="<?php echo htmlspecialchars($sponsorship['coordinator_phone'] ?? ''); ?>"
                           placeholder="+256 XXX XXX XXX">
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 30px; width: 100%; padding: 12px; font-size: 16px; font-weight: 600;">💾 Save All Changes</button>
    </form>
</div>

<script>
let sponsorshipSlides = <?php echo json_encode($additionalSlides); ?>;
if (typeof selectImage === 'function') {
    const originalSelectImage = selectImage;
    window.selectImage = function(imageUrl) {
        if (currentImageField === 'sponsorship_slides') {
            sponsorshipSlides.push(imageUrl);
            updateSponsorshipSlides();
            closeMediaLibrary();
        } else {
            originalSelectImage(imageUrl);
        }
    };
}
function updateSponsorshipSlides() {
    const container = document.getElementById('sponsorshipSlidesContainer');
    container.innerHTML = sponsorshipSlides.map((slide, idx) => `
        <div style="position: relative; border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px;">
            <img src="${slide}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;">
            <input type="hidden" name="additional_slides[]" value="${slide}">
            <button type="button" onclick="sponsorshipSlides.splice(${idx}, 1); updateSponsorshipSlides();" 
                    class="btn btn-danger" style="width: 100%; padding: 5px; font-size: 12px;">🗑️ Remove</button>
        </div>
    `).join('');
}

let sponsorshipLevelIndex = <?php echo count($sponsorshipLevels); ?>;

function addSponsorshipLevel() {
    const container = document.getElementById('sponsorshipLevelsContainer');
    const levelHtml = `
        <div class="level-item" data-level-index="${sponsorshipLevelIndex}" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Level ${sponsorshipLevelIndex + 1}</h4>
                <button type="button" onclick="removeSponsorshipLevel(${sponsorshipLevelIndex})" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">🗑️ Remove</button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label class="form-label">Level Name</label>
                    <input type="text" name="sponsorship_levels[${sponsorshipLevelIndex}][name]" class="form-input" 
                           placeholder="e.g., Full Scholarship">
                </div>
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="text" name="sponsorship_levels[${sponsorshipLevelIndex}][amount]" class="form-input" 
                           placeholder="e.g., UGX 500,000">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">Description</label>
                <textarea name="sponsorship_levels[${sponsorshipLevelIndex}][description]" class="form-input" rows="2" 
                          placeholder="Enter level description..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Benefits (one per line)</label>
                <textarea name="sponsorship_levels[${sponsorshipLevelIndex}][benefits_text]" class="form-input" rows="4" 
                          placeholder="Full tuition coverage&#10;Textbooks and supplies&#10;School uniform"></textarea>
                <small style="color: #6b7280; font-size: 12px;">Enter each benefit on a new line</small>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', levelHtml);
    sponsorshipLevelIndex++;
}

function removeSponsorshipLevel(index) {
    const levelItem = document.querySelector(`.level-item[data-level-index="${index}"]`);
    if (levelItem) {
        levelItem.remove();
    }
}

let successStoryIndex = <?php echo count($successStories); ?>;

function addSuccessStory() {
    const container = document.getElementById('successStoriesContainer');
    const storyHtml = `
        <div class="story-item" data-story-index="${successStoryIndex}" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">Story ${successStoryIndex + 1}</h4>
                <button type="button" onclick="removeSuccessStory(${successStoryIndex})" class="btn btn-danger" style="padding: 5px 15px; font-size: 12px;">🗑️ Remove</button>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label class="form-label">Student Name</label>
                    <input type="text" name="success_stories[${successStoryIndex}][name]" class="form-input" 
                           placeholder="e.g., Sarah M.">
                </div>
                <div class="form-group">
                    <label class="form-label">Age</label>
                    <input type="text" name="success_stories[${successStoryIndex}][age]" class="form-input" 
                           placeholder="e.g., 12">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 15px;">
                <!-- Before Section -->
                <div style="background: #fef2f2; padding: 15px; border-radius: 8px; border: 1px solid #fecaca;">
                    <h5 style="font-size: 14px; font-weight: 600; color: #991b1b; margin: 0 0 15px 0;">📷 Before</h5>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label class="form-label">Before Image</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1;">
                                <input type="text" name="success_stories[${successStoryIndex}][before_image]" 
                                       id="story_${successStoryIndex}_before_image" 
                                       class="form-input" 
                                       placeholder="Select image" readonly>
                            </div>
                            <button type="button" onclick="openMediaLibrary('story_${successStoryIndex}_before_image')" 
                                    class="btn btn-primary" style="padding: 8px 12px; font-size: 12px;">🖼️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Before Description</label>
                        <textarea name="success_stories[${successStoryIndex}][before_description]" class="form-input" rows="3" 
                                  placeholder="Describe the situation before sponsorship..."></textarea>
                    </div>
                </div>
                
                <!-- After Section -->
                <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; border: 1px solid #bbf7d0;">
                    <h5 style="font-size: 14px; font-weight: 600; color: #166534; margin: 0 0 15px 0;">✨ After</h5>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label class="form-label">After Image</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1;">
                                <input type="text" name="success_stories[${successStoryIndex}][after_image]" 
                                       id="story_${successStoryIndex}_after_image" 
                                       class="form-input" 
                                       placeholder="Select image" readonly>
                            </div>
                            <button type="button" onclick="openMediaLibrary('story_${successStoryIndex}_after_image')" 
                                    class="btn btn-primary" style="padding: 8px 12px; font-size: 12px;">🖼️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">After Description</label>
                        <textarea name="success_stories[${successStoryIndex}][after_description]" class="form-input" rows="3" 
                                  placeholder="Describe the transformation after sponsorship..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Success Story / Testimonial</label>
                <textarea name="success_stories[${successStoryIndex}][story]" class="form-input" rows="3" 
                          placeholder="Tell the full story of transformation..."></textarea>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', storyHtml);
    successStoryIndex++;
}

function removeSuccessStory(index) {
    const storyItem = document.querySelector(`.story-item[data-story-index="${index}"]`);
    if (storyItem) {
        storyItem.remove();
    }
}
</script>
