<?php
/**
 * Page Router for Root-Level PHP Files
 * Routes requests to root-level PHP pages (index.php, about.php, etc.)
 */

// Get the request path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

// Remove query string
$path = strtok($path, '?');

// Map common routes to PHP files
$routeMap = [
    '' => 'index.php',
    'index' => 'index.php',
    'about' => 'about.php',
    'login' => 'login.php',
    'logout' => 'logout.php',
    'admin' => 'admin.php',
    'contact' => 'contact.php',
    'admission' => 'admission.php',
    'announcements' => 'announcements.php',
    'curriculum' => 'curriculum.php',
    'gallery' => 'gallery.php',
    'news' => 'news.php',
    'testimonials' => 'testimonials.php',
    'facilities' => 'facilities.php',
    'activities' => 'activities.php',
    'teachers' => 'teachers.php',
    'clubs' => 'clubs.php',
    'parents' => 'parents.php',
    'sponsorship' => 'sponsorship.php',
    'give-back' => 'give-back.php',
    'resources' => 'resources.php',
    'people-behind-school' => 'people-behind-school.php',
    'events' => 'events.php',
];

// Determine which file to serve
$fileToServe = null;

// Check if path matches a route
if (isset($routeMap[$path])) {
    $fileToServe = $routeMap[$path];
} elseif (empty($path)) {
    // Root path
    $fileToServe = 'index.php';
} elseif (preg_match('/^([a-z0-9-]+)$/i', $path)) {
    // Try to match path to a PHP file directly
    $possibleFile = __DIR__ . '/../' . $path . '.php';
    if (file_exists($possibleFile)) {
        $fileToServe = $path . '.php';
    }
}

// If no file found, default to index.php
if (!$fileToServe) {
    $fileToServe = 'index.php';
}

// Get the full path to the file
$filePath = __DIR__ . '/../' . $fileToServe;

// Check if file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    echo "Page not found";
    exit;
}

// Include the PHP file
require $filePath;
