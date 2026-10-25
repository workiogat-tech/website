<?php
/**
 * Admin Panel - Full Customization Interface
 * Exact replica of React AdminDashboard component
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/image-helper.php';

$auth = getAuth();
$auth->requireAdmin();

$db = getDB();
$currentUser = $auth->getCurrentUser();

// Get branding for primary colors
$branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$primaryColor = $branding['primary_color'] ?? '#800020';
$secondaryColor = $branding['secondary_color'] ?? '#6B0000';

// Helper function to convert hex to RGB
function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    ];
}

// Handle POST requests for media tab BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['tab']) && $_GET['tab'] === 'media') {
    $selectedSection = $_GET['section'] ?? null;
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_section') {
            $name = trim($_POST['name'] ?? '');
            $color = $_POST['color'] ?? '#3B82F6';
            if ($name) {
                try {
                    // Check if section with same name already exists
                    $existing = $db->fetchOne("SELECT id FROM media_sections WHERE name = ? AND is_active = 1", [$name]);
                    if ($existing) {
                        $_SESSION['media_error'] = 'A section with this name already exists.';
                    } else {
                        $db->insert("INSERT INTO media_sections (name, color, is_active, created_at) VALUES (?, ?, 1, NOW())", [$name, $color]);
                        $_SESSION['media_success'] = 'Section created successfully!';
                    }
                } catch (Exception $e) {
                    error_log("Section creation error: " . $e->getMessage());
                    $_SESSION['media_error'] = 'Failed to create section: ' . $e->getMessage();
                }
                header('Location: ?tab=media');
                exit;
            } else {
                $_SESSION['media_error'] = 'Section name is required.';
                header('Location: ?tab=media');
                exit;
            }
        } elseif ($_POST['action'] === 'edit_section' && isset($_POST['section_id'])) {
            $sectionId = $_POST['section_id'];
            $name = trim($_POST['name'] ?? '');
            $color = $_POST['color'] ?? '#3B82F6';
            if ($name) {
                try {
                    // Check if another section with same name already exists
                    $existing = $db->fetchOne("SELECT id FROM media_sections WHERE name = ? AND id != ? AND is_active = 1", [$name, $sectionId]);
                    if ($existing) {
                        $_SESSION['media_error'] = 'A section with this name already exists.';
                    } else {
                        $db->update("UPDATE media_sections SET name = ?, color = ?, updated_at = NOW() WHERE id = ?", [$name, $color, $sectionId]);
                        $_SESSION['media_success'] = 'Section updated successfully!';
                    }
                } catch (Exception $e) {
                    error_log("Section update error: " . $e->getMessage());
                    $_SESSION['media_error'] = 'Failed to update section: ' . $e->getMessage();
                }
                header('Location: ?tab=media' . ($selectedSection ? '&section=' . $selectedSection : ''));
                exit;
            } else {
                $_SESSION['media_error'] = 'Section name is required.';
                header('Location: ?tab=media' . ($selectedSection ? '&section=' . $selectedSection : ''));
                exit;
            }
        } elseif ($_POST['action'] === 'delete_section' && isset($_POST['section_id'])) {
            $sectionId = $_POST['section_id'];
            try {
                // Check if section has media
                $mediaCount = $db->fetchOne("SELECT COUNT(*) as count FROM media WHERE section_id = ?", [$sectionId])['count'] ?? 0;
                if ($mediaCount > 0) {
                    $_SESSION['media_error'] = "Cannot delete section. It contains {$mediaCount} file(s). Please move or delete the files first.";
                } else {
                    $db->update("UPDATE media_sections SET is_active = 0 WHERE id = ?", [$sectionId]);
                    $_SESSION['media_success'] = 'Section deleted successfully!';
                }
            } catch (Exception $e) {
                error_log("Section deletion error: " . $e->getMessage());
                $_SESSION['media_error'] = 'Failed to delete section: ' . $e->getMessage();
            }
            header('Location: ?tab=media');
            exit;
        } elseif ($_POST['action'] === 'delete_media' && isset($_POST['media_id'])) {
            $media = $db->fetchOne("SELECT file_path FROM media WHERE id = ?", [$_POST['media_id']]);
            if ($media && !empty($media['file_path'])) {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($media['file_path'], PHP_URL_PATH);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $db->delete("DELETE FROM media WHERE id = ?", [$_POST['media_id']]);
            $_SESSION['media_success'] = 'File deleted successfully!';
            header('Location: ?tab=media' . ($selectedSection ? '&section=' . $selectedSection : ''));
            exit;
        } elseif ($_POST['action'] === 'move_media' && isset($_POST['media_id']) && isset($_POST['new_section_id'])) {
            $mediaId = $_POST['media_id'];
            $newSectionId = $_POST['new_section_id'] ?: null;
            try {
                // Validate new section exists if provided
                if ($newSectionId) {
                    $sectionCheck = $db->fetchOne("SELECT id FROM media_sections WHERE id = ? AND is_active = 1", [$newSectionId]);
                    if (!$sectionCheck) {
                        $_SESSION['media_error'] = 'Selected section does not exist or is inactive.';
                        header('Location: ?tab=media' . ($selectedSection ? '&section=' . $selectedSection : ''));
                        exit;
                    }
                }
                $db->update("UPDATE media SET section_id = ?, updated_at = NOW() WHERE id = ?", [$newSectionId, $mediaId]);
                $_SESSION['media_success'] = 'File moved successfully!';
            } catch (Exception $e) {
                error_log("Move media error: " . $e->getMessage());
                $_SESSION['media_error'] = 'Failed to move file: ' . $e->getMessage();
            }
            header('Location: ?tab=media' . ($selectedSection ? '&section=' . $selectedSection : ''));
            exit;
        }
    }
}

// Get active tab from URL. The map editor lives on the Contact screen.
$activeTab = $_GET['tab'] ?? 'dashboard';
if ($activeTab === 'map') {
    header('Location: admin.php?tab=contact#school-map');
    exit;
}
$customizationSection = $_GET['section'] ?? 'header';

// Get statistics
$stats = [];
try {
    $stats = [
        'users' => $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
        'teachers' => $db->fetchOne("SELECT COUNT(*) as count FROM teachers WHERE is_active = 1")['count'] ?? 0,
        'events' => $db->fetchOne("SELECT COUNT(*) as count FROM activities WHERE is_active = 1")['count'] ?? 0,
        'testimonials' => $db->fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_active = 1")['count'] ?? 0,
        'facilities' => $db->fetchOne("SELECT COUNT(*) as count FROM facilities WHERE is_active = 1")['count'] ?? 0,
        'news' => $db->fetchOne("SELECT COUNT(*) as count FROM news WHERE is_active = 1")['count'] ?? 0,
        'announcements' => $db->fetchOne("SELECT COUNT(*) as count FROM announcements WHERE is_active = 1")['count'] ?? 0,
    ];
} catch (Exception $e) {
    error_log("Stats error: " . $e->getMessage());
    $stats = ['users' => 0, 'teachers' => 0, 'events' => 0, 'testimonials' => 0, 'facilities' => 0, 'news' => 0, 'announcements' => 0];
}

// Get branding
$branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?? [];

$navGroups = [
    ['id' => 'overview', 'label' => 'Overview', 'color' => '#1e3a5f', 'items' => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => '▦'],
    ]],
    ['id' => 'design', 'label' => 'Design', 'color' => '#6d28d9', 'items' => [
        ['id' => 'branding', 'label' => 'Branding', 'icon' => '◆'],
        ['id' => 'colors', 'label' => 'Colors', 'icon' => '◉'],
        ['id' => 'button-customization', 'label' => 'Buttons', 'icon' => '▢'],
        ['id' => 'hero-text-styles', 'label' => 'Hero Text', 'icon' => 'T'],
        ['id' => 'festive-animations', 'label' => 'Animations', 'icon' => '✦'],
    ]],
    ['id' => 'homepage', 'label' => 'Homepage', 'color' => '#0f766e', 'items' => [
        ['id' => 'homepage-sections', 'label' => 'Homepage', 'icon' => '⌂'],
        ['id' => 'hero', 'label' => 'Hero', 'icon' => '▣'],
        ['id' => 'about', 'label' => 'About', 'icon' => '¶'],
    ]],
    ['id' => 'school', 'label' => 'School', 'color' => '#1d4ed8', 'items' => [
        ['id' => 'background', 'label' => 'Background', 'icon' => '⌂'],
        ['id' => 'people-behind-school', 'label' => 'Our People', 'icon' => '☺'],
        ['id' => 'teachers', 'label' => 'Teachers', 'icon' => '✎'],
        ['id' => 'curriculum', 'label' => 'Curriculum', 'icon' => '☰'],
        ['id' => 'programs', 'label' => 'Programs', 'icon' => '☰'],
        ['id' => 'facilities', 'label' => 'Facilities', 'icon' => '⌂'],
        ['id' => 'clubs', 'label' => 'Clubs', 'icon' => '☺'],
    ]],
    ['id' => 'updates', 'label' => 'News', 'color' => '#b45309', 'items' => [
        ['id' => 'news', 'label' => 'News', 'icon' => '☰'],
        ['id' => 'announcements', 'label' => 'Announcements', 'icon' => '!'],
        ['id' => 'all-updates-settings', 'label' => 'Updates Page', 'icon' => '☰'],
        ['id' => 'activities', 'label' => 'Events', 'icon' => '▦'],
        ['id' => 'gallery', 'label' => 'Gallery', 'icon' => '▣'],
        ['id' => 'testimonials', 'label' => 'Testimonials', 'icon' => '★'],
    ]],
    ['id' => 'community', 'label' => 'Community', 'color' => '#9f1239', 'items' => [
        ['id' => 'admission', 'label' => 'Admission', 'icon' => '✎'],
        ['id' => 'parents', 'label' => 'Parents', 'icon' => '☺'],
        ['id' => 'sponsorship', 'label' => 'Sponsorship', 'icon' => '♥'],
        ['id' => 'giveback', 'label' => 'Give Back', 'icon' => '♥'],
        ['id' => 'resources', 'label' => 'Resources', 'icon' => '☰'],
    ]],
    ['id' => 'site', 'label' => 'Site', 'color' => '#334155', 'items' => [
        ['id' => 'navigation', 'label' => 'Menu', 'icon' => '≡'],
        ['id' => 'footer', 'label' => 'Footer', 'icon' => '↓'],
        ['id' => 'social-media', 'label' => 'Social Media', 'icon' => '↗'],
        ['id' => 'contact', 'label' => 'Contact & Map', 'icon' => '⌖'],
    ]],
    ['id' => 'system', 'label' => 'System', 'color' => '#44403c', 'items' => [
        ['id' => 'media', 'label' => 'Media Library', 'icon' => '▣'],
        ['id' => 'users', 'label' => 'Users', 'icon' => '☺'],
        ['id' => 'settings', 'label' => 'Settings', 'icon' => '⚙'],
    ]],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo htmlspecialchars($branding['school_name'] ?? 'Excellence Academy'); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%);
            min-height: 100vh;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        html {
            margin: 0;
            padding: 0;
        }
        
        .admin-wrapper {
            display: flex;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        /* Sidebar - Gradient Background */
        .admin-sidebar {
            width: 272px;
            background: #f7f6f3;
            border-right: 1px solid #e7e5e4;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow: hidden;
            z-index: 50;
        }
        
        .sidebar-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }
        
        .sidebar-header-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-logo {
            width: 32px;
            height: 32px;
            background: <?php echo htmlspecialchars($primaryColor); ?>;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .sidebar-title {
            font-size: 20px;
            font-weight: bold;
            color: <?php echo htmlspecialchars($primaryColor); ?>;
        }
        
        .sidebar-subtitle {
            font-size: 14px;
            color: #6b7280;
        }
        
        .nav-search {
            margin: 12px 12px 4px;
            width: calc(100% - 24px);
            padding: 8px 10px;
            border: 1px solid #e7e5e4;
            border-radius: 8px;
            background: #fff;
            color: #1c1917;
            font-size: 13px;
        }

        .nav-tabs {
            flex: 1;
            padding: 4px 0 12px;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .nav-group { margin: 2px 8px 8px; }
        .nav-group.collapsed .nav-tab { display: none; }
        .nav-group-label {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 8px 8px 4px;
            background: none;
            border: none;
            color: #78716c;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            text-align: left;
        }
        .nav-swatch { width: 8px; height: 8px; border-radius: 99px; flex-shrink: 0; }
        .nav-chevron { margin-left: auto; font-size: 10px; color: #a8a29e; }
        
        .nav-tab {
            display: flex;
            align-items: center;
            padding: 8px 10px;
            margin-bottom: 1px;
            border-radius: 8px;
            text-decoration: none;
            color: #44403c;
            transition: background 0.15s;
            cursor: pointer;
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            font-size: 13.5px;
            font-weight: 500;
        }
        
        .nav-tab:hover {
            /* Hover colors applied via inline styles */
        }
        
        .nav-tab.active {
            background: rgba(<?php 
                $rgb = hex2rgb($primaryColor);
                echo $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'];
            ?>, 0.1);
            color: <?php echo htmlspecialchars($primaryColor); ?>;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-left: 4px solid <?php echo htmlspecialchars($primaryColor); ?>;
            font-weight: 600;
        }
        
        .nav-tab-icon {
            font-size: 20px;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        .nav-tab.active .nav-tab-icon {
            color: <?php echo htmlspecialchars($primaryColor); ?>;
        }
        
        .sidebar-footer {
            padding: 24px;
            border-top: 1px solid #e5e7eb;
        }
        
        .logout-btn {
            width: 100%;
            padding: 12px 16px;
            background: none;
            color: #dc2626;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .logout-btn:hover {
            background: #fef2f2;
            color: #991b1b;
            transform: scale(1.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 272px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .admin-topbar {
            background: linear-gradient(135deg, <?php echo htmlspecialchars($primaryColor); ?> 0%, <?php echo htmlspecialchars($secondaryColor); ?> 100%);
            padding: 16px 24px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            height: 80px;
            flex-shrink: 0;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        .topbar-search {
            padding: 10px 15px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            background: rgba(255,255,255,0.1);
            color: white;
            width: 300px;
            font-size: 14px;
        }
        
        .topbar-search::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #9333ea 0%, #ec4899 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 16px;
        }
        
        .user-info {
            display: none;
        }
        
        @media (min-width: 1024px) {
            .user-info {
                display: block;
            }
        }
        
        .admin-badge {
            background: #ec4899;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        
        .admin-badge:hover {
            background: #db2777;
        }
        
        .admin-content {
            flex: 1;
            padding: 24px;
            padding-bottom: 100px; /* Extra padding so save buttons are accessible */
            background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%);
            overflow-y: auto;
        }
        
        .content-container {
            max-width: 1280px;
            margin: 0 auto;
        }
        
        /* Curriculum page specific - reduce card width */
        .content-container .curriculum-card {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .content-card {
            background: transparent;
            backdrop-filter: none;
            border-radius: 12px;
            padding: 30px;
            padding-bottom: 80px; /* Extra padding for save buttons - increased */
            box-shadow: none;
            margin-bottom: 40px; /* More space between cards */
            min-height: 400px; /* Ensure minimum height */
        }
        
        /* Ensure forms have enough bottom padding */
        .content-card form {
            padding-bottom: 20px;
        }
        
        /* Save buttons should be visible */
        .content-card .btn-primary {
            background: <?php echo htmlspecialchars($primaryColor); ?>;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .content-card .btn-primary:hover {
            background: <?php echo htmlspecialchars($secondaryColor); ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .content-card .btn-primary,
        .content-card button[type="submit"] {
            margin-top: 30px;
            margin-bottom: 20px;
        }
        
        .content-card h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #111827;
        }
        
        /* Ensure buttons are always accessible */
        .form-group:last-of-type {
            margin-bottom: 40px;
        }
        
        /* Scroll to bottom button for long forms */
        .scroll-to-bottom {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #800020;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 100;
        }
        
        .scroll-to-bottom:hover {
            background: #a00030;
            transform: scale(1.1);
        }
        
        .scroll-to-bottom.visible {
            display: flex;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 1024px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                width: 280px;
                box-shadow: 2px 0 20px rgba(0,0,0,0.2);
            }
            
            .admin-sidebar.open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .mobile-menu-toggle {
                display: block;
                z-index: 100;
            }
            
            .topbar-search {
                width: 200px;
                font-size: 13px;
            }
            
            .admin-content {
                padding: 20px 15px;
            }
            
            .content-card {
                padding: 20px;
            }
            
            .content-card h2 {
                font-size: 24px;
            }
        }
        
        @media (max-width: 768px) {
            .admin-topbar {
                padding: 12px 15px;
                height: auto;
                min-height: 60px;
                flex-wrap: wrap;
            }
            
            .topbar-left {
                width: 100%;
                justify-content: space-between;
                margin-bottom: 10px;
            }
            
            .topbar-search {
                width: 100%;
                margin-top: 10px;
                order: 3;
            }
            
            .topbar-user {
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .user-info {
                display: none;
            }
            
            .admin-badge {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .admin-content {
                padding: 15px 10px;
                padding-bottom: 100px;
            }
            
            .content-container {
                max-width: 100%;
            }
            
            .content-card {
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .content-card h2 {
                font-size: 20px;
                margin-bottom: 15px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            .form-label {
                font-size: 14px;
                margin-bottom: 6px;
            }
            
            .form-input,
            .form-textarea,
            .form-select {
                font-size: 16px; /* Prevents zoom on iOS */
                padding: 10px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
                width: 100%;
                margin-bottom: 10px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                width: 100%;
            }
            
            .sidebar-header {
                padding: 15px;
                height: auto;
                min-height: 70px;
            }
            
            .sidebar-title {
                font-size: 18px;
            }
            
            .sidebar-subtitle {
                font-size: 12px;
            }
            
            .nav-tabs {
                padding: 20px 12px 0;
            }
            
            .nav-tab {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .nav-tab-icon {
                font-size: 18px;
                margin-right: 10px;
            }
            
            .sidebar-footer {
                padding: 15px;
            }
            
            .logout-btn {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            /* Tables */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                font-size: 12px;
                min-width: 600px;
            }
            
            th, td {
                padding: 8px 6px;
            }
            
            /* Modals in admin */
            .modal-content {
                width: 95%;
                max-width: 95%;
                margin: 10px;
                padding: 15px;
                max-height: 90vh;
                overflow-y: auto;
            }
            
            /* Grid layouts in admin */
            .admin-grid,
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .admin-topbar {
                padding: 10px 12px;
                min-height: 55px;
            }
            
            .mobile-menu-toggle {
                font-size: 20px;
                padding: 5px;
            }
            
            .topbar-search {
                font-size: 14px;
                padding: 8px 12px;
            }
            
            .admin-content {
                padding: 12px 8px;
                padding-bottom: 100px;
            }
            
            .content-card {
                padding: 12px;
            }
            
            .content-card h2 {
                font-size: 18px;
            }
            
            .form-input,
            .form-textarea,
            .form-select {
                padding: 8px;
                font-size: 16px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 13px;
            }
            
            .sidebar-header {
                padding: 12px;
            }
            
            .sidebar-title {
                font-size: 16px;
            }
            
            .nav-tab {
                padding: 8px 10px;
                font-size: 12px;
            }
            
            .nav-tab-icon {
                font-size: 16px;
                margin-right: 8px;
            }
            
            /* Admin tabs specific */
            .admin-tabs-content {
                padding: 10px;
            }
            
            /* Form grids */
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            /* Image previews */
            .image-preview {
                max-width: 100%;
                height: auto;
            }
            
            /* Color pickers */
            input[type="color"] {
                width: 100%;
                height: 50px;
            }
        }
        
        /* Landscape orientation for tablets */
        @media (max-width: 1024px) and (orientation: landscape) {
            .admin-sidebar {
                width: 240px;
            }
            
            .admin-content {
                padding: 15px;
            }
        }
        
        /* Overlay for mobile sidebar */
        @media (max-width: 1024px) {
            .admin-sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            
            .admin-sidebar.open ~ .admin-sidebar-overlay {
                display: block;
            }
        }
        
        /* Toast Animations */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Mobile Sidebar Overlay -->
        <div class="admin-sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <div class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-header-content">
                    <div class="sidebar-logo">🎓</div>
                    <div>
                        <div class="sidebar-title">EduSystem</div>
                        <div class="sidebar-subtitle">Admin Portal</div>
                    </div>
                </div>
                <button class="mobile-menu-toggle" onclick="toggleSidebar()" style="display: none;">✕</button>
            </div>
            
            <input type="search" class="nav-search" id="adminSearch" placeholder="Find a section" oninput="filterAdminNav(this.value)" aria-label="Find a section">
            <nav class="nav-tabs">
                <?php foreach ($navGroups as $group):
                    $containsActive = false;
                    foreach ($group['items'] as $item) {
                        if ($item['id'] === $activeTab) $containsActive = true;
                    }
                ?>
                    <div class="nav-group" data-group="<?php echo htmlspecialchars($group['id']); ?>">
                        <button type="button" class="nav-group-label" onclick="toggleNavGroup('<?php echo htmlspecialchars($group['id']); ?>')">
                            <span class="nav-swatch" style="background: <?php echo htmlspecialchars($group['color']); ?>"></span>
                            <span><?php echo htmlspecialchars($group['label']); ?></span>
                            <span class="nav-chevron">▾</span>
                        </button>
                        <?php foreach ($group['items'] as $tab):
                            $isActive = $activeTab === $tab['id'];
                        ?>
                            <a href="?tab=<?php echo htmlspecialchars($tab['id']); ?>"
                               class="nav-tab <?php echo $isActive ? 'active' : ''; ?>"
                               <?php if ($isActive): ?>id="active-nav-tab" style="background: <?php echo htmlspecialchars($group['color']); ?>; color: #fff;"<?php endif; ?>>
                                <span class="nav-tab-icon"><?php echo $tab['icon']; ?></span>
                                <span><?php echo htmlspecialchars($tab['label']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <span>🚪</span>
                    <span>Sign Out</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
                    <input type="search" class="topbar-search" placeholder="Find a section" aria-label="Find a section" oninput="var box=document.getElementById('adminSearch'); if(box){box.value=this.value;} filterAdminNav(this.value);">
                </div>
                <div class="topbar-user">
                    <div class="user-avatar"><?php echo strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)); ?></div>
                    <div class="user-info">
                        <div style="font-weight: 600; font-size: 14px;">Administrator</div>
                        <div style="font-size: 12px; opacity: 0.8;">Admin</div>
                    </div>
                    <button class="admin-badge">ADMIN</button>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="admin-content">
                <div class="content-container">
                    <?php
                    // Include the appropriate content based on active tab
                    $contentFile = __DIR__ . '/admin-tabs/' . $activeTab . '.php';
                    if (file_exists($contentFile)) {
                        include $contentFile;
                    } else {
                        // Default dashboard
                        include __DIR__ . '/admin-tabs/dashboard.php';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleNavGroup(id) {
            const group = document.querySelector('.nav-group[data-group="' + id + '"]');
            if (!group) return;
            const willCollapse = !group.classList.contains('collapsed');
            group.classList.toggle('collapsed', willCollapse);
            const chevron = group.querySelector('.nav-chevron');
            if (chevron) chevron.textContent = willCollapse ? '▸' : '▾';
            const open = JSON.parse(localStorage.getItem('adminNavOpen') || '{}');
            open[id] = !willCollapse;
            localStorage.setItem('adminNavOpen', JSON.stringify(open));
        }

        function filterAdminNav(query) {
            const q = String(query || '').trim().toLowerCase();
            document.querySelectorAll('.nav-group').forEach(group => {
                let visible = 0;
                group.querySelectorAll('.nav-tab').forEach(tab => {
                    const show = !q || tab.textContent.toLowerCase().indexOf(q) !== -1;
                    tab.style.display = show ? 'flex' : 'none';
                    if (show) visible += 1;
                });
                group.style.display = visible ? '' : 'none';
                if (q) group.classList.remove('collapsed');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            let open = {};
            try { open = JSON.parse(localStorage.getItem('adminNavOpen') || '{}'); } catch (e) { open = {}; }
            document.querySelectorAll('.nav-group').forEach(group => {
                const id = group.getAttribute('data-group');
                const hasActive = group.querySelector('.nav-tab.active');
                if (open[id] === false && !hasActive) {
                    group.classList.add('collapsed');
                    const chevron = group.querySelector('.nav-chevron');
                    if (chevron) chevron.textContent = '▸';
                }
            });
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.admin-sidebar-overlay');
            sidebar.classList.toggle('open');
            
            // Prevent body scroll when sidebar is open on mobile
            if (window.innerWidth <= 1024) {
                if (sidebar.classList.contains('open')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
        }
        
        // Close sidebar when clicking overlay
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.querySelector('.admin-sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', function() {
                    toggleSidebar();
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                const sidebar = document.getElementById('sidebar');
                const toggleBtn = document.querySelector('.mobile-menu-toggle');
                if (window.innerWidth <= 1024 && sidebar && sidebar.classList.contains('open')) {
                    if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                        toggleSidebar();
                    }
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                const sidebar = document.getElementById('sidebar');
                if (window.innerWidth > 1024) {
                    sidebar.classList.remove('open');
                    document.body.style.overflow = '';
                }
            });
        });
        
        // Show/hide scroll to bottom button
        window.addEventListener('scroll', function() {
            const scrollBtn = document.getElementById('scrollToBottom');
            if (scrollBtn) {
                if (window.scrollY > 300) {
                    scrollBtn.classList.add('visible');
                } else {
                    scrollBtn.classList.remove('visible');
                }
            }
        });
        
        function scrollToBottom() {
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        }
        
        // Scroll active sidebar tab into view
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = document.getElementById('active-nav-tab') || document.querySelector('.nav-tab.active');
            if (activeTab) {
                // Scroll the active tab into view with a slight delay to ensure sidebar is rendered
                setTimeout(() => {
                    activeTab.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
                    // Also ensure the sidebar itself scrolls
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar) {
                        const navTabs = sidebar.querySelector('.nav-tabs');
                        if (navTabs) {
                            const activeRect = activeTab.getBoundingClientRect();
                            const navRect = navTabs.getBoundingClientRect();
                            const scrollTop = navTabs.scrollTop;
                            const targetScroll = scrollTop + activeRect.top - navRect.top - (navRect.height / 2) + (activeRect.height / 2);
                            navTabs.scrollTo({ top: targetScroll, behavior: 'smooth' });
                        }
                    }
                }, 200);
            }
            
            // Auto-attach AJAX handler to all admin forms on page load
            // EXCLUDE addPersonForm - it's a modal form that should not auto-save
            const forms = document.querySelectorAll('form[action*="api/content"], form[action*="api/"]');
            forms.forEach(form => {
                // Skip addPersonForm - it's handled separately
                if (form.id === 'addPersonForm') {
                    return;
                }
                if (!form.onsubmit) {
                    form.addEventListener('submit', function(e) {
                        handleAdminFormSubmit(e, this);
                    });
                }
            });
        });
        
        // Auto-save debounce timers
        const autoSaveTimers = {};
        
        // Auto-save function with debounce
        function autoSaveForm(form) {
            // NEVER auto-save addPersonForm
            if (form && form.id === 'addPersonForm') {
                return;
            }
            const formId = form.id || form.action;
            if (!formId) return;
            
            // Clear existing timer
            if (autoSaveTimers[formId]) {
                clearTimeout(autoSaveTimers[formId]);
            }
            
            // Set new timer (save after 2 seconds of no changes)
            autoSaveTimers[formId] = setTimeout(() => {
                saveFormSilently(form);
            }, 2000);
        }
        
        // Show toast notification
        function showToast(message, type = 'success') {
            // Suppress "Fatal error" messages globally - never show them to users
            if (type === 'error' && message && (message.toLowerCase().includes('fatal error') || message.toLowerCase().includes('server error: fatal'))) {
                console.error('Fatal error suppressed from toast:', message);
                return; // Don't show the toast
            }
            
            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.admin-toast');
            existingToasts.forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = 'admin-toast';
            const bgColor = type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)';
            const icon = type === 'success' ? '✅' : '❌';
            
            toast.style.cssText = `
                position: fixed;
                top: 100px;
                right: 30px;
                background: ${bgColor};
                color: white;
                padding: 16px 24px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 10000;
                font-weight: 600;
                font-size: 16px;
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 300px;
                animation: slideInRight 0.3s ease-out;
            `;
            
            toast.innerHTML = `
                <span style="font-size: 24px;">${icon}</span>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
        
        // Silent save (no alerts, just visual indicator)
        function saveFormSilently(form) {
            // NEVER process addPersonForm - it's a modal form for local data entry only
            if (form && form.id === 'addPersonForm') {
                console.warn('saveFormSilently called on addPersonForm - this should not happen');
                return;
            }
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            const originalBg = submitBtn ? submitBtn.style.background : '';
            
            if (submitBtn) {
                submitBtn.textContent = '💾 Saving...';
                submitBtn.style.opacity = '0.7';
                submitBtn.disabled = true;
            }
            
            // Convert form data to object (handle nested arrays/objects)
            const formData = new FormData(form);
            let data = {};
            
            // Special handling for people_behind_school form
            if (form.id === 'peopleForm') {
                // CRITICAL: Update people_json field BEFORE collecting form data
                // Call the update function if it exists
                if (typeof window.updatePeopleJsonField === 'function') {
                    try {
                        window.updatePeopleJsonField();
                    } catch (e) {
                        console.error('Error calling updatePeopleJsonField:', e);
                    }
                }
                
                // Get people_json from hidden field FIRST (should be set by updatePeopleJsonField)
                const peopleJsonField = form.querySelector('#people_json');
                let peopleJsonString = '[]';
                
                if (peopleJsonField && peopleJsonField.value) {
                    peopleJsonString = peopleJsonField.value;
                } else {
                    // Fallback: Try to get from global peopleList
                    const globalPeopleList = window.peopleList || (typeof peopleList !== 'undefined' ? peopleList : []);
                    
                    if (Array.isArray(globalPeopleList) && globalPeopleList.length > 0) {
                        // Collect data from form fields for each person
                        const formPeopleData = {};
                        formData.forEach((value, key) => {
                            const match = key.match(/^people\[(\d+)\]\[(.+)\]$/);
                            if (match) {
                                const index = parseInt(match[1]);
                                const field = match[2];
                                if (!formPeopleData[index]) {
                                    formPeopleData[index] = {};
                                }
                                formPeopleData[index][field] = value;
                            }
                        });
                        
                        // Merge peopleList with form field data
                        const finalPeopleArray = [];
                        globalPeopleList.forEach((person, index) => {
                            const finalPerson = { ...person };
                            
                            // Override with form field data if it exists
                            if (formPeopleData[index]) {
                                Object.keys(formPeopleData[index]).forEach(key => {
                                    if (formPeopleData[index][key]) {
                                        finalPerson[key] = formPeopleData[index][key];
                                    }
                                });
                            }
                            
                            // Validate required fields
                            const fullName = (finalPerson.full_name || '').trim();
                            const title = (finalPerson.title || '').trim();
                            const biography = (finalPerson.biography || '').trim();
                            
                            if (fullName && title && biography) {
                                finalPeopleArray.push(finalPerson);
                            }
                        });
                        
                        peopleJsonString = JSON.stringify(finalPeopleArray);
                        
                        // Update the hidden field for next time
                        if (peopleJsonField) {
                            peopleJsonField.value = peopleJsonString;
                        }
                    }
                }
                
                // ALWAYS set people_json in data object - CRITICAL
                data.people_json = peopleJsonString;
                
                // Add all other form fields
                formData.forEach((value, key) => {
                    if (key !== 'people_json' && !key.startsWith('people[')) {
                        data[key] = value;
                    }
                });
            } else {
                // Standard form processing
                formData.forEach((value, key) => {
                    if (key.endsWith('_text')) {
                        // Skip color text fields
                        return;
                    }
                    
                    // Handle nested keys like "sections[0][image]" or "payment_details[bank_name]"
                    if (key.includes('[') && key.includes(']')) {
                        // Parse multi-level nested keys like sections[0][image]
                        const keys = [];
                        let currentKey = '';
                        let inBrackets = false;
                        let bracketContent = '';
                        
                        for (let i = 0; i < key.length; i++) {
                            const char = key[i];
                            if (char === '[') {
                                if (currentKey) {
                                    keys.push(currentKey);
                                    currentKey = '';
                                }
                                inBrackets = true;
                                bracketContent = '';
                            } else if (char === ']') {
                                if (bracketContent) {
                                    // Check if it's numeric (array index) or string (object key)
                                    const numKey = parseInt(bracketContent);
                                    keys.push(isNaN(numKey) ? bracketContent : numKey);
                                }
                                inBrackets = false;
                                bracketContent = '';
                            } else if (inBrackets) {
                                bracketContent += char;
                            } else {
                                currentKey += char;
                            }
                        }
                        
                        if (currentKey) {
                            keys.push(currentKey);
                        }
                        
                        // Build nested structure
                        let current = data;
                        for (let i = 0; i < keys.length - 1; i++) {
                            const k = keys[i];
                            const nextKey = keys[i + 1];
                            
                            if (typeof k === 'number') {
                                // Array index
                                // Ensure parent is an array
                                if (!Array.isArray(current)) {
                                    // Convert object to array
                                    const obj = current;
                                    current = [];
                                    Object.keys(obj).forEach(key => {
                                        const idx = parseInt(key);
                                        if (!isNaN(idx) && idx >= 0) {
                                            current[idx] = obj[key];
                                        }
                                    });
                                }
                                // Ensure array has enough elements
                                while (current.length <= k) {
                                    current.push(null);
                                }
                                // Create next level (object if next key is string, array if numeric)
                                if (current[k] === null || current[k] === undefined) {
                                    current[k] = typeof nextKey === 'number' ? [] : {};
                                }
                                current = current[k];
                            } else {
                                // Object key
                                if (!current[k]) {
                                    // Create next level based on next key type
                                    current[k] = typeof nextKey === 'number' ? [] : {};
                                } else if (typeof nextKey === 'number' && !Array.isArray(current[k])) {
                                    // Next key is numeric but current is object - convert to array
                                    const obj = current[k];
                                    current[k] = [];
                                    Object.keys(obj).forEach(key => {
                                        const idx = parseInt(key);
                                        if (!isNaN(idx) && idx >= 0) {
                                            current[k][idx] = obj[key];
                                        }
                                    });
                                }
                                current = current[k];
                            }
                        }
                        
                        // Set the final value
                        const finalKey = keys[keys.length - 1];
                        if (typeof finalKey === 'number') {
                            // Final key is numeric - ensure parent is array
                            if (!Array.isArray(current)) {
                                const obj = current;
                                current = [];
                                Object.keys(obj).forEach(key => {
                                    const idx = parseInt(key);
                                    if (!isNaN(idx) && idx >= 0) {
                                        current[idx] = obj[key];
                                    }
                                });
                            }
                            while (current.length <= finalKey) {
                                current.push(null);
                            }
                            current[finalKey] = value;
                        } else {
                            // Final key is string - set value
                            current[finalKey] = value;
                        }
                    } else if (key.endsWith('[]')) {
                        // Handle array fields
                        const arrayKey = key.slice(0, -2);
                        if (!data[arrayKey]) {
                            data[arrayKey] = [];
                        }
                        data[arrayKey].push(value);
                    } else {
                        data[key] = value;
                    }
                });
            }
            
            // Sync color picker values (use picker value, not text field)
            form.querySelectorAll('input[type="color"]').forEach(colorInput => {
                if (colorInput.name) {
                    data[colorInput.name] = colorInput.value;
                }
            });
            
            // Convert objects with numeric keys to arrays (for proper JSON serialization)
            function convertNumericKeysToArrays(obj) {
                if (Array.isArray(obj)) {
                    return obj.map(item => convertNumericKeysToArrays(item));
                } else if (obj !== null && typeof obj === 'object') {
                    const keys = Object.keys(obj);
                    // Check if all keys are sequential numeric indices (0, 1, 2, ...)
                    const numericKeys = keys.map(k => parseInt(k)).filter(k => !isNaN(k) && k >= 0);
                    const isSequentialArray = numericKeys.length === keys.length && 
                        numericKeys.length > 0 &&
                        numericKeys.every((k, i) => k === i);
                    
                    if (isSequentialArray) {
                        // Convert to array
                        return numericKeys.map(k => convertNumericKeysToArrays(obj[k]));
                    } else {
                        // Keep as object, but recurse into nested objects
                        const result = {};
                        keys.forEach(key => {
                            result[key] = convertNumericKeysToArrays(obj[key]);
                        });
                        return result;
                    }
                }
                return obj;
            }
            
            // Convert data structure before sending
            data = convertNumericKeysToArrays(data);
            
            // Send AJAX request with timeout (reduced for faster feedback)
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
            
            // Get the correct action URL - handle relative paths
            let actionUrl = form.getAttribute('action') || form.action;
            
            // If action is empty, points to admin.php, or is the current page, fix it
            if (!actionUrl || actionUrl === '' || actionUrl.includes('admin.php') || actionUrl === window.location.href.split('?')[0]) {
                // Use the action attribute directly if it exists
                const actionAttr = form.getAttribute('action');
                if (actionAttr && actionAttr !== '' && !actionAttr.includes('admin.php')) {
                    actionUrl = actionAttr;
                } else if (form.id === 'peopleForm') {
                    // Fallback for peopleForm
                    actionUrl = 'api/content/people-behind-school.php';
                } else {
                    // Try to extract from form action property (might be absolute URL)
                    actionUrl = form.action;
                }
            }
            
            // Resolve relative URL to absolute
            if (actionUrl && !actionUrl.startsWith('http://') && !actionUrl.startsWith('https://') && !actionUrl.startsWith('/')) {
                // Relative path - resolve from current page
                const currentPath = window.location.pathname;
                const basePath = currentPath.substring(0, currentPath.lastIndexOf('/'));
                actionUrl = basePath + '/' + actionUrl;
            } else if (actionUrl && !actionUrl.startsWith('http://') && !actionUrl.startsWith('https://') && actionUrl.startsWith('/')) {
                // Absolute path from root
                actionUrl = window.location.origin + actionUrl;
            } else if (actionUrl && !actionUrl.startsWith('http://') && !actionUrl.startsWith('https://')) {
                // Try to create absolute URL
                try {
                    actionUrl = new URL(actionUrl, window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'))).href;
                } catch (e) {
                    // Fallback: construct manually
                    const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
                    actionUrl = window.location.origin + basePath + '/' + actionUrl;
                }
            }
            
            if (!data.people_json && form.id === 'peopleForm') {
                data.people_json = '[]';
            }
            
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
                signal: controller.signal
            })
            .then(async response => {
                // Try to parse response as JSON first
                let result;
                const text = await response.text();
                
                if (!text || text.trim() === '') {
                    // Empty response
                    if (!response.ok) {
                        result = { error: `Server returned empty response (Status: ${response.status})` };
                    } else {
                        result = { success: true, message: 'Settings saved successfully!' };
                    }
                } else {
                    try {
                        result = JSON.parse(text);
                    } catch (e) {
                        // If parsing fails, the server might have returned HTML (PHP error page)
                        console.error('Failed to parse response as JSON. Response was:', text.substring(0, 500));
                        // Check if it looks like HTML
                        if (text.trim().startsWith('<')) {
                            // Try to extract error message from HTML with better patterns
                            let errorText = '';
                            
                            // Try multiple patterns to extract error
                            const patterns = [
                                /Fatal error[^<]*:?\s*([^<]+)/i,
                                /Parse error[^<]*:?\s*([^<]+)/i,
                                /Warning[^<]*:?\s*([^<]+)/i,
                                /Notice[^<]*:?\s*([^<]+)/i,
                                /Error[^<]*:?\s*([^<]+)/i,
                                /<title>([^<]+)<\/title>/i,
                                /<h1[^>]*>([^<]+)<\/h1>/i,
                                /<p[^>]*>([^<]+)<\/p>/i
                            ];
                            
                            for (const pattern of patterns) {
                                const match = text.match(pattern);
                                if (match && match[1] && match[1].trim().length > 3) {
                                    errorText = match[1].trim().substring(0, 300);
                                    break;
                                } else if (match && match[0] && match[0].trim().length > 3) {
                                    errorText = match[0].trim().substring(0, 300);
                                    break;
                                }
                            }
                            
                            if (!errorText || errorText.length < 3) {
                                // Try to extract from body text
                                const bodyMatch = text.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
                                if (bodyMatch) {
                                    const bodyText = bodyMatch[1].replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                                    if (bodyText.length > 10) {
                                        errorText = bodyText.substring(0, 300);
                                    }
                                }
                            }
                            
                            if (!errorText || errorText.length < 3) {
                                errorText = 'Server returned HTML error page. Check PHP error logs for details.';
                            } else {
                                // Suppress "Fatal error" messages from being displayed to users
                                if (errorText.toLowerCase().includes('fatal error')) {
                                    errorText = 'An error occurred. Please try again later.';
                                } else {
                                    errorText = 'Server error: ' + errorText;
                                }
                            }
                            
                            result = { error: errorText };
                        } else {
                            result = { error: 'Invalid response format from server: ' + text.substring(0, 200) };
                        }
                    }
                }
                
                if (!response.ok) {
                    // Extract error message from response
                    let errorMsg = result.error || result.message || `Server error (${response.status})`;
                    // Suppress "Fatal error" messages
                    if (errorMsg.toLowerCase().includes('fatal error') || errorMsg.toLowerCase().includes('server error: fatal')) {
                        errorMsg = 'An error occurred. Please try again later.';
                    }
                    throw new Error(errorMsg);
                }
                
                clearTimeout(timeoutId);
                return result;
            })
            .then(result => {
                if (result.error) {
                    console.error('Save error:', result.error);
                    // Suppress "Fatal error" messages from being displayed to users
                    let errorMsg = result.error || 'An unknown error occurred. Please check the console for details.';
                    if (errorMsg.toLowerCase().includes('fatal error') || errorMsg.toLowerCase().includes('server error: fatal')) {
                        errorMsg = 'An error occurred. Please try again later.';
                    }
                    // Only show error if it's not a fatal error (which we've suppressed)
                    if (!errorMsg.toLowerCase().includes('fatal error')) {
                        showToast(errorMsg, 'error');
                    }
                    
                    // Also log to console with full details
                    console.error('Full error details:', {
                        error: result.error,
                        formId: form.id,
                        formAction: form.action,
                        dataKeys: Object.keys(data),
                        peopleJsonLength: data.people_json ? data.people_json.length : 0
                    });
                    
                    if (submitBtn) {
                        submitBtn.textContent = '❌ Error';
                        submitBtn.style.opacity = '1';
                        submitBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                        submitBtn.disabled = false;
                        setTimeout(() => {
                            submitBtn.textContent = originalText;
                            submitBtn.style.background = originalBg;
                        }, 6000); // Show error longer
                    }
                } else {
                    // Show success message
                    const successMsg = result.message || 'Settings saved successfully!';
                    showToast(successMsg, 'success');
                    
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
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Form submission error:', error);
                // Extract the actual error message from the error object
                let errorMessage = error.message || 'Failed to save. Please try again.';
                
                // Suppress "Fatal error" messages
                if (errorMessage.toLowerCase().includes('fatal error') || errorMessage.toLowerCase().includes('server error: fatal')) {
                    errorMessage = 'An error occurred. Please try again later.';
                }
                
                // Handle timeout specifically
                if (error.name === 'AbortError' || errorMessage.includes('aborted')) {
                    errorMessage = 'Request timed out. The server may be slow or unresponsive. Please try again.';
                } else if (errorMessage.includes('Failed to fetch') || errorMessage.includes('NetworkError')) {
                    errorMessage = 'Network error. Please check your connection and try again.';
                } else if (errorMessage.includes('Network response was not ok')) {
                    errorMessage = 'An error occurred. Please try again later.';
                }
                
                // Only show error if it's not a fatal error (which we've suppressed)
                if (!errorMessage.toLowerCase().includes('fatal error')) {
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
        }
        
        // Handle admin form submissions with AJAX - saves immediately to DB
        function handleAdminFormSubmit(event, form) {
            // NEVER handle addPersonForm - it's a modal form for local data entry only
            if (form && form.id === 'addPersonForm') {
                console.warn('addPersonForm should not be handled by handleAdminFormSubmit');
                return true; // Let the form's own handler deal with it
            }
            event.preventDefault();
            saveFormSilently(form);
            return false;
        }
        
        // Initialize auto-save on all forms
        document.addEventListener('DOMContentLoaded', function() {
            // Add auto-save to all admin forms
            // EXCLUDE addPersonForm - it's a modal form for local data entry only
            document.querySelectorAll('form[id$="Form"]').forEach(form => {
                // Skip addPersonForm - it should never auto-save
                if (form.id === 'addPersonForm') {
                    return;
                }
                // Add input listeners for auto-save
                form.querySelectorAll('input, textarea, select').forEach(input => {
                    input.addEventListener('input', () => autoSaveForm(form));
                    input.addEventListener('change', () => autoSaveForm(form));
                });
            });
        });
    </script>
    
    <!-- Scroll to Bottom Button -->
    <button id="scrollToBottom" class="scroll-to-bottom" onclick="scrollToBottom()" title="Scroll to bottom">
        ⬇️
    </button>
    
    <!-- Media Library Modal -->
    <div id="mediaLibraryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: white; border-radius: 12px; width: 100%; max-width: 1200px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            <div style="padding: 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 24px; font-weight: bold; color: #333;">🖼️ Media Library</h3>
                <button onclick="closeMediaLibrary()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">✕</button>
            </div>
            
            <div style="flex: 1; overflow-y: auto; padding: 20px;">
                <div id="mediaLibraryContent" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                    <div style="text-align: center; padding: 40px; color: #999;">Loading media...</div>
                </div>
            </div>
            
            <div style="padding: 20px; border-top: 1px solid #e0e0e0; text-align: right;">
                <button onclick="closeMediaLibrary()" class="btn" style="background: #6b7280; color: white;">Close</button>
            </div>
        </div>
    </div>
    
    <script>
        let currentImageField = '';
        window.currentImageField = ''; // Make it globally accessible
        let mediaItems = [];
        
        function openMediaLibrary(fieldName) {
            currentImageField = fieldName;
            window.currentImageField = fieldName; // Also set global
            document.getElementById('mediaLibraryModal').style.display = 'flex';
            loadMediaLibrary();
        }
        
        function closeMediaLibrary() {
            document.getElementById('mediaLibraryModal').style.display = 'none';
            currentImageField = '';
            window.currentImageField = ''; // Also clear global
        }
        
        function loadMediaLibrary() {
            const controller = typeof AbortController === 'function' ? new AbortController() : null;
            const timer = setTimeout(function () { if (controller) controller.abort(); }, 8000);
            fetch('api/media/list.php', {
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller ? controller.signal : undefined
            })
                .then(response => {
                    clearTimeout(timer);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    mediaItems = data.media || [];
                    if (mediaItems.length === 0) {
                        document.getElementById('mediaLibraryContent').innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">No media files found. Upload files in the Media Library tab.</div>';
                    } else {
                        displayMedia();
                    }
                })
                .catch(error => {
                    console.error('Error loading media:', error);
                    document.getElementById('mediaLibraryContent').innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">Failed to load media: ' + error.message + '<br><br>Please check the console for details.</div>';
                });
        }
        
        function displayMedia() {
            const container = document.getElementById('mediaLibraryContent');
            if (!container) return;
            
            if (!mediaItems || mediaItems.length === 0) {
                container.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;"><div style="font-size: 48px; margin-bottom: 10px;">🖼️</div><div>No media files found.</div><div style="margin-top: 10px; font-size: 12px;">Upload files in the Media Library tab.</div></div>';
                return;
            }
            
            container.innerHTML = mediaItems.map(item => {
                const imageUrl = item.file_path || item.file_url || '';
                const safeUrl = imageUrl.replace(/'/g, "\\'");
                const safeName = (item.original_name || 'Unknown').replace(/'/g, "\\'").substring(0, 20);
                return `
                    <div onclick="selectImage('${safeUrl}')" 
                         style="cursor: pointer; border: 2px solid #e0e0e0; border-radius: 8px; overflow: hidden; transition: all 0.3s; background: white;"
                         onmouseover="this.style.borderColor='#2563eb'; this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'"
                         onmouseout="this.style.borderColor='#e0e0e0'; this.style.transform='scale(1)'; this.style.boxShadow='none'">
                        <div style="width: 100%; height: 120px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                            ${imageUrl ? `
                                <img src="${safeUrl}" 
                                     alt="${safeName}" 
                                     style="max-width: 100%; max-height: 100%; object-fit: cover; width: 100%; height: 100%;"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'150\' height=\'120\'%3E%3Crect fill=\'%23f0f0f0\' width=\'150\' height=\'120\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dominant-baseline=\'middle\'%3E🖼️%3C/text%3E%3C/svg%3E';">
                            ` : '<span style="color:#999; font-size: 24px;">🖼️</span>'}
                        </div>
                        <div style="padding: 8px; font-size: 11px; color: #666; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; background: white;">
                            ${safeName}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function selectImage(imageUrl) {
            if (!currentImageField && !window.currentImageField) return;
            
            const fieldId = currentImageField || window.currentImageField;
            
            // Check if this is a resource section image (stored in window object)
            if (fieldId.startsWith('resource_section_image_')) {
                const input = window['resourceImageInput_' + fieldId];
                if (input) {
                    input.value = imageUrl;
                    // Update preview if exists
                    const container = input.closest('div[style*="display: flex"]');
                    if (container) {
                        const existingImg = container.querySelector('img');
                        if (existingImg) {
                            existingImg.src = imageUrl;
                            existingImg.style.display = 'block';
                        } else {
                            // Add preview image
                            const previewDiv = container.querySelector('div[style*="flex-shrink: 0"]');
                            if (!previewDiv) {
                                const newPreview = document.createElement('div');
                                newPreview.style.cssText = 'flex-shrink: 0;';
                                newPreview.innerHTML = `<img src="${imageUrl}" alt="Section image preview" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;" onerror="this.onerror=null; this.style.display='none';">`;
                                container.insertBefore(newPreview, container.firstChild);
                            }
                        }
                    }
                    closeMediaLibrary();
                    return;
                }
            }
            
            // Standard handling for fields with IDs
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = imageUrl;
                // Trigger change event
                field.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Update image preview if it exists
                updateImagePreview(fieldId, imageUrl);
            }
            
            // Special handling for hero slides
            if (fieldId === 'hero_slides') {
                if (typeof heroSlides !== 'undefined') {
                    heroSlides.push(imageUrl);
                    if (typeof updateSlidesDisplay === 'function') {
                        updateSlidesDisplay();
                    }
                }
            }
            
            // Call selectMediaImage if it exists (for custom handlers)
            if (typeof window.selectMediaImage === 'function') {
                window.selectMediaImage(imageUrl);
            }
            
            closeMediaLibrary();
        }
        
        // Function to update image preview when image is selected
        function updateImagePreview(fieldId, imageUrl) {
            if (!imageUrl) return;
            
            const field = document.getElementById(fieldId);
            if (!field) return;
            
            // Find the parent container that has the preview area
            const parentContainer = field.closest('div[style*="display: flex"]') || field.parentElement?.parentElement;
            if (!parentContainer) return;
            
            // Look for the preview container (usually has flex-shrink: 0)
            const previewContainer = parentContainer.querySelector('div[style*="flex-shrink: 0"]');
            if (!previewContainer) return;
            
            // Check if there's already an img tag
            let previewImg = previewContainer.querySelector('img');
            
            if (previewImg) {
                // Update existing preview image
                previewImg.src = imageUrl;
                previewImg.style.display = 'block';
            } else {
                // Check if it's a placeholder div
                const placeholderDivs = previewContainer.querySelectorAll('div');
                let isPlaceholder = false;
                for (let div of placeholderDivs) {
                    if (div.textContent && div.textContent.includes('No image')) {
                        isPlaceholder = true;
                        break;
                    }
                }
                
                if (isPlaceholder) {
                    // Replace placeholder with actual image
                    previewContainer.innerHTML = `
                        <div style="position: relative;">
                            <img src="${imageUrl}" 
                                 alt="Page header preview" 
                                 style="width: 160px; height: 96px; object-fit: cover; border-radius: 8px; border: 2px solid #10b981; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 160px; height: 96px; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center; position: absolute; top: 0; left: 0;">
                                <div style="text-align: center;">
                                    <span style="font-size: 24px; color: #999;">🖼️</span>
                                    <p style="font-size: 11px; color: #999; margin-top: 5px;">Failed to load</p>
                                </div>
                            </div>
                            <p style="text-align: center; font-size: 11px; color: #666; margin-top: 5px;">Preview</p>
                        </div>
                    `;
                }
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('mediaLibraryModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMediaLibrary();
            }
        });
    </script>
</body>
</html>
