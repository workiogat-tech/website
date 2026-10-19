<?php
/**
 * Public API - Homepage Data
 * Returns all homepage data as JSON for frontend consumption
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Start output buffering
ob_start();

// Suppress error display
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/image-helper.php';

try {
    $db = getDB();

    $packed = $db->fetchOne(
        "SELECT
            (SELECT row_to_json(x) FROM (SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1) x) AS branding,
            (SELECT row_to_json(x) FROM (SELECT * FROM hero_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1) x) AS \"heroContent\",
            (SELECT row_to_json(x) FROM (SELECT * FROM about_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1) x) AS \"aboutContent\",
            (SELECT row_to_json(x) FROM (SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1) x) AS \"contactInfo\",
            (SELECT COALESCE(json_agg(x ORDER BY x.created_at DESC), '[]'::json) FROM (SELECT * FROM teachers WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6) x) AS teachers,
            (SELECT COALESCE(json_agg(x ORDER BY x.created_at DESC), '[]'::json) FROM (SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6) x) AS testimonials,
            (SELECT COALESCE(json_agg(x ORDER BY x.date DESC), '[]'::json) FROM (SELECT * FROM activities WHERE is_active = 1 ORDER BY date DESC LIMIT 6) x) AS activities,
            (SELECT COALESCE(json_agg(x ORDER BY x.created_at DESC), '[]'::json) FROM (SELECT * FROM facilities WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6) x) AS facilities,
            (SELECT COALESCE(json_agg(x ORDER BY x.order_index ASC), '[]'::json) FROM (SELECT * FROM school_life_items WHERE is_active = 1 ORDER BY order_index ASC) x) AS \"schoolLifeItems\",
            (SELECT row_to_json(x) FROM (SELECT * FROM academics_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1) x) AS \"academicsContent\",
            (SELECT COALESCE(json_agg(x ORDER BY x.order_index ASC), '[]'::json) FROM (SELECT * FROM sports_items WHERE is_active = 1 ORDER BY order_index ASC) x) AS \"sportsItems\",
            (SELECT row_to_json(x) FROM (SELECT * FROM sports_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1) x) AS \"sportsContent\",
            (SELECT row_to_json(x) FROM (SELECT * FROM custom_section_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1) x) AS \"customSectionContent\",
            (SELECT COALESCE(json_agg(x ORDER BY x.order_index ASC), '[]'::json) FROM (SELECT * FROM three_column_sections WHERE is_active = 1 ORDER BY order_index ASC) x) AS \"threeColumnSections\",
            (SELECT COALESCE(json_agg(m ORDER BY m.created_at DESC), '[]'::json) FROM (
                SELECT media.* FROM media
                JOIN media_sections ms ON ms.id = media.section_id
                WHERE ms.name = 'Gallery' AND ms.is_active = 1 AND media.is_active = 1
                  AND (media.file_type LIKE 'image/%' OR media.filename ILIKE '%.jpg' OR media.filename ILIKE '%.jpeg' OR media.filename ILIKE '%.png' OR media.filename ILIKE '%.gif' OR media.filename ILIKE '%.webp')
                ORDER BY media.created_at DESC
                LIMIT 6
            ) m) AS \"galleryImages\""
    );

    $decodeObject = function ($value) {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    };
    $decodeList = function ($value) use ($decodeObject) {
        $decoded = $decodeObject($value);
        return is_array($decoded) ? $decoded : [];
    };

    $data = [
        'branding' => $decodeObject($packed['branding'] ?? null),
        'heroContent' => $decodeObject($packed['heroContent'] ?? null),
        'aboutContent' => $decodeObject($packed['aboutContent'] ?? null),
        'contactInfo' => $decodeObject($packed['contactInfo'] ?? null),
        'teachers' => $decodeList($packed['teachers'] ?? null),
        'testimonials' => $decodeList($packed['testimonials'] ?? null),
        'activities' => $decodeList($packed['activities'] ?? null),
        'facilities' => $decodeList($packed['facilities'] ?? null),
        'schoolLifeItems' => $decodeList($packed['schoolLifeItems'] ?? null),
        'academicsContent' => $decodeObject($packed['academicsContent'] ?? null),
        'sportsItems' => $decodeList($packed['sportsItems'] ?? null),
        'sportsContent' => $decodeObject($packed['sportsContent'] ?? null),
        'customSectionContent' => $decodeObject($packed['customSectionContent'] ?? null),
        'threeColumnSections' => $decodeList($packed['threeColumnSections'] ?? null),
        'galleryImages' => $decodeList($packed['galleryImages'] ?? null),
    ];
    
    // Decode JSON fields
    if ($data['heroContent'] && isset($data['heroContent']['slides'])) {
        $decoded = json_decode($data['heroContent']['slides'], true);
        if (is_array($decoded)) {
            $data['heroContent']['slides'] = $decoded;
        }
    }
    
    // Normalize image URLs
    if ($data['heroContent'] && !empty($data['heroContent']['background_image'])) {
        $data['heroContent']['background_image'] = normalizeImageUrl($data['heroContent']['background_image']);
    }
    
    // Normalize three-column section images
    if (!empty($data['threeColumnSections'])) {
        foreach ($data['threeColumnSections'] as &$section) {
            if (!empty($section['image'])) {
                $section['image'] = normalizeImageUrl($section['image']);
            }
        }
        unset($section); // Break reference
    }
    
    ob_clean();
    echo json_encode(['success' => true, 'data' => $data]);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
