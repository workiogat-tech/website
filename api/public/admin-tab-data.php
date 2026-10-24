<?php
/**
 * Public API - Admin Tab Data
 * Returns data for admin tabs as JSON
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$auth = getAuth();
$auth->requireAdmin();

$db = getDB();
$tab = $_GET['tab'] ?? 'dashboard';

try {
    $data = [];
    
    // Load data based on tab
    switch ($tab) {
        case 'colors':
            $colors = $db->fetchAll("SELECT * FROM website_colors WHERE is_active = 1");
            $colorsMap = [];
            foreach ($colors as $color) {
                $colorsMap[$color['section_name']] = $color;
            }
            $data = [
                'colors' => $colorsMap,
                'sections' => [
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
                ]
            ];
            break;
            
        case 'dashboard':
            $data = [
                'stats' => [
                    'users' => $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
                    'teachers' => $db->fetchOne("SELECT COUNT(*) as count FROM teachers WHERE is_active = 1")['count'] ?? 0,
                    'events' => $db->fetchOne("SELECT COUNT(*) as count FROM activities WHERE is_active = 1")['count'] ?? 0,
                    'testimonials' => $db->fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_active = 1")['count'] ?? 0,
                    'facilities' => $db->fetchOne("SELECT COUNT(*) as count FROM facilities WHERE is_active = 1")['count'] ?? 0,
                    'news' => $db->fetchOne("SELECT COUNT(*) as count FROM news WHERE is_active = 1")['count'] ?? 0,
                ]
            ];
            break;
            
        default:
            // For other tabs, return empty data (they'll load their own data via their APIs)
            $data = [];
    }
    
    ob_clean();
    echo json_encode(['success' => true, 'data' => $data]);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
