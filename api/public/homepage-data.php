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
    
    // Fetch all homepage data
    $data = [
        'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
        'heroContent' => $db->fetchOne("SELECT * FROM hero_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
        'aboutContent' => $db->fetchOne("SELECT * FROM about_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
        'contactInfo' => $db->fetchOne("SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
        'teachers' => $db->fetchAll("SELECT * FROM teachers WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6") ?: [],
        'testimonials' => $db->fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6") ?: [],
        'activities' => $db->fetchAll("SELECT * FROM activities WHERE is_active = 1 ORDER BY date DESC LIMIT 6") ?: [],
        'facilities' => $db->fetchAll("SELECT * FROM facilities WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6") ?: [],
        'schoolLifeItems' => $db->fetchAll("SELECT * FROM school_life_items WHERE is_active = 1 ORDER BY order_index ASC") ?: [],
        'academicsContent' => $db->fetchOne("SELECT * FROM academics_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
        'sportsItems' => $db->fetchAll("SELECT * FROM sports_items WHERE is_active = 1 ORDER BY order_index ASC") ?: [],
        'sportsContent' => $db->fetchOne("SELECT * FROM sports_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
        'customSectionContent' => $db->fetchOne("SELECT * FROM custom_section_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
        'threeColumnSections' => $db->fetchAll("SELECT * FROM three_column_sections WHERE is_active = 1 ORDER BY order_index ASC") ?: [],
    ];
    
    // Fetch gallery images
    try {
        $gallerySection = $db->fetchOne("SELECT * FROM media_sections WHERE name = 'Gallery' AND is_active = 1 LIMIT 1");
        if ($gallerySection) {
            $data['galleryImages'] = $db->fetchAll(
                "SELECT * FROM media WHERE section_id = ? AND is_active = 1 AND (file_type LIKE 'image/%' OR filename LIKE '%.jpg' OR filename LIKE '%.jpeg' OR filename LIKE '%.png' OR filename LIKE '%.gif' OR filename LIKE '%.webp') ORDER BY created_at DESC LIMIT 6",
                [$gallerySection['id']]
            ) ?: [];
        } else {
            $data['galleryImages'] = [];
        }
    } catch (Exception $e) {
        $data['galleryImages'] = [];
    }
    
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
