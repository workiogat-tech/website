<?php
/**
 * Website Colors Tab
 */
$colors = $db->fetchAll("SELECT * FROM website_colors WHERE is_active = 1");
$colorsMap = [];
foreach ($colors as $color) {
    $colorsMap[$color['section_name']] = $color;
}

$sections = [
    ['name' => 'header', 'label' => 'Header'],
    ['name' => 'footer', 'label' => 'Footer'],
    ['name' => 'buttons', 'label' => 'Buttons'],
    ['name' => 'hero', 'label' => 'Hero Section'],
    ['name' => 'about-section', 'label' => 'About Section (Homepage)'],
    ['name' => 'school-life', 'label' => 'School Life Section'],
    ['name' => 'academics', 'label' => 'Academics Section'],
    ['name' => 'sports', 'label' => 'Sports Section'],
    ['name' => 'vision-mission', 'label' => 'Vision & Mission Section'],
    ['name' => 'core-values', 'label' => 'Core Values Section'],
    ['name' => 'why-choose', 'label' => 'Why Choose Section'],
    ['name' => 'join-section', 'label' => 'Join Section'],
    ['name' => 'index', 'label' => 'Homepage (Index)'],
    ['name' => 'about', 'label' => 'About Page'],
    ['name' => 'activities', 'label' => 'Activities Page'],
    ['name' => 'admission', 'label' => 'Admission Page'],
    ['name' => 'announcements', 'label' => 'Announcements Page'],
    ['name' => 'clubs', 'label' => 'Clubs Page'],
    ['name' => 'contact', 'label' => 'Contact Page'],
    ['name' => 'curriculum', 'label' => 'Curriculum Page'],
    ['name' => 'events', 'label' => 'Events Page'],
    ['name' => 'facilities', 'label' => 'Facilities Page'],
    ['name' => 'gallery', 'label' => 'Gallery Page'],
    ['name' => 'give-back', 'label' => 'Give Back Page'],
    ['name' => 'news', 'label' => 'News Page'],
    ['name' => 'parents', 'label' => 'Parents Portal Page'],
    ['name' => 'people-behind-school', 'label' => 'People Behind School Page'],
    ['name' => 'resources', 'label' => 'Resources Page'],
    ['name' => 'school-background', 'label' => 'School Background Page'],
    ['name' => 'sponsorship', 'label' => 'Sponsorship Page'],
    ['name' => 'teachers', 'label' => 'Teachers Page'],
    ['name' => 'testimonials', 'label' => 'Testimonials Page'],
];
?>
<div class="content-card">
    <h2>🎨 Website Colors</h2>
    <p style="margin-bottom: 20px; color: #666;">Customize colors for different sections of your website. Changes save automatically and reflect immediately.</p>
    
    <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin-bottom: 30px; border-radius: 4px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #1976d2;">📝 How to Edit Colors:</h3>
        <ol style="margin: 0; padding-left: 20px; color: #555;">
            <li style="margin-bottom: 8px;"><strong>Click on any color input field</strong> below to open the color picker</li>
            <li style="margin-bottom: 8px;"><strong>Select your desired color</strong> using the color picker, or type a hex code (e.g., #ffffff for white)</li>
            <li style="margin-bottom: 8px;"><strong>Click "💾 Save All Colors"</strong> at the bottom to apply changes</li>
            <li style="margin-bottom: 8px;">Changes will <strong>save automatically</strong> and appear immediately on your website</li>
        </ol>
    </div>
    
    <form method="POST" action="api/content/website-colors.php" id="colorsForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <div style="margin-bottom: 30px; padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px;">
            <h3 style="margin: 0 0 15px 0; color: #856404; font-size: 18px;">Total Sections: <?php echo count($sections); ?></h3>
            <p style="margin: 0; color: #856404; font-weight: bold;">All sections are displayed below. Scroll down to see them all.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; margin-bottom: 40px;">
            <?php 
            $sectionCount = 0;
            foreach ($sections as $section): 
                $sectionCount++;
                $colorData = $colorsMap[$section['name']] ?? [];
            ?>
                <div style="background: #ffffff; padding: 20px; border-radius: 12px; border: 2px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 15px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                        <?php echo htmlspecialchars($section['label']); ?>
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;">Primary Color</label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][primaryColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['primary_color'] ?? '#800020'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;">Secondary Color</label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][secondaryColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['secondary_color'] ?? '#6B0000'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;">Background Color</label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][backgroundColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['background_color'] ?? '#ffffff'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;">Text Color</label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][textColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['text_color'] ?? '#1e293b'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;">Accent Color</label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][accentColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['accent_color'] ?? '#800020'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;">Hover Color</label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][hoverColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['hover_color'] ?? '#6B0000'); ?>">
                        </div>
                    </div>
                    <input type="hidden" name="colors[<?php echo htmlspecialchars($section['name']); ?>][sectionName]" value="<?php echo htmlspecialchars($section['name']); ?>">
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px; padding: 30px; background: #f8f9fa; border-radius: 12px; border: 2px solid #e0e0e0;">
            <div style="margin-bottom: 20px; padding: 15px; background: #d4edda; border-radius: 8px; color: #155724; font-size: 16px; font-weight: bold;">
                ✅ Displayed <?php echo $sectionCount; ?> out of <?php echo count($sections); ?> sections
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 15px 40px; font-size: 16px; font-weight: 600; background: linear-gradient(135deg, #800020, #6B0000); color: white; border: none; border-radius: 8px; cursor: pointer;">
                💾 Save All Colors
            </button>
            <p style="margin-top: 15px; color: #666; font-size: 14px;">
                All color changes will be saved and applied immediately to your website
            </p>
        </div>
    </form>
</div>
