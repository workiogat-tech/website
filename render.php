<?php
/**
 * Sends each public page with real image addresses already in the HTML.
 * The browser can download the logo, hero photo, and section photos
 * before any page script runs.
 */
$_GET['__path'] = 'public/page';
ini_set('display_errors', '0');
error_reporting(E_ALL);

$requested = str_replace('\\', '/', (string) ($_GET['page'] ?? 'index.html'));
$requested = ltrim($requested, '/');
if ($requested === '' || strpos($requested, '..') !== false) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

if (preg_match('#^admin-tabs/[a-z0-9-]+\.html$#', $requested)) {
    $adminPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $requested);
    if (!is_file($adminPath)) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache');
    readfile($adminPath);
    exit;
}

require_once __DIR__ . '/config/config.php';

$page = basename($requested);
if (!preg_match('/^[a-z0-9-]+\.html$/', $page)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$path = __DIR__ . DIRECTORY_SEPARATOR . $page;
if (!is_file($path)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$html = file_get_contents($path);
if ($html === false) {
    http_response_code(500);
    echo 'Could not read the page';
    exit;
}

try {
    $db = getDB();
    $html = embedPageImages($db, $html, $page);
} catch (Throwable $e) {
    error_log('Page images were left as saved HTML: ' . $e->getMessage());
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');
echo $html;

function embedPageImages($db, $html, $page) {
    $plan = pageImagePlan($page);
    $branding = readTable($db, 'branding');
    $brand = $branding[0] ?? [];
    $schoolName = trim((string) ($brand['school_name'] ?? 'Zion Schools'));
    $logo = firstImage([$brand['logo'] ?? '']);

    $heroUrls = [];
    foreach ($plan['hero'] as $table) {
        $preferred = heroUrlsFromRows(readSpecial($db, $table));
        if ($preferred) {
            $heroUrls = $preferred;
            break;
        }
    }

    $contentUrls = [];
    foreach ($plan['content'] as $table) {
        collectImageUrls(readSpecial($db, $table), $contentUrls);
    }
    $contentUrls = uniqueUrls($contentUrls);
    if ($heroUrls) {
        removeOnce($contentUrls, $heroUrls[0]);
    }

    $html = injectHeader($html, $logo, $schoolName);
    $html = injectHero($html, $heroUrls[0] ?? '');
    $html = injectPlaceholders($html, $contentUrls);
    $html = injectWhyChoose($html, $contentUrls);
    $html = injectContainers($html, $contentUrls);
    return injectPreloads($html, $logo, $heroUrls);
}

function pageImagePlan($page) {
    $plans = [
        'index.html' => ['hero' => ['hero_content'], 'content' => ['three_column_sections', 'school_life_items']],
        'about.html' => ['hero' => ['about_content'], 'content' => ['about_content', 'why_choose_content']],
        'clubs.html' => ['hero' => ['clubs_content'], 'content' => ['clubs_content']],
        'curriculum.html' => ['hero' => ['curriculum_content'], 'content' => ['curriculum_content', 'curriculum_programs']],
        'facilities.html' => ['hero' => ['facilities_settings'], 'content' => ['facilities']],
        'teachers.html' => ['hero' => ['teachers_settings'], 'content' => ['teachers']],
        'testimonials.html' => ['hero' => ['testimonials_settings'], 'content' => ['testimonials']],
        'gallery.html' => ['hero' => ['gallery_settings'], 'content' => ['gallery_media']],
        'activities.html' => ['hero' => ['activities_settings'], 'content' => ['activities']],
        'events.html' => ['hero' => ['activities_settings'], 'content' => ['activities']],
        'news.html' => ['hero' => ['news_settings'], 'content' => ['news']],
        'announcements.html' => ['hero' => ['announcements_settings'], 'content' => ['announcements']],
        'admission.html' => ['hero' => ['admission_content'], 'content' => ['admission_content']],
        'school-background.html' => ['hero' => ['school_background_content'], 'content' => ['school_background_content']],
        'sponsorship.html' => ['hero' => ['sponsorship_content'], 'content' => ['sponsorship_content']],
        'give-back.html' => ['hero' => ['giveback_content'], 'content' => ['giveback_content']],
        'parents.html' => ['hero' => ['parents_portal_content'], 'content' => ['parents_portal_content']],
        'people-behind-school.html' => ['hero' => ['people_behind_school_content'], 'content' => ['people_behind_school_content']],
        'resources.html' => ['hero' => ['parents_content'], 'content' => ['parents_content']],
        'contact.html' => ['hero' => ['contact_content'], 'content' => ['contact_content']],
    ];
    return $plans[$page] ?? ['hero' => [], 'content' => []];
}

function readSpecial($db, $table) {
    if ($table === 'gallery_media') {
        try {
            return $db->fetchAll(
                "SELECT media.file_path, media.file_url, media.filename
                 FROM media
                 JOIN media_sections ms ON ms.id = media.section_id
                 WHERE ms.name = 'Gallery'
                 LIMIT 40"
            );
        } catch (Exception $e) {
            return [];
        }
    }
    return readTable($db, $table);
}

function readTable($db, $table) {
    if (!preg_match('/^[a-z_]+$/', $table)) {
        return [];
    }
    try {
        return $db->fetchAll("SELECT * FROM {$table} WHERE is_active = 1 ORDER BY created_at DESC LIMIT 60");
    } catch (Exception $e) {
        try {
            return $db->fetchAll("SELECT * FROM {$table} WHERE is_active = 1 LIMIT 60");
        } catch (Exception $e2) {
            try {
                return $db->fetchAll("SELECT * FROM {$table} LIMIT 60");
            } catch (Exception $e3) {
                return [];
            }
        }
    }
}

function heroUrlsFromRows($rows) {
    $preferred = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        foreach (['hero_image', 'background_image', 'slides'] as $key) {
            if (!empty($row[$key])) {
                collectImageUrls($row[$key], $preferred, $key);
            }
        }
    }
    $preferred = uniqueUrls($preferred);
    if ($preferred) {
        return $preferred;
    }
    $fallback = [];
    collectImageUrls($rows, $fallback);
    return uniqueUrls($fallback);
}

function collectImageUrls($value, &$urls, $key = '') {
    if (is_string($value)) {
        $value = trim($value);
        if ($value === '' || strpos($value, 'data:') === 0) {
            return;
        }
        if (($value[0] ?? '') === '[' || ($value[0] ?? '') === '{') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                collectImageUrls($decoded, $urls, $key);
                return;
            }
        }
        if (isImageAddress($value, $key)) {
            $urls[] = $value;
        }
        return;
    }
    if (is_array($value)) {
        foreach ($value as $name => $item) {
            collectImageUrls($item, $urls, is_string($name) ? $name : $key);
        }
    }
}

function isImageAddress($value, $key) {
    if (preg_match('/\.(jpe?g|png|gif|webp|svg)(\?.*)?$/i', $value)) {
        return true;
    }
    if (stripos($value, 'supabase.co/storage') !== false) {
        return true;
    }
    if ($key && preg_match('/image|logo|photo|slide|thumbnail|file_path|file_url/i', $key)) {
        return (bool) preg_match('#^(https?:)?//|^/#', $value);
    }
    return false;
}

function firstImage($values) {
    $urls = [];
    collectImageUrls($values, $urls);
    return $urls[0] ?? '';
}

function uniqueUrls($urls) {
    $seen = [];
    $out = [];
    foreach ($urls as $url) {
        if (!isset($seen[$url])) {
            $seen[$url] = true;
            $out[] = $url;
        }
    }
    return $out;
}

function removeOnce(&$urls, $url) {
    $index = array_search($url, $urls, true);
    if ($index !== false) {
        array_splice($urls, $index, 1);
    }
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function photoTag($url, $class, $eager, $style = '') {
    $extra = $eager ? ' fetchpriority="high"' : ' loading="lazy"';
    $styleAttr = $style !== '' ? ' style="' . h($style) . '"' : '';
    return '<img class="' . h($class) . '" src="' . h($url) . '" alt=""' . $extra . ' decoding="async"' . $styleAttr . '>';
}

function injectHeader($html, $logo, $schoolName) {
    $mark = $logo !== ''
        ? photoTag($logo, 'logo-image', true)
        : '<div class="logo-placeholder"><span>' . h(substr($schoolName, 0, 1) ?: 'S') . '</span></div>';
    $header = '<header class="site-header" id="mainHeader"><div class="header-main"><div class="container"><div class="header-content">'
        . '<div class="logo"><a href="index.html" class="logo-link">' . $mark . '</a></div>'
        . '<div class="header-actions">'
        . '<button type="button" class="search-button" id="searchButton" aria-label="Search"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg></button>'
        . '<button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu" aria-expanded="false"><span class="menu-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg></span></button>'
        . '</div></div><nav class="main-nav mobile-nav" id="mainNav"></nav></div></div></header>';
    $updated = preg_replace('/<div id="header-placeholder">\s*<\/div>/', '<div id="header-placeholder">' . $header . '</div>', $html, 1, $count);
    return $count ? $updated : $html;
}

function injectHero($html, $url) {
    if ($url === '') {
        return $html;
    }
    if (strpos($html, 'id="heroSection"') !== false) {
        $tag = photoTag($url, 'hero-slide-photo hero-immediate-photo', true);
        return preg_replace('/(<div class="hero-content" id="heroSection"[^>]*>)/', '$1' . $tag, $html, 1);
    }
    $tag = photoTag($url, 'page-hero-photo', true);
    if (strpos($html, 'page-hero-background') !== false) {
        return preg_replace('/(<div class="page-hero-background"[^>]*>)/', '$1' . $tag, $html, 1);
    }
    if (strpos($html, 'admission-hero-background') !== false) {
        return preg_replace('/(<div class="admission-hero-background"[^>]*>)/', '$1' . $tag, $html, 1);
    }
    return $html;
}

function injectPlaceholders($html, &$urls) {
    if (!$urls) {
        return $html;
    }
    return preg_replace_callback('/<div class="(?:image-placeholder|three-column-placeholder)"[^>]*>.*?<\/div>/s', function ($match) use (&$urls) {
        if (!$urls) {
            return $match[0];
        }
        $url = array_shift($urls);
        return photoTag($url, 'embedded-photo', false);
    }, $html);
}

function injectWhyChoose($html, &$urls) {
    if (!$urls || strpos($html, 'class="why-choose-image"') === false) {
        return $html;
    }
    $tag = photoTag($urls[0], 'embedded-photo', false, 'width:100%;height:400px;object-fit:cover;display:block;border-radius:12px;');
    $updated = preg_replace('/(<div class="why-choose-image"[^>]*>)/', '$1' . $tag, $html, 1, $count);
    if ($count) {
        array_shift($urls);
    }
    return $updated;
}

function injectContainers($html, &$urls) {
    if (!$urls) {
        return $html;
    }
    $ids = [
        'gallery-container', 'news-container', 'announcements-container', 'activities-container',
        'clubs-container', 'people-container', 'events-container', 'facilities-container',
        'teachers-container', 'testimonials-container', 'needs-container', 'success-stories-container',
    ];
    $present = [];
    foreach ($ids as $id) {
        if (strpos($html, 'id="' . $id . '"') !== false) {
            $present[] = $id;
        }
    }
    if (!$present) {
        return $html;
    }
    $cards = '';
    $count = 0;
    while ($urls && $count < 24) {
        $url = array_shift($urls);
        $cards .= photoTag($url, 'embedded-photo', $count < 2, 'width:100%;height:220px;object-fit:cover;border-radius:12px;display:block;');
        $count++;
    }
    $grid = '<div class="embedded-photo-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin:24px 0;">' . $cards . '</div>';
    $id = $present[0];
    return preg_replace('/(<div[^>]*id="' . preg_quote($id, '/') . '"[^>]*>)/', '$1' . $grid, $html, 1);
}

function injectPreloads($html, $logo, $heroUrls) {
    $links = '';
    $preload = function ($url) use (&$links) {
        if ($url === '') {
            return;
        }
        $links .= '<link rel="preload" as="image" href="' . h($url) . '" fetchpriority="high">';
    };
    $preload($logo);
    $preload($heroUrls[0] ?? '');
    if (isset($heroUrls[1])) {
        $links .= '<link rel="preload" as="image" href="' . h($heroUrls[1]) . '">';
    }
    if (isset($heroUrls[2])) {
        $links .= '<link rel="preload" as="image" href="' . h($heroUrls[2]) . '">';
    }
    if ($links === '') {
        return $html;
    }
    return preg_replace('/<\/head>/', $links . '</head>', $html, 1);
}
