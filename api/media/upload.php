<?php
/**
 * Media Upload API - Supports single and multiple file uploads
 */
// Start output buffering to catch any unexpected output
ob_start();

// Suppress error display for API endpoints (errors will be logged but not displayed)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/storage.php';

// Clear any output that may have been generated
ob_clean();

// Set JSON header
header('Content-Type: application/json');

$auth = getAuth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded. Please select a file to upload.']);
    exit;
}

// Check if file was actually uploaded
if (is_array($_FILES['file']['name'])) {
    // Multiple files - check if any files were selected
    $hasFiles = false;
    foreach ($_FILES['file']['name'] as $name) {
        if (!empty($name)) {
            $hasFiles = true;
            break;
        }
    }
    if (!$hasFiles) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No files selected. Please select at least one file to upload.']);
        exit;
    }
} else {
    // Single file - check if file was selected
    if (empty($_FILES['file']['name']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No file selected. Please select a file to upload.']);
        exit;
    }
}

$sectionId = $_POST['section_id'] ?? null;
if (!$sectionId) {
    http_response_code(400);
    echo json_encode(['error' => 'Section ID required']);
    exit;
}

// Handle single file (when name is 'file') or array (when name is 'file[]')
$files = [];
if (is_array($_FILES['file']['name'])) {
    // Multiple files
    $fileCount = count($_FILES['file']['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        $files[] = [
            'name' => $_FILES['file']['name'][$i],
            'type' => $_FILES['file']['type'][$i],
            'tmp_name' => $_FILES['file']['tmp_name'][$i],
            'error' => $_FILES['file']['error'][$i],
            'size' => $_FILES['file']['size'][$i]
        ];
    }
} else {
    // Single file
    $files[] = $_FILES['file'];
}

$maxSize = 50 * 1024 * 1024; // 50MB
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$uploadedFiles = [];
$errors = [];

foreach ($files as $file) {
    // Check upload error code
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error (' . $file['error'] . ')';
        $errors[] = $file['name'] . ': ' . $errorMsg;
        error_log("Upload error for {$file['name']}: {$errorMsg} (code: {$file['error']})");
        continue;
    }
    
    // Check if file exists
    if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
        $errors[] = $file['name'] . ': Temporary file not found';
        error_log("Temporary file missing for {$file['name']}");
        continue;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $errors[] = $file['name'] . ': File too large (' . round($file['size'] / 1024 / 1024, 2) . 'MB). Maximum size is 50MB';
        continue;
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        $errors[] = $file['name'] . ': Invalid file type (' . $file['type'] . '). Only images (JPEG, PNG, GIF, WEBP) are allowed';
        continue;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (empty($extension)) {
        $errors[] = $file['name'] . ': File has no extension';
        continue;
    }
    $filename = uniqid() . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;

    try {
        $fileUrl = storeUploadedFile($file['tmp_name'], $filename, $file['type']);
    } catch (Exception $e) {
        $errors[] = $file['name'] . ': ' . $e->getMessage();
        error_log('Upload storage error for ' . $file['name'] . ': ' . $e->getMessage());
        continue;
    }
    
    // Insert into database
    try {
        // Browser sections use ids that are not database rows. Keep the file anyway.
        $dbSectionId = null;
        $sectionNumber = is_numeric($sectionId) ? (int) $sectionId : 0;
        if ($sectionNumber > 0 && $sectionNumber < 2147483647) {
            $sectionCheck = $db->fetchOne("SELECT id FROM media_sections WHERE id = ? AND is_active = 1", [$sectionNumber]);
            if ($sectionCheck) {
                $dbSectionId = $sectionCheck['id'];
            }
        }
        
        $mediaId = $db->insert(
            "INSERT INTO media (section_id, filename, original_name, file_path, file_size, file_type, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [
                $dbSectionId,
                $filename,
                $file['name'],
                $fileUrl,
                $file['size'],
                $file['type']
            ]
        );
        
        if (!$mediaId) {
            throw new Exception('Failed to get insert ID');
        }
        
        $uploadedFiles[] = [
            'media_id' => $mediaId,
            'file_path' => $fileUrl,
            'file_url' => $fileUrl, // For backward compatibility
            'filename' => $file['name']
        ];
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        // Provide more helpful error messages
        if (strpos($errorMsg, 'Database query failed') !== false) {
            $errorMsg = 'Database error. Please check database connection and table structure.';
        }
        $errors[] = $file['name'] . ': ' . $errorMsg;
        error_log("Database error for {$file['name']}: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    }
}

if (count($uploadedFiles) > 0) {
    echo json_encode([
        'success' => true,
        'uploaded' => $uploadedFiles,
        'errors' => $errors,
        'message' => count($uploadedFiles) . ' file(s) uploaded successfully' . (count($errors) > 0 ? ', ' . count($errors) . ' failed' : '')
    ]);
} else {
    http_response_code(400);
    $errorMessage = 'No files uploaded';
    if (count($errors) > 0) {
        $errorMessage = 'Upload failed: ' . implode('; ', $errors);
    }
    echo json_encode([
        'success' => false,
        'error' => $errorMessage,
        'errors' => $errors
    ]);
}

// Clean output buffer and send response
ob_end_flush();
?>
