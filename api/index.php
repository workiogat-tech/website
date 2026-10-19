<?php
/**
 * API Router
 * Routes API requests to appropriate handlers
 */

// Start output buffering to catch any unexpected output
ob_start();

// Suppress error display for API endpoints (errors will be logged but not displayed)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set API headers (CORS and JSON)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    http_response_code(200);
    ob_end_flush();
    exit();
}

require_once __DIR__ . '/../config/config.php';

// Clear any output that may have been generated
ob_clean();

// Get request method and path.
// Every /api/* URL is rewritten to this file. __path is the original path
// after /api, so handlers keep working as one function.
$method = $_SERVER['REQUEST_METHOD'];
$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (isset($_GET['__path']) && $_GET['__path'] !== '') {
    $rawPath = '/api/' . ltrim((string) $_GET['__path'], '/');
    unset($_GET['__path'], $_REQUEST['__path']);
    if (isset($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $queryParams);
        unset($queryParams['__path']);
        $_SERVER['QUERY_STRING'] = http_build_query($queryParams);
    }
}
$path = preg_replace('#^/api#', '', $rawPath, 1);
$pathParts = array_filter(explode('/', $path));

// Route the request
$route = '/' . implode('/', $pathParts);
if (empty($route) || $route === '/') {
    $route = '/health';
}

$relative = ltrim($route, '/');
$relative = preg_replace('/\.php$/', '', $relative);
$candidate = __DIR__ . '/' . $relative . '.php';
$apiRoot = realpath(__DIR__);
$realCandidate = realpath($candidate);
if (
    $realCandidate &&
    $apiRoot &&
    strpos($realCandidate, $apiRoot) === 0 &&
    is_file($realCandidate) &&
    basename($realCandidate) !== 'index.php'
) {
    require $realCandidate;
    ob_end_flush();
    exit;
}

// Include route handlers
switch ($route) {
    case '/health':
        require __DIR__ . '/health.php';
        break;
    case '/auth/login':
        if ($method === 'POST') {
            require __DIR__ . '/auth/login.php';
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/auth/logout':
        if ($method === 'POST') {
            require __DIR__ . '/auth/logout.php';
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/auth/verify':
        if ($method === 'GET') {
            require __DIR__ . '/auth/verify.php';
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/forms/contact':
        if ($method === 'POST') {
            require __DIR__ . '/forms/contact.php';
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/forms/sponsorship-application':
        if ($method === 'POST') {
            require __DIR__ . '/forms/sponsorship-application.php';
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/forms/child-sponsorship':
        if ($method === 'POST') {
            require __DIR__ . '/forms/child-sponsorship.php';
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/forms/newsletter-subscription':
        if ($method === 'POST') {
            require __DIR__ . '/forms/newsletter-subscription.php';
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/forms/parent-registration':
        if ($method === 'POST') {
            require __DIR__ . '/forms/parent-registration.php';
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    default:
        // Try to match content routes (e.g., /hero-content, /about-content, etc.)
        if (preg_match('/^\/([a-z-]+)$/', $route, $matches)) {
            $contentType = $matches[1];
            $handlerFile = __DIR__ . '/content/' . $contentType . '.php';
            if (file_exists($handlerFile)) {
                require $handlerFile;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Route not found']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Route not found']);
        }
        break;
}

// Clean output buffer and send response
ob_end_flush();
