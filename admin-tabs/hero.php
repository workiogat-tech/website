<?php
/**
 * Hero Section Management - EXACT replica from images
 */
$hero = $db->fetchOne("SELECT * FROM hero_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];
$slides = [];
if (!empty($hero['slides'])) {
    $allSlides = is_string($hero['slides']) ? json_decode($hero['slides'], true) : $hero['slides'];
    if (!is_array($allSlides)) $allSlides = [];
    // Filter out empty slides and get ONLY unique slides (admin stores and shows unique only)
    // Frontend will duplicate them to create exactly 2 copies (1 original + 1 duplicate)
    $allFiltered = array_filter($allSlides, function($slide) {
        return !empty(trim($slide));
    });
    // Get unique slides only - preserve order by using first occurrence
    $uniqueSlides = [];
    $seen = [];
    foreach ($allFiltered as $slide) {
        $trimmed = trim($slide);
        if (!empty($trimmed) && !in_array($trimmed, $seen)) {
            $uniqueSlides[] = $trimmed;
            $seen[] = $trimmed;
        }
    }
    $slides = array_values($uniqueSlides);
}

// Get headline, subheadline, and description colors
$headlineColor = $hero['headline_color'] ?? $hero['headlineColor'] ?? '#ffffff';
$subHeadlineColor = $hero['subheadline_color'] ?? $hero['subHeadlineColor'] ?? '#ffffff';
$descriptionColor = $hero['description_color'] ?? $hero['descriptionColor'] ?? '#ffffff';
?>
<div class="content-card" style="padding: 0;">
    <div style="padding: 30px;">
        <h2 style="font-size: 28px; font-weight: bold; color: #111827; margin: 0 0 30px 0;">Hero Section Management</h2>
        
        <form method="POST" action="api/content/hero-content.php" id="heroForm" onsubmit="return handleAdminFormSubmit(event, this);">
            <!-- Main Headline -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Main Headline</label>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <input type="text" 
                           name="headline" 
                           id="hero_headline"
                           class="form-input" 
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                           value="<?php echo htmlspecialchars($hero['headline'] ?? $hero['title'] ?? 'Welcome to SJS'); ?>"
                           placeholder="Enter main headline"
                           onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                           onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                           oninput="updateHeadlinePreview()">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 12px; font-weight: 500; color: #6b7280; margin-bottom: 4px;">Headline Color</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="color" 
                                       id="headline_color_picker"
                                       name="headline_color" 
                                       style="width: 48px; height: 40px; padding: 0; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; flex-shrink: 0;"
                                       value="<?php echo htmlspecialchars($headlineColor); ?>"
                                       onchange="document.getElementById('headline_color_text').value=this.value; updateHeadlinePreview();">
                                <input type="text" 
                                       id="headline_color_text"
                                       name="headline_color_text" 
                                       class="form-input" 
                                       style="flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 12px; font-family: monospace;"
                                       value="<?php echo htmlspecialchars($headlineColor); ?>"
                                       onchange="document.getElementById('headline_color_picker').value=this.value; updateHeadlinePreview();"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       placeholder="#ffffff">
                            </div>
                        </div>
                        <div style="margin-top: 24px;">
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Preview</div>
                            <div id="headlinePreview" 
                                 style="padding: 12px 16px; border-radius: 8px; border: 1px solid #e5e7eb; background: #1a1a1a; color: <?php echo htmlspecialchars($headlineColor); ?>; font-size: 18px; font-weight: 600; min-width: 200px;">
                                <?php echo htmlspecialchars($hero['headline'] ?? $hero['title'] ?? 'Welcome to SJS'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sub Headline -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Sub Headline</label>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <input type="text" 
                           name="subheadline" 
                           id="hero_subheadline"
                           class="form-input" 
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                           value="<?php echo htmlspecialchars($hero['subheadline'] ?? $hero['subtitle'] ?? 'Empowering minds, shaping futures'); ?>"
                           placeholder="Enter sub headline"
                           onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                           onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                           oninput="updateSubHeadlinePreview()">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 12px; font-weight: 500; color: #6b7280; margin-bottom: 4px;">Sub Headline Color</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="color" 
                                       id="subheadline_color_picker"
                                       name="subheadline_color" 
                                       style="width: 48px; height: 40px; padding: 0; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; flex-shrink: 0;"
                                       value="<?php echo htmlspecialchars($subHeadlineColor); ?>"
                                       onchange="document.getElementById('subheadline_color_text').value=this.value; updateSubHeadlinePreview();">
                                <input type="text" 
                                       id="subheadline_color_text"
                                       name="subheadline_color_text" 
                                       class="form-input" 
                                       style="flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 12px; font-family: monospace;"
                                       value="<?php echo htmlspecialchars($subHeadlineColor); ?>"
                                       onchange="document.getElementById('subheadline_color_picker').value=this.value; updateSubHeadlinePreview();"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       placeholder="#ffffff">
                            </div>
                        </div>
                        <div style="margin-top: 24px;">
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Preview</div>
                            <div id="subheadlinePreview" 
                                 style="padding: 12px 16px; border-radius: 8px; border: 1px solid #e5e7eb; background: #1a1a1a; color: <?php echo htmlspecialchars($subHeadlineColor); ?>; font-size: 16px; font-weight: 500; min-width: 200px;">
                                <?php echo htmlspecialchars($hero['subheadline'] ?? $hero['subtitle'] ?? 'Empowering minds, shaping futures'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Description</label>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <textarea name="description" 
                              id="hero_description"
                              class="form-input" 
                              rows="3"
                              style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical; transition: all 0.2s;"
                              placeholder="We focus on academic excellence, moral values, and holistic development for every child."
                              onfocus="this.style.borderColor='#9333ea'; this.style.boxShadow='0 0 0 3px rgba(147, 51, 234, 0.1)'"
                              onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"><?php echo htmlspecialchars($hero['description'] ?? 'We focus on academic excellence, moral values, and holistic development for every child.'); ?></textarea>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 12px; font-weight: 500; color: #6b7280; margin-bottom: 4px;">Description Color</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="color" 
                                       id="description_color_picker"
                                       name="description_color" 
                                       style="width: 48px; height: 40px; padding: 0; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; flex-shrink: 0;"
                                       value="<?php echo htmlspecialchars($descriptionColor); ?>"
                                       onchange="document.getElementById('description_color_text').value=this.value; updateDescriptionPreview();">
                                <input type="text" 
                                       id="description_color_text"
                                       name="description_color_text" 
                                       class="form-input" 
                                       style="flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 12px; font-family: monospace;"
                                       value="<?php echo htmlspecialchars($descriptionColor); ?>"
                                       onchange="document.getElementById('description_color_picker').value=this.value; updateDescriptionPreview();"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       placeholder="#ffffff">
                            </div>
                        </div>
                        <div style="margin-top: 24px;">
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Preview</div>
                            <div id="descriptionPreview" 
                                 style="padding: 12px 16px; border-radius: 8px; border: 1px solid #e5e7eb; background: #1a1a1a; color: <?php echo htmlspecialchars($descriptionColor); ?>; font-size: 14px; font-weight: 400; min-width: 200px; max-width: 300px;">
                                <?php echo htmlspecialchars($hero['description'] ?? 'We focus on academic excellence, moral values, and holistic development for every child.'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Background Image -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Background Image</label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="flex: 1;">
                        <input type="text" 
                               name="background_image" 
                               id="hero_background" 
                               class="form-input" 
                               style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #f9fafb; color: #6b7280;"
                               value="<?php echo htmlspecialchars($hero['background_image'] ?? ''); ?>" 
                               placeholder="Select image from media library"
                               readonly>
                    </div>
                    <button type="button" 
                            onclick="openMediaLibrary('hero_background')" 
                            style="width: 48px; height: 48px; background: #2563eb; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; flex-shrink: 0;"
                            onmouseover="this.style.background='#1d4ed8'"
                            onmouseout="this.style.background='#2563eb'">
                        <span style="font-size: 20px; color: white;">🖼️</span>
                    </button>
                </div>
            </div>
            
            <!-- Slide Images -->
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151;">Slide Images</label>
                    <span id="slidesCountInfo" style="font-size: 12px; color: #6b7280; font-style: italic;"></span>
                </div>
                <button type="button" 
                        onclick="openMediaLibrary('hero_slides')" 
                        style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; margin-bottom: 16px; transition: background 0.2s;"
                        onmouseover="this.style.background='#1d4ed8'"
                        onmouseout="this.style.background='#2563eb'">
                    ➕ Add From Media Library
                </button>
                <div id="heroSlidesContainer" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 12px;">
                    <?php if (!empty($slides) && count($slides) > 0): ?>
                        <?php 
                        // Show ONLY unique slides in admin panel (duplication happens on frontend)
                        $uniqueSlides = array_unique($slides);
                        $uniqueSlides = array_values(array_filter($uniqueSlides, function($slide) {
                            return !empty(trim($slide));
                        }));
                        foreach ($uniqueSlides as $index => $slide): 
                            $slideUrl = normalizeImageUrl($slide);
                            if (empty($slideUrl) && !empty($slide)) {
                                $slideUrl = $slide;
                            }
                        ?>
                            <div style="position: relative; border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #f9fafb; min-height: 150px;" data-slide-url="<?php echo htmlspecialchars($slide); ?>">
                                <?php if (!empty($slideUrl)): ?>
                                    <img src="<?php echo htmlspecialchars($slideUrl); ?>" 
                                         alt="Slide <?php echo $index + 1; ?>"
                                         style="width: 100%; height: 150px; object-fit: cover; display: block;"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div style="display: <?php echo empty($slideUrl) ? 'flex' : 'none'; ?>; width: 100%; height: 150px; background: #f3f4f6; align-items: center; justify-content: center; color: #9ca3af;">
                                    <span style="font-size: 32px;">🖼️</span>
                                </div>
                                <button type="button" 
                                        onclick="removeSlide(this)" 
                                        style="position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; background: rgba(220, 38, 38, 0.9); color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: all 0.2s; z-index: 10;"
                                        onmouseover="this.style.background='rgba(220, 38, 38, 1)'; this.style.transform='scale(1.1)'"
                                        onmouseout="this.style.background='rgba(220, 38, 38, 0.9)'; this.style.transform='scale(1)'">
                                    ✕
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="heroSlidesMessage" style="display: <?php echo (empty($slides) || count($slides) === 0) ? 'block' : 'none'; ?>;">
                    <p style="color: #9ca3af; font-size: 14px; margin-top: 12px; font-style: italic;">No slide images added yet. Click "Add From Media Library" to add images.</p>
                </div>
            </div>
            
            <!-- Slide Settings -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px;">
                <div class="form-group">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Slide Interval (milliseconds)</label>
                    <input type="number" 
                           name="slide_interval_ms" 
                           class="form-input" 
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;"
                           value="<?php echo htmlspecialchars($hero['slide_interval_ms'] ?? 5000); ?>"
                           min="1000" 
                           step="500">
                </div>
                
                <div class="form-group">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Slide Direction</label>
                    <select name="slide_direction" 
                            class="form-input" 
                            style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="left" <?php echo ($hero['slide_direction'] ?? 'left') === 'left' ? 'selected' : ''; ?>>Left</option>
                        <option value="right" <?php echo ($hero['slide_direction'] ?? 'left') === 'right' ? 'selected' : ''; ?>>Right</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Slide Mode</label>
                    <select name="slide_mode" 
                            class="form-input" 
                            style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="marquee" <?php echo ($hero['slide_mode'] ?? 'marquee') === 'marquee' ? 'selected' : ''; ?>>Continuous (M</option>
                        <option value="fade" <?php echo ($hero['slide_mode'] ?? 'marquee') === 'fade' ? 'selected' : ''; ?>>Fade</option>
                    </select>
                </div>
            </div>
            
            <!-- Image Display Settings -->
            <div style="background: #f9fafb; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Image Display Settings</h3>
                
                <!-- Background Size -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Background Size</label>
                    <select name="background_size" 
                            id="background_size"
                            class="form-input" 
                            style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="contain" <?php echo ($hero['background_size'] ?? 'contain') === 'contain' ? 'selected' : ''; ?>>Contain (Show Full Image)</option>
                        <option value="cover" <?php echo ($hero['background_size'] ?? 'contain') === 'cover' ? 'selected' : ''; ?>>Cover (Fill Container)</option>
                        <option value="auto" <?php echo ($hero['background_size'] ?? 'contain') === 'auto' ? 'selected' : ''; ?>>Auto</option>
                    </select>
                    <p style="font-size: 12px; color: #6b7280; margin-top: 6px; margin: 0;">
                        "Contain" shows the full image without cropping. "Cover" fills the container but may crop edges.
                    </p>
                </div>
                
                <!-- Background Position -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Background Position</label>
                    <select name="background_position" 
                            id="background_position"
                            class="form-input" 
                            style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white; cursor: pointer;">
                        <option value="center" <?php echo ($hero['background_position'] ?? 'center') === 'center' ? 'selected' : ''; ?>>Center</option>
                        <option value="top" <?php echo ($hero['background_position'] ?? 'center') === 'top' ? 'selected' : ''; ?>>Top</option>
                        <option value="bottom" <?php echo ($hero['background_position'] ?? 'center') === 'bottom' ? 'selected' : ''; ?>>Bottom</option>
                        <option value="left" <?php echo ($hero['background_position'] ?? 'center') === 'left' ? 'selected' : ''; ?>>Left</option>
                        <option value="right" <?php echo ($hero['background_position'] ?? 'center') === 'right' ? 'selected' : ''; ?>>Right</option>
                    </select>
                    <p style="font-size: 12px; color: #6b7280; margin-top: 6px; margin: 0;">
                        Controls where the image is positioned within the container.
                    </p>
                </div>
                
                <!-- Image Scale -->
                <div>
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Image Scale: <span id="imageScaleValue"><?php echo htmlspecialchars($hero['image_scale'] ?? $hero['imageScale'] ?? 1); ?>x</span>
                    </label>
                    <div style="position: relative;">
                        <input type="range" 
                               name="image_scale" 
                               id="image_scale"
                               min="0.5" 
                               max="2" 
                               step="0.1"
                               value="<?php echo htmlspecialchars($hero['image_scale'] ?? $hero['imageScale'] ?? 1); ?>"
                               style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; outline: none; cursor: pointer;"
                               oninput="document.getElementById('imageScaleValue').textContent=this.value+'x';"
                               onchange="document.getElementById('imageScaleValue').textContent=this.value+'x';">
                        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #6b7280;">
                            <span>0.5x (Smaller)</span>
                            <span>1x (Normal)</span>
                            <span>2x (Larger)</span>
                        </div>
                    </div>
                    <p style="font-size: 12px; color: #6b7280; margin-top: 6px; margin: 0;">
                        Adjust the zoom level of the image. Use this to make images larger or smaller.
                    </p>
                </div>
            </div>
            
            <!-- Live Preview Section -->
            <div style="background: #f9fafb; border-radius: 12px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">🎬 Live Slider Preview</h3>
                <div style="position: relative; width: 100%; height: 300px; border-radius: 8px; overflow: hidden; background: #1a1a1a; border: 2px solid #e5e7eb;">
                    <div id="heroSliderPreview" style="position: absolute; inset: 0; overflow: hidden;">
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #9ca3af; font-size: 14px;">
                            Add slide images to see preview
                        </div>
                    </div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 20px; color: white; pointer-events: none;">
                        <div style="font-size: 16px; font-weight: 600;" id="previewHeadline"><?php echo htmlspecialchars($hero['headline'] ?? $hero['title'] ?? 'Welcome to SJS'); ?></div>
                        <div style="font-size: 14px; margin-top: 4px; opacity: 0.9;" id="previewSubheadline"><?php echo htmlspecialchars($hero['subheadline'] ?? $hero['subtitle'] ?? 'Empowering minds, shaping futures'); ?></div>
                    </div>
                </div>
                <p style="font-size: 12px; color: #6b7280; margin-top: 12px; margin: 0;">
                    This preview shows how the slider will appear on the homepage. The slider uses 3 copies for seamless infinite scrolling.
                </p>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: flex; gap: 12px; margin-top: 30px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="submit" 
                        style="padding: 12px 24px; background: linear-gradient(135deg, #9333ea, #7e22ce); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(147, 51, 234, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span>💾</span> Save Changes
                </button>
                <button type="button" 
                        onclick="window.open('index.php', '_blank')"
                        style="padding: 12px 24px; background: #4b5563; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                        onmouseover="this.style.background='#374151'; this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.background='#4b5563'; this.style.transform='translateY(0)'">
                    <span>👁️</span> Preview
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let heroSlides = <?php echo json_encode($slides ?? []); ?>;
if (!Array.isArray(heroSlides)) {
    heroSlides = [];
}

// Store ONLY unique slides in admin panel (frontend creates exactly 2 copies)
// Get unique slides and keep only unique (no duplication in admin)
const uniqueSlides = [...new Set(heroSlides.filter(s => s && s.trim()))];
heroSlides = uniqueSlides; // Admin only stores unique slides - frontend will duplicate to create 2 copies (1 original + 1 duplicate)

function updateHeadlinePreview() {
    const headline = document.getElementById('hero_headline').value || 'Welcome to SJS';
    const color = document.getElementById('headline_color_text').value || '#ffffff';
    const preview = document.getElementById('headlinePreview');
    preview.textContent = headline;
    preview.style.color = color;
}

function updateSubHeadlinePreview() {
    const subheadline = document.getElementById('hero_subheadline').value || 'Empowering minds, shaping futures';
    const color = document.getElementById('subheadline_color_text').value || '#ffffff';
    const preview = document.getElementById('subheadlinePreview');
    preview.textContent = subheadline;
    preview.style.color = color;
}

function updateDescriptionPreview() {
    const description = document.getElementById('hero_description').value || 'We focus on academic excellence, moral values, and holistic development for every child.';
    const color = document.getElementById('description_color_text').value || '#ffffff';
    const preview = document.getElementById('descriptionPreview');
    if (preview) {
        preview.textContent = description;
        preview.style.color = color;
    }
}

function removeSlide(button) {
    const container = button.closest('div');
    if (!container) return;
    
    // Get slide URL from data attribute
    const slideUrl = container.getAttribute('data-slide-url');
    if (!slideUrl) return;
    
    // Remove the slide (admin only stores unique slides)
    heroSlides = heroSlides.filter(url => url !== slideUrl);
    updateSlidesDisplay();
}

function removeSlideAtIndex(button, index) {
    // Remove slide at specific index (admin only stores unique slides)
    if (index >= 0 && index < heroSlides.length) {
        const slideUrl = heroSlides[index];
        // Remove this unique slide
        heroSlides = heroSlides.filter(url => url !== slideUrl);
        updateSlidesDisplay();
    }
}

// Handle media library selection for slides
if (typeof selectImageFromLibrary === 'undefined') {
    window.selectImageFromLibrary = function(imageUrl) {
        if (currentImageField === 'hero_slides') {
            // Add new image (only if not already present - keep unique only)
            // Frontend will create exactly 2 copies (1 original + 1 duplicate)
            if (!heroSlides.includes(imageUrl)) {
                heroSlides.push(imageUrl);
            }
            updateSlidesDisplay();
        } else {
            // Set single image field
            const field = document.getElementById(currentImageField);
            if (field) {
                field.value = imageUrl;
                // Trigger change event to update preview if needed
                field.dispatchEvent(new Event('change'));
            }
        }
        if (typeof closeMediaLibrary === 'function') {
            closeMediaLibrary();
        }
    };
}

// Override selectImage function from admin.php to handle hero slides duplication
const originalSelectImage = window.selectImage;
window.selectImage = function(imageUrl) {
    if (currentImageField === 'hero_slides') {
        // Add new image (only if not already present - keep unique only)
        // Frontend will create exactly 2 copies (1 original + 1 duplicate)
        if (!heroSlides.includes(imageUrl)) {
            heroSlides.push(imageUrl);
        }
        updateSlidesDisplay();
        if (typeof closeMediaLibrary === 'function') {
            closeMediaLibrary();
        }
    } else {
        // Use original function for other fields
        if (originalSelectImage) {
            originalSelectImage(imageUrl);
        } else {
            const field = document.getElementById(currentImageField);
            if (field) {
                field.value = imageUrl;
                field.dispatchEvent(new Event('change'));
            }
            if (typeof closeMediaLibrary === 'function') {
                closeMediaLibrary();
            }
        }
    }
};

function updateSlidesDisplay() {
    const container = document.getElementById('heroSlidesContainer');
    const messageDiv = document.getElementById('heroSlidesMessage');
    const countInfo = document.getElementById('slidesCountInfo');
    if (!container) return;
    
    // Filter out empty slides and get ONLY unique slides
    const validSlides = heroSlides.filter(s => s && s.trim());
    const uniqueSlides = [...new Set(validSlides)];
    
    // Update count info - show that frontend will create 3 copies (matching React version)
    if (countInfo) {
        if (uniqueSlides.length > 0) {
            const totalOnFrontend = uniqueSlides.length * 3; // Frontend creates 3 copies for seamless loop
            const pattern = uniqueSlides.length === 1 ? 'AAA' : 
                           uniqueSlides.length === 2 ? 'ABABAB' : 
                           uniqueSlides.length === 3 ? 'ABCABCABC' : 
                           `${uniqueSlides.length} images × 3`;
            countInfo.textContent = `${uniqueSlides.length} unique image(s) → ${totalOnFrontend} total on frontend (pattern: ${pattern})`;
            countInfo.style.color = '#10b981';
        } else {
            countInfo.textContent = '';
        }
    }
    
    // Update preview slider
    updateSliderPreview();
    
    container.innerHTML = '';
    
    if (uniqueSlides.length === 0) {
        if (messageDiv) messageDiv.style.display = 'block';
        return;
    }
    
    if (messageDiv) messageDiv.style.display = 'none';
    
    // Display ONLY unique slides (admin stores unique only, frontend duplicates them)
    uniqueSlides.forEach((slide, index) => {
        const div = document.createElement('div');
        div.style.cssText = 'position: relative; border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #f9fafb; min-height: 150px;';
        div.setAttribute('data-slide-url', slide);
        div.setAttribute('data-slide-index', index);
        div.innerHTML = `
            <img src="${slide}" alt="Slide ${index + 1}" style="width: 100%; height: 150px; object-fit: cover; display: block;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div style="display: none; width: 100%; height: 150px; background: #f3f4f6; align-items: center; justify-content: center; color: #9ca3af;">
                <span style="font-size: 32px;">🖼️</span>
            </div>
            <button type="button" onclick="removeSlide(this)" style="position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; background: rgba(220, 38, 38, 0.9); color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; z-index: 10; transition: all 0.2s;" onmouseover="this.style.background='rgba(220, 38, 38, 1)'; this.style.transform='scale(1.1)'" onmouseout="this.style.background='rgba(220, 38, 38, 0.9)'; this.style.transform='scale(1)'">✕</button>
        `;
        container.appendChild(div);
    });
}

// Update slider preview in admin panel
function updateSliderPreview() {
    const previewContainer = document.getElementById('heroSliderPreview');
    if (!previewContainer) return;
    
    const validSlides = heroSlides.filter(s => s && s.trim());
    const uniqueSlides = [...new Set(validSlides)];
    
    if (uniqueSlides.length === 0) {
        previewContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #9ca3af; font-size: 14px;">Add slide images to see preview</div>';
        return;
    }
    
    // Create 3 copies for seamless loop (matching React version)
    const copies = 3;
    const duplicatedSlides = [];
    for (let cycle = 0; cycle < copies; cycle++) {
        uniqueSlides.forEach(slide => duplicatedSlides.push(slide));
    }
    
    // Get settings
    const slideMode = document.querySelector('select[name="slide_mode"]')?.value || 'marquee';
    const slideDirection = document.querySelector('select[name="slide_direction"]')?.value || 'left';
    const slideInterval = parseInt(document.querySelector('input[name="slide_interval_ms"]')?.value || 5000);
    const backgroundSize = document.querySelector('select[name="background_size"]')?.value || 'contain';
    const backgroundPosition = document.querySelector('select[name="background_position"]')?.value || 'center';
    const imageScale = parseFloat(document.querySelector('input[name="image_scale"]')?.value || 1);
    
    if (slideMode === 'marquee') {
        // Calculate duration (matching React version)
        const marqueeDuration = Math.max(20, (slideInterval / 1000) * uniqueSlides.length * copies);
        
        // Create marquee slider
        const marquee = document.createElement('div');
        marquee.style.cssText = `
            display: flex;
            height: 100%;
            width: ${duplicatedSlides.length * 100}%;
            animation: ${slideDirection === 'right' ? 'previewMarqueeRight' : 'previewMarquee'} ${marqueeDuration}s linear infinite;
            will-change: transform;
            margin: 0;
            padding: 0;
            gap: 0;
        `;
        
        duplicatedSlides.forEach((slide, index) => {
            const slideDiv = document.createElement('div');
            const slideWidthPercent = (100 / duplicatedSlides.length);
            slideDiv.style.cssText = `
                flex-shrink: 0;
                width: ${slideWidthPercent}%;
                height: 100%;
                background-image: url('${slide}');
                background-size: ${imageScale !== 1 ? `${imageScale * 100}%` : (backgroundSize || 'contain')};
                background-position: ${backgroundPosition};
                background-repeat: no-repeat;
                margin: 0;
                padding: 0;
            `;
            marquee.appendChild(slideDiv);
        });
        
        previewContainer.innerHTML = '';
        previewContainer.appendChild(marquee);
        
        // Add keyframes if not already added
        if (!document.getElementById('previewSliderStyles')) {
            const style = document.createElement('style');
            style.id = 'previewSliderStyles';
            style.textContent = `
                @keyframes previewMarquee {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-33.333%); }
                }
                @keyframes previewMarqueeRight {
                    0% { transform: translateX(-33.333%); }
                    100% { transform: translateX(0); }
                }
            `;
            document.head.appendChild(style);
        }
    } else {
        // Fade mode
        previewContainer.innerHTML = '';
        uniqueSlides.forEach((slide, index) => {
            const slideDiv = document.createElement('div');
            slideDiv.style.cssText = `
                position: absolute;
                inset: 0;
                background-image: url('${slide}');
                background-size: ${imageScale !== 1 ? `${imageScale * 100}%` : backgroundSize};
                background-position: ${backgroundPosition};
                background-repeat: no-repeat;
                opacity: ${index === 0 ? '1' : '0'};
                transition: opacity 0.7s ease-in-out;
            `;
            previewContainer.appendChild(slideDiv);
        });
        
        // Auto-advance fade slides
        let currentFadeIndex = 0;
        setInterval(() => {
            const fadeSlides = previewContainer.querySelectorAll('div');
            if (fadeSlides.length > 1) {
                fadeSlides[currentFadeIndex].style.opacity = '0';
                currentFadeIndex = (currentFadeIndex + 1) % fadeSlides.length;
                fadeSlides[currentFadeIndex].style.opacity = '1';
            }
        }, slideInterval);
    }
}

// Update preview when headline/subheadline changes
function updateHeadlinePreview() {
    const headline = document.getElementById('hero_headline').value || 'Welcome to SJS';
    const color = document.getElementById('headline_color_text').value || '#ffffff';
    const preview = document.getElementById('headlinePreview');
    preview.textContent = headline;
    preview.style.color = color;
    
    // Update preview slider headline
    const previewHeadline = document.getElementById('previewHeadline');
    if (previewHeadline) {
        previewHeadline.textContent = headline;
        previewHeadline.style.color = color;
    }
}

function updateSubHeadlinePreview() {
    const subheadline = document.getElementById('hero_subheadline').value || 'Empowering minds, shaping futures';
    const color = document.getElementById('subheadline_color_text').value || '#ffffff';
    const preview = document.getElementById('subheadlinePreview');
    preview.textContent = subheadline;
    preview.style.color = color;
    
    // Update preview slider subheadline
    const previewSubheadline = document.getElementById('previewSubheadline');
    if (previewSubheadline) {
        previewSubheadline.textContent = subheadline;
        previewSubheadline.style.color = color;
    }
}

// Ensure slides are properly included in form submission
document.addEventListener('DOMContentLoaded', function() {
    updateHeadlinePreview();
    updateSubHeadlinePreview();
    updateDescriptionPreview();
    
    // Initialize slides display with current slides
    updateSlidesDisplay();
    
    // Update preview when settings change
    document.querySelectorAll('select[name="slide_mode"], select[name="slide_direction"], input[name="slide_interval_ms"], select[name="background_size"], select[name="background_position"], input[name="image_scale"]').forEach(input => {
        input.addEventListener('change', updateSliderPreview);
        input.addEventListener('input', updateSliderPreview);
    });
    
    // Override saveFormSilently for hero form to explicitly include all slides
    const heroForm = document.getElementById('heroForm');
    if (heroForm && typeof window.saveFormSilently === 'function') {
        const originalSaveFormSilently = window.saveFormSilently;
        
        window.saveFormSilently = function(form) {
            if (form && form.id === 'heroForm') {
                // For hero form, ensure all slides (including duplicates) are included
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.textContent : '';
                const originalBg = submitBtn ? submitBtn.style.background : '';
                
                if (submitBtn) {
                    submitBtn.textContent = '💾 Saving...';
                    submitBtn.style.opacity = '0.7';
                    submitBtn.disabled = true;
                }
                
                // Convert form data to object
                const formData = new FormData(form);
                const data = {};
                
                // First, collect all form data including text fields
                formData.forEach((value, key) => {
                    if (key.endsWith('[]')) {
                        const arrayKey = key.slice(0, -2);
                        if (!data[arrayKey]) {
                            data[arrayKey] = [];
                        }
                        data[arrayKey].push(value);
                    } else {
                        // Include ALL fields, we'll handle color fields specially below
                        data[key] = value;
                    }
                });
                
                // CRITICAL: Store ONLY unique slides (frontend will create exactly 3 copies for seamless loop)
                const validSlides = heroSlides.filter(s => s && s.trim());
                const uniqueSlides = [...new Set(validSlides)]; // Ensure only unique slides
                if (uniqueSlides.length > 0) {
                    data.slides = uniqueSlides; // Store unique slides only - frontend duplicates to 3 copies
                }
                
                // CRITICAL: Sync color picker values - ALWAYS use the actual color value
                // This ensures colors are saved even if the picker or text field is empty
                const colorFields = [
                    { picker: 'headline_color', text: 'headline_color_text' },
                    { picker: 'subheadline_color', text: 'subheadline_color_text' },
                    { picker: 'description_color', text: 'description_color_text' }
                ];
                
                colorFields.forEach(field => {
                    // Find inputs by both name and id
                    const picker = form.querySelector(`input[name="${field.picker}"], input#${field.picker.replace(/_/g, '_')}`);
                    const text = form.querySelector(`input[name="${field.text}"], input#${field.text.replace(/_/g, '_')}`);
                    
                    // Get the actual color value - prefer picker, fallback to text, fallback to what's already in data
                    let colorValue = data[field.picker] || '#ffffff'; // Start with existing value or default
                    
                    // Try picker first (type="color" input)
                    if (picker) {
                        const pickerValue = picker.value;
                        if (pickerValue && pickerValue.trim() && /^#[0-9A-Fa-f]{6}$/.test(pickerValue.trim())) {
                            colorValue = pickerValue.trim();
                        }
                    }
                    
                    // If picker didn't have a valid value, try text field
                    if (text && (!colorValue || colorValue === '#ffffff')) {
                        const textValue = text.value;
                        if (textValue && textValue.trim() && /^#[0-9A-Fa-f]{6}$/.test(textValue.trim())) {
                            colorValue = textValue.trim();
                        }
                    }
                    
                    // Validate hex color format - if still invalid, keep default
                    if (!/^#[0-9A-Fa-f]{6}$/.test(colorValue)) {
                        colorValue = '#ffffff'; // Default to white if invalid
                    }
                    
                    // ALWAYS set the main color field (not the _text field)
                    data[field.picker] = colorValue;
                    
                    // Remove the _text field from data (we only send the main color field)
                    delete data[field.text];
                    
                    // Sync the picker and text field values to match what we're sending
                    if (picker) {
                        picker.value = colorValue;
                    }
                    if (text) {
                        text.value = colorValue;
                    }
                });
                
                // Send AJAX request
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.error || 'Network response was not ok');
                        }).catch(() => {
                            throw new Error('Network response was not ok (Status: ' + response.status + ')');
                        });
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.error) {
                        console.error('Save error:', result.error);
                        // Suppress "Fatal error" messages
                        if (!result.error.toLowerCase().includes('fatal error') && !result.error.toLowerCase().includes('server error: fatal')) {
                            if (typeof showToast === 'function') {
                                showToast(result.error || 'Failed to save. Please try again.', 'error');
                            }
                        }
                        if (submitBtn) {
                            submitBtn.textContent = '❌ Error';
                            submitBtn.style.opacity = '1';
                            submitBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                            submitBtn.disabled = false;
                            setTimeout(() => {
                                submitBtn.textContent = originalText;
                                submitBtn.style.background = originalBg;
                            }, 4000);
                        }
                    } else {
                        const successMsg = result.message || 'Hero content saved successfully!';
                        if (typeof showToast === 'function') {
                            showToast(successMsg, 'success');
                        }
                        if (submitBtn) {
                            submitBtn.textContent = '✅ Saved';
                            submitBtn.style.opacity = '1';
                            submitBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                            submitBtn.disabled = false;
                            setTimeout(() => {
                                submitBtn.textContent = originalText;
                                submitBtn.style.background = originalBg;
                            }, 4000);
                        }
                        
                        // CRITICAL: Reload page after 1 second to show saved colors
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    // Suppress fatal error messages
                    if (error.message && (error.message.toLowerCase().includes('fatal error') || error.message.toLowerCase().includes('server error: fatal'))) {
                        // Don't show toast for fatal errors
                        return;
                    }
                    let errorMessage = 'Network error. Please check your connection and try again.';
                    if (error.message && error.message.includes('500')) {
                        errorMessage = 'An error occurred. Please try again later.';
                    }
                    if (typeof showToast === 'function') {
                        showToast(errorMessage, 'error');
                    }
                    if (submitBtn) {
                        submitBtn.textContent = '❌ Error';
                        submitBtn.style.opacity = '1';
                        submitBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                        submitBtn.disabled = false;
                        setTimeout(() => {
                            submitBtn.textContent = originalText;
                            submitBtn.style.background = originalBg;
                        }, 4000);
                    }
                });
            } else {
                // Use original function for other forms
                return originalSaveFormSilently(form);
            }
        };
    }
});
</script>
