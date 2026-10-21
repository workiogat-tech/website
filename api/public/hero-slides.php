<?php
/**
 * Small public payload for the homepage slider.
 * The full homepage request is much slower, so the pictures start from here.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=120');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';

function publicHeroSlides($value): array {
    if (is_array($value)) {
        $list = $value;
    } elseif (is_string($value) && trim($value) !== '') {
        $decoded = json_decode($value, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        $list = is_array($decoded) ? $decoded : [$value];
    } else {
        return [];
    }

    $slides = [];
    foreach ($list as $slide) {
        $url = '';
        if (is_string($slide)) {
            $url = trim($slide);
        } elseif (is_array($slide)) {
            foreach (['url', 'image', 'src', 'file_path', 'file_url'] as $key) {
                if (!empty($slide[$key]) && is_string($slide[$key])) {
                    $url = trim($slide[$key]);
                    break;
                }
            }
        }
        if ($url === '' || stripos($url, 'data:') === 0 || in_array($url, $slides, true)) {
            continue;
        }
        $slides[] = $url;
    }
    return $slides;
}

try {
    $db = getDB();
    $row = $db->fetchOne(
        "SELECT headline, subheadline, description, background_image, slides,
                slide_interval_ms, slide_direction, slide_mode, background_size,
                background_position, image_scale, headline_color, subheadline_color
         FROM hero_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
    );
    if (!$row) {
        echo json_encode(['slides' => []]);
        exit;
    }
    $row['slides'] = publicHeroSlides($row['slides'] ?? null);
    echo json_encode($row);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Hero images could not be loaded']);
}
