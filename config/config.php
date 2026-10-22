<?php
/**
 * Application Configuration
 * Excellence Academy School Management System
 */

// Show PHP errors on local pages. Keep them out of API JSON and production.
error_reporting(E_ALL);
$isVercelEnv = getenv('VERCEL') === '1'
    || (isset($_ENV['VERCEL']) && $_ENV['VERCEL'] !== '')
    || (isset($_SERVER['VERCEL']) && $_SERVER['VERCEL'] === '1');
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isApiRequest = strpos($scriptPath, '/api/') !== false;
ini_set('display_errors', (!$isVercelEnv && !$isApiRequest) ? '1' : '0');

// Timezone
date_default_timezone_set('Africa/Kampala');

// Application paths
// For Vercel, use /tmp for uploads (serverless filesystem is read-only except /tmp)
// For local development, use the uploads directory
$isVercel = $isVercelEnv;
if ($isVercel) {
    // Vercel: Use /tmp for temporary storage (note: files are ephemeral)
    // For production, you should use external storage like S3, Vercel Blob, or similar
    define('BASE_PATH', dirname(__DIR__));
    define('UPLOAD_PATH', '/tmp/uploads/');
    // Ensure /tmp/uploads exists
    if (!is_dir('/tmp/uploads')) {
        @mkdir('/tmp/uploads', 0755, true);
    }
} else {
    define('BASE_PATH', dirname(__DIR__));
    define('UPLOAD_PATH', BASE_PATH . '/uploads/');
}

// Auto-detect base URL path (works for subdirectories and Vercel)
$isVercel = $isVercelEnv;
if ($isVercel) {
    // Vercel: Use absolute URL for uploads (since files should be in external storage)
    // For now, use /uploads/ - but in production, you should use a CDN or external storage URL
    $vercelUrl = $_ENV['VERCEL_URL'] ?? (getenv('VERCEL_URL') ?: '');
    if (!empty($vercelUrl)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        define('UPLOAD_URL', $protocol . '://' . $vercelUrl . '/uploads/');
    } else {
        define('UPLOAD_URL', '/uploads/');
    }
} elseif (isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_NAME'])) {
    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $basePath = str_replace('\\', '/', realpath(BASE_PATH));
    
    if ($documentRoot && $basePath) {
        $relativePath = str_replace($documentRoot, '', $basePath);
        $relativePath = str_replace('\\', '/', $relativePath);
        $relativePath = '/' . trim($relativePath, '/') . '/';
        if ($relativePath === '//') {
            $relativePath = '/';
        }
        define('UPLOAD_URL', $relativePath . 'uploads/');
    } else {
        // Fallback: try to detect from SCRIPT_NAME
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $scriptDir = str_replace('\\', '/', $scriptDir);
        if ($scriptDir === '/' || $scriptDir === '.') {
            define('UPLOAD_URL', '/uploads/');
        } else {
            define('UPLOAD_URL', rtrim($scriptDir, '/') . '/uploads/');
        }
    }
} else {
    // Fallback for CLI or when SERVER vars not available
    // Default to /uploads/
    define('UPLOAD_URL', '/uploads/');
}

// Maximum upload size (50MB)
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024);

// Allowed file types for uploads
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Load .env file if it exists
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || 
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
        }
    }
}

foreach ([
    'DATABASE_URL', 'SUPABASE_DB_URL', 'SUPABASE_URL', 'SUPABASE_SERVICE_ROLE_KEY',
    'SUPABASE_STORAGE_BUCKET', 'ADMIN_EMAIL', 'ADMIN_PASSWORD', 'ADMIN_USERNAME',
    'SMTP_HOST', 'SMTP_PORT', 'SMTP_SECURE', 'SMTP_USER', 'SMTP_PASS',
    'SMTP_FROM_EMAIL', 'SMTP_FROM_NAME', 'ADMIN_EMAILS', 'JWT_SECRET'
] as $envKey) {
    if ((!isset($_ENV[$envKey]) || $_ENV[$envKey] === '') && getenv($envKey) !== false && getenv($envKey) !== '') {
        $_ENV[$envKey] = getenv($envKey);
    }
}

// SMTP Configuration (for cPanel email)
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'localhost'); // Usually localhost for cPanel
define('SMTP_PORT', intval($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_SECURE', filter_var($_ENV['SMTP_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN)); // true for SSL (port 465), false for TLS (port 587)
define('SMTP_USER', $_ENV['SMTP_USER'] ?? ''); // Your cPanel email address
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? ''); // Your cPanel email password
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? ''); // From email address
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? 'Excellence Academy');

// Admin emails (comma-separated list from .env)
define('ADMIN_EMAILS', $_ENV['ADMIN_EMAILS'] ?? '');

// JWT Secret for API authentication (if needed)
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this-in-production');

// CORS and JSON headers - ONLY set for API endpoints
// Regular HTML pages should NOT have these headers
// API files should set these headers themselves
// DO NOT set Content-Type here - let each file set its own

// Include database connection
require_once __DIR__ . '/database.php';
require_once dirname(__DIR__) . '/includes/pg-session.php';

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || $isVercelEnv;
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_secure', $https ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_lifetime', '604800');
session_set_cookie_params([
    'lifetime' => 604800,
    'path' => '/',
    'secure' => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $requestPath = strtolower((string) ($_GET['__path'] ?? ($_SERVER['REQUEST_URI'] ?? '')));
    $isPublicRead = ($requestMethod === 'GET' || $requestMethod === 'HEAD')
        && (strpos($requestPath, 'public/') !== false || strpos($requestPath, 'health') !== false);
    // Visitors reading the public site do not need a database session.
    if (!$isPublicRead || !empty($_COOKIE['PHPSESSID'])) {
        session_set_save_handler(new PgSessionHandler(), true);
        session_start();
    }
}
