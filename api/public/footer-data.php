<?php
/**
 * Public API - Footer Data
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';

try {
    $db = getDB();
    $footerContent = $db->fetchOne("SELECT * FROM footer_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null;
    $socialMedia = $db->fetchAll("SELECT * FROM social_media WHERE is_active = 1 ORDER BY created_at DESC") ?: [];
    $branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null;
    $contactInfo = $db->fetchOne("SELECT * FROM contact_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1") ?: null;
    
    $socialLinks = [];
    $quickLinks = [];
    if ($footerContent) {
        if (!empty($footerContent['social_links'])) {
            $socialLinks = json_decode($footerContent['social_links'], true) ?: [];
        }
        if (!empty($footerContent['quick_links'])) {
            $quickLinks = json_decode($footerContent['quick_links'], true) ?: [];
        }
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => [
            'footerContent' => $footerContent,
            'socialMedia' => $socialMedia,
            'socialLinks' => $socialLinks,
            'quickLinks' => $quickLinks,
            'branding' => $branding,
            'contactInfo' => $contactInfo
        ]
    ]);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
