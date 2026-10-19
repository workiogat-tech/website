<?php
/**
 * Script to update all pages to use global hero text styles
 * Run this once to apply hero styles to all pages
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/hero-styles-helper.php';

$pages = [
    'activities.php',
    'facilities.php',
    'testimonials.php',
    'events.php',
    'news.php',
    'announcements.php',
    'gallery.php',
    'curriculum.php',
    'contact.php',
    'give-back.php',
    'sponsorship.php',
    'resources.php',
    'parents.php',
    'people-behind-school.php',
];

echo "<h1>Updating Hero Styles on All Pages</h1>";
echo "<pre>";

foreach ($pages as $page) {
    $filePath = __DIR__ . '/' . $page;
    if (!file_exists($filePath)) {
        echo "Skipping $page (file not found)\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    $updated = false;
    
    // Add hero-styles-helper include if not present
    if (strpos($content, 'hero-styles-helper.php') === false) {
        if (strpos($content, 'image-helper.php') !== false) {
            $content = str_replace(
                "require_once __DIR__ . '/includes/image-helper.php';",
                "require_once __DIR__ . '/includes/image-helper.php';\nrequire_once __DIR__ . '/includes/hero-styles-helper.php';",
                $content
            );
            $updated = true;
        }
    }
    
    // Update page-hero-title
    if (preg_match('/<h1\s+class="page-hero-title"[^>]*>(.*?)<\/h1>/s', $content, $matches)) {
        $oldTag = $matches[0];
        $titleText = $matches[1];
        
        // Check if it already has hero styles
        if (strpos($oldTag, 'getHeroTitleStyle') === false) {
            $newTag = '<?php $heroStyles = getHeroTextStyles($db); ?><h1 class="page-hero-title" style="<?php echo getHeroTitleStyle($heroStyles); ?>">' . $titleText . '</h1>';
            $content = str_replace($oldTag, $newTag, $content);
            $updated = true;
        }
    }
    
    // Update page-hero-subtitle
    if (preg_match('/<p\s+class="page-hero-subtitle"[^>]*>(.*?)<\/p>/s', $content, $matches)) {
        $oldTag = $matches[0];
        $subtitleText = $matches[1];
        
        // Check if it already has hero styles
        if (strpos($oldTag, 'getHeroSubtitleStyle') === false) {
            $newTag = '<p class="page-hero-subtitle" style="<?php echo getHeroSubtitleStyle($heroStyles); ?>">' . $subtitleText . '</p>';
            $content = str_replace($oldTag, $newTag, $content);
            $updated = true;
        }
    }
    
    // Update page-hero-caption
    if (preg_match('/<p\s+class="page-hero-caption"[^>]*>(.*?)<\/p>/s', $content, $matches)) {
        $oldTag = $matches[0];
        $captionText = $matches[1];
        
        // Check if it already has hero styles
        if (strpos($oldTag, 'getHeroCaptionStyle') === false) {
            $newTag = '<p class="page-hero-caption" style="<?php echo getHeroCaptionStyle($heroStyles); ?>">' . $captionText . '</p>';
            $content = str_replace($oldTag, $newTag, $content);
            $updated = true;
        }
    }
    
    if ($updated) {
        file_put_contents($filePath, $content);
        echo "✅ Updated $page\n";
    } else {
        echo "⏭️  Skipped $page (already updated or no hero section found)\n";
    }
}

echo "\n✅ All pages updated!\n";
echo "</pre>";
echo "<p><a href='admin.php?tab=hero-text-styles'>Go to Hero Text Styles Admin Panel</a></p>";
?>
