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
