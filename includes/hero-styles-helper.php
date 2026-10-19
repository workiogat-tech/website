<?php
/**
 * Hero Text Styles Helper
 * Loads and applies global hero section text styles
 */
function getHeroTextStyles($db) {
    try {
        $styles = $db->fetchOne("SELECT * FROM hero_text_styles WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        if (!$styles) {
            // Return defaults
            return [
                'title_color' => '#ffffff',
                'title_font_size' => '64',
                'title_font_weight' => 'bold',
                'title_text_shadow' => '1',
                'subtitle_color' => '#ffffff',
                'subtitle_font_size' => '28',
                'subtitle_font_weight' => '300',
                'subtitle_text_shadow' => '1',
                'caption_color' => 'rgba(255, 255, 255, 0.9)',
                'caption_font_size' => '21',
                'caption_font_weight' => '400',
                'caption_text_shadow' => '0',
            ];
        }
        return $styles;
    } catch (Exception $e) {
        error_log("Error loading hero text styles: " . $e->getMessage());
        // Return defaults on error
        return [
            'title_color' => '#ffffff',
            'title_font_size' => '64',
            'title_font_weight' => 'bold',
            'title_text_shadow' => '1',
            'subtitle_color' => '#ffffff',
            'subtitle_font_size' => '28',
            'subtitle_font_weight' => '300',
            'subtitle_text_shadow' => '1',
            'caption_color' => 'rgba(255, 255, 255, 0.9)',
            'caption_font_size' => '21',
            'caption_font_weight' => '400',
            'caption_text_shadow' => '0',
        ];
    }
}

function getHeroTitleStyle($heroStyles) {
    $fontWeight = $heroStyles['title_font_weight'] ?? 'bold';
    if ($fontWeight === 'bold') {
        $fontWeight = '700';
    }
    
    $style = 'color: ' . htmlspecialchars($heroStyles['title_color'] ?? '#ffffff') . '; ';
    $style .= 'font-size: ' . htmlspecialchars($heroStyles['title_font_size'] ?? '64') . 'px; ';
    $style .= 'font-weight: ' . htmlspecialchars($fontWeight) . '; ';
    
    if (($heroStyles['title_text_shadow'] ?? '1') == '1') {
        $style .= 'text-shadow: 2px 2px 4px rgba(0,0,0,0.5); ';
    }
    
    return $style;
}

function getHeroSubtitleStyle($heroStyles) {
    $style = 'color: ' . htmlspecialchars($heroStyles['subtitle_color'] ?? '#ffffff') . '; ';
    $style .= 'font-size: ' . htmlspecialchars($heroStyles['subtitle_font_size'] ?? '28') . 'px; ';
    $style .= 'font-weight: ' . htmlspecialchars($heroStyles['subtitle_font_weight'] ?? '300') . '; ';
    
    if (($heroStyles['subtitle_text_shadow'] ?? '1') == '1') {
        $style .= 'text-shadow: 1px 1px 2px rgba(0,0,0,0.5); ';
    }
    
    return $style;
}

function getHeroCaptionStyle($heroStyles) {
    $style = 'color: ' . htmlspecialchars($heroStyles['caption_color'] ?? 'rgba(255, 255, 255, 0.9)') . '; ';
    $style .= 'font-size: ' . htmlspecialchars($heroStyles['caption_font_size'] ?? '21') . 'px; ';
    $style .= 'font-weight: ' . htmlspecialchars($heroStyles['caption_font_weight'] ?? '400') . '; ';
    
    if (($heroStyles['caption_text_shadow'] ?? '0') == '1') {
        $style .= 'text-shadow: 1px 1px 2px rgba(0,0,0,0.5); ';
    }
    
    return $style;
}

function getHeroDescriptionStyle($heroStyles) {
    $style = 'color: ' . htmlspecialchars($heroStyles['description_color'] ?? '#ffffff') . '; ';
    $style .= 'font-size: ' . htmlspecialchars($heroStyles['description_font_size'] ?? '20') . 'px; ';
    $style .= 'font-weight: ' . htmlspecialchars($heroStyles['description_font_weight'] ?? '400') . '; ';
    
    if (($heroStyles['description_text_shadow'] ?? '1') == '1') {
        $style .= 'text-shadow: 1px 1px 2px rgba(0,0,0,0.5); ';
    }
    
    return $style;
}
?>
