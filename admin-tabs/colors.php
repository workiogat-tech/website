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
    ['name' => 'header', 'label' => 'Header', 'group' => 'Site-wide'],
    ['name' => 'footer', 'label' => 'Footer', 'group' => 'Site-wide'],
    ['name' => 'buttons', 'label' => 'Buttons', 'group' => 'Site-wide'],
    ['name' => 'index', 'label' => 'Homepage', 'group' => 'Homepage'],
    ['name' => 'hero', 'label' => 'Hero', 'group' => 'Homepage'],
    ['name' => 'about-section', 'label' => 'About', 'group' => 'Homepage'],
    ['name' => 'school-life', 'label' => 'School Life', 'group' => 'Homepage'],
    ['name' => 'academics', 'label' => 'Academics', 'group' => 'Homepage'],
    ['name' => 'sports', 'label' => 'Sports', 'group' => 'Homepage'],
    ['name' => 'vision-mission', 'label' => 'Vision and Mission', 'group' => 'Homepage'],
    ['name' => 'core-values', 'label' => 'Core Values', 'group' => 'Homepage'],
    ['name' => 'why-choose', 'label' => 'Why Choose Us', 'group' => 'Homepage'],
    ['name' => 'join-section', 'label' => 'Join', 'group' => 'Homepage'],
    ['name' => 'about', 'label' => 'About', 'group' => 'Pages'],
    ['name' => 'activities', 'label' => 'Activities', 'group' => 'Pages'],
    ['name' => 'admission', 'label' => 'Admission', 'group' => 'Pages'],
    ['name' => 'announcements', 'label' => 'Announcements', 'group' => 'Pages'],
    ['name' => 'clubs', 'label' => 'Clubs', 'group' => 'Pages'],
    ['name' => 'contact', 'label' => 'Contact', 'group' => 'Pages'],
    ['name' => 'curriculum', 'label' => 'Curriculum', 'group' => 'Pages'],
    ['name' => 'events', 'label' => 'Events', 'group' => 'Pages'],
    ['name' => 'facilities', 'label' => 'Facilities', 'group' => 'Pages'],
    ['name' => 'gallery', 'label' => 'Gallery', 'group' => 'Pages'],
    ['name' => 'give-back', 'label' => 'Give Back', 'group' => 'Pages'],
    ['name' => 'news', 'label' => 'News', 'group' => 'Pages'],
    ['name' => 'parents', 'label' => 'Parents', 'group' => 'Pages'],
    ['name' => 'people-behind-school', 'label' => 'Our People', 'group' => 'Pages'],
    ['name' => 'resources', 'label' => 'Resources', 'group' => 'Pages'],
    ['name' => 'school-background', 'label' => 'Background', 'group' => 'Pages'],
    ['name' => 'sponsorship', 'label' => 'Sponsorship', 'group' => 'Pages'],
    ['name' => 'teachers', 'label' => 'Teachers', 'group' => 'Pages'],
    ['name' => 'testimonials', 'label' => 'Testimonials', 'group' => 'Pages'],
];
$colorGroupNotes = [
    'Site-wide' => 'Header, footer, and the shared button palette.',
    'Homepage' => 'Each block on the home page.',
    'Pages' => 'The palette used on that page.',
];
?>
<div class="content-card">
    <h2>Colors</h2>
    <p style="margin-bottom: 12px; color: #57534e;">Branding sets the default school color. This screen stores a palette for each part of the site. A page uses its own primary color when one is saved.</p>
    <div style="background: #f5f5f4; border-radius: 10px; padding: 14px 16px; margin-bottom: 22px; color: #44403c; font-size: 14px; line-height: 1.5;">
        <strong>Primary</strong> is headings and links.
        <strong>Secondary</strong> is the darker shade.
        <strong>Background</strong> is the page background.
        <strong>Text</strong> is paragraph text.
        <strong>Accent</strong> is highlights.
        <strong>Hover</strong> is the color when a visitor hovers a button.
    </div>
    
    <form method="POST" action="api/content/website-colors.php" id="colorsForm" onsubmit="return handleAdminFormSubmit(event, this);">
        <input type="search" placeholder="Find a section, such as Core Values" oninput="filterColorCards(this.value)" style="width:100%;max-width:420px;margin-bottom:18px;padding:10px 12px;border:1px solid #d6d3d1;border-radius:8px;">
        <?php
        $sectionCount = 0;
        $lastGroup = '';
        foreach ($sections as $section):
            $sectionCount++;
            $colorData = $colorsMap[$section['name']] ?? [];
            if ($lastGroup !== $section['group']):
                if ($lastGroup !== '') echo '</div>';
                $lastGroup = $section['group'];
        ?>
            <h3 style="margin: 8px 0 6px; font-size: 18px; color: #1c1917;"><?php echo htmlspecialchars($section['group']); ?></h3>
            <p style="margin: 0 0 14px; color: #78716c; font-size: 14px;"><?php echo htmlspecialchars($colorGroupNotes[$section['group']] ?? ''); ?></p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <?php endif; ?>
                <?php $isCoreValues = $section['name'] === 'core-values'; ?>
                <div class="color-section-card" data-color-label="<?php echo htmlspecialchars(strtolower($section['label'] . ' ' . $section['name'])); ?>" style="background: #ffffff; padding: 20px; border-radius: 12px; border: 2px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 15px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                        <?php echo htmlspecialchars($section['label']); ?>
                    </h4>
                    <?php if ($isCoreValues): ?>
                    <p style="margin: 0 0 12px; color: #57534e; font-size: 13px; line-height: 1.45;">Homepage Core Values boxes. Card color fills each box. Card text is the wording inside it. Title color is the heading above the boxes.</p>
                    <?php endif; ?>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;"><?php echo $isCoreValues ? 'Title color' : 'Primary Color'; ?></label>
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
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;"><?php echo $isCoreValues ? 'Card color' : 'Background Color'; ?></label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][backgroundColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['background_color'] ?? ($isCoreValues ? '#f3f4f6' : '#ffffff')); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;"><?php echo $isCoreValues ? 'Card text' : 'Text Color'; ?></label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][textColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['text_color'] ?? ($isCoreValues ? '#1f2937' : '#1e293b')); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;">Accent Color</label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][accentColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['accent_color'] ?? '#800020'); ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 5px; color: #555;"><?php echo $isCoreValues ? 'Card hover' : 'Hover Color'; ?></label>
                            <input type="color" 
                                   name="colors[<?php echo htmlspecialchars($section['name']); ?>][hoverColor]" 
                                   style="height: 45px; width: 100%; cursor: pointer; border: 2px solid #ddd; border-radius: 4px;"
                                   value="<?php echo htmlspecialchars($colorData['hover_color'] ?? ($isCoreValues ? '#e5e7eb' : '#6B0000')); ?>">
                        </div>
                    </div>
                    <input type="hidden" name="colors[<?php echo htmlspecialchars($section['name']); ?>][sectionName]" value="<?php echo htmlspecialchars($section['name']); ?>">
                </div>
            <?php endforeach; if ($lastGroup !== '') echo '</div>'; ?>
        
        <div style="text-align: center; margin-top: 12px; padding: 24px; background: #fafaf9; border-radius: 12px; border: 1px solid #e7e5e4;">
            <button type="submit" class="btn btn-primary" style="padding: 14px 32px; font-size: 16px; font-weight: 600;">
                Save all colors
            </button>
            <p style="margin-top: 15px; color: #666; font-size: 14px;">
                All color changes will be saved and applied immediately to your website
            </p>
        </div>
    </form>
</div>
<script>
function filterColorCards(query) {
    var needle = String(query || '').trim().toLowerCase();
    document.querySelectorAll('.color-section-card').forEach(function (card) {
        var label = (card.getAttribute('data-color-label') || '').toLowerCase();
        card.style.display = !needle || label.indexOf(needle) !== -1 ? '' : 'none';
    });
}
</script>
