<?php
/**
 * Public API - Generic Page Data
 * Returns data for any page as JSON
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/image-helper.php';

$pageName = $_GET['page'] ?? '';

try {
    $db = getDB();
    $data = [];
    
    switch ($pageName) {
        case 'activities':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'activities' => $db->fetchAll("SELECT * FROM activities WHERE is_active = 1 ORDER BY date DESC") ?: [],
                'settings' => $db->fetchOne("SELECT * FROM activities_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'curriculum':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'content' => $db->fetchOne("SELECT * FROM curriculum_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'programs' => $db->fetchAll("SELECT * FROM curriculum_programs WHERE is_active = 1 ORDER BY order_index ASC") ?: [],
                'whyChoose' => $db->fetchOne("SELECT * FROM curriculum_why_choose WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'whyChooseItems' => $db->fetchAll("SELECT * FROM curriculum_why_choose_items WHERE is_active = 1 ORDER BY order_index ASC") ?: [],
            ];
            break;
            
        case 'facilities':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'facilities' => $db->fetchAll("SELECT * FROM facilities WHERE is_active = 1 ORDER BY created_at DESC") ?: [],
                'settings' => $db->fetchOne("SELECT * FROM facilities_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'teachers':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'teachers' => $db->fetchAll("SELECT * FROM teachers WHERE is_active = 1 ORDER BY created_at DESC") ?: [],
                'settings' => $db->fetchOne("SELECT * FROM teachers_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'testimonials':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'testimonials' => $db->fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC") ?: [],
                'settings' => $db->fetchOne("SELECT * FROM testimonials_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'gallery':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'settings' => $db->fetchOne("SELECT * FROM gallery_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            // Get gallery images
            try {
                $gallerySection = $db->fetchOne("SELECT * FROM media_sections WHERE name = 'Gallery' AND is_active = 1 LIMIT 1");
                if ($gallerySection) {
                    $data['images'] = $db->fetchAll(
                        "SELECT * FROM media WHERE section_id = ? AND is_active = 1 AND (file_type LIKE 'image/%' OR filename LIKE '%.jpg' OR filename LIKE '%.jpeg' OR filename LIKE '%.png' OR filename LIKE '%.gif' OR filename LIKE '%.webp') ORDER BY created_at DESC",
                        [$gallerySection['id']]
                    ) ?: [];
                } else {
                    $data['images'] = [];
                }
            } catch (Exception $e) {
                $data['images'] = [];
            }
            break;
            
        case 'news':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'news' => $db->fetchAll("SELECT * FROM news WHERE is_active = 1 ORDER BY created_at DESC") ?: [],
                'settings' => $db->fetchOne("SELECT * FROM news_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'announcements':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'announcements' => $db->fetchAll("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC") ?: [],
                'settings' => $db->fetchOne("SELECT * FROM announcements_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'clubs':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'clubs' => $db->fetchAll("SELECT * FROM clubs_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'admission':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'content' => $db->fetchOne("SELECT * FROM admission_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'school-background':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'content' => $db->fetchOne("SELECT * FROM school_background_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'people-behind-school':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'people' => $db->fetchAll("SELECT * FROM people_behind_school WHERE is_active = 1 ORDER BY created_at DESC") ?: [],
            ];
            break;
            
        case 'sponsorship':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'content' => $db->fetchOne("SELECT * FROM sponsorship_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'give-back':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'content' => $db->fetchOne("SELECT * FROM giveback_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'parents':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'content' => $db->fetchOne("SELECT * FROM parents_portal_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        case 'resources':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'resources' => $db->fetchAll("SELECT * FROM resources WHERE is_active = 1 ORDER BY created_at DESC") ?: [],
            ];
            break;
            
        case 'events':
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
                'activities' => $db->fetchAll("SELECT * FROM activities WHERE is_active = 1 ORDER BY date DESC") ?: [],
                'settings' => $db->fetchOne("SELECT * FROM activities_settings WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
            break;
            
        default:
            $data = [
                'branding' => $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null,
            ];
    }
    
    ob_clean();
    echo json_encode(['success' => true, 'data' => $data]);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
