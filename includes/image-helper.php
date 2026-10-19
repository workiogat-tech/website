<?php
/**
 * Image Helper Functions
 * Normalizes image URLs for proper display in admin panel
 */

/**
 * Normalize image URL for display
 * Handles relative paths, base paths, and absolute URLs
 * Also handles incomplete paths by looking up the full filename from media table
 */
function normalizeImageUrl($imageUrl) {
    if (empty($imageUrl)) {
        return '';
    }
    
    // If it's already an absolute URL (http/https), return as is
    if (substr($imageUrl, 0, 4) === 'http') {
        return $imageUrl;
    }
    
    // Get upload URL constant
    $uploadUrl = defined('UPLOAD_URL') ? UPLOAD_URL : '/uploads/';
    
    // Normalize slashes
    $imageUrl = str_replace('\\', '/', $imageUrl);
    $uploadUrl = rtrim($uploadUrl, '/') . '/';
    
    // Store original URL for comparison
    $originalUrl = $imageUrl;
    
    // Check if path looks incomplete (no file extension)
    // Do this BEFORE checking if it starts with uploadUrl, so we can fix incomplete paths
    $hasExtension = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $imageUrl);
    $basename = basename($imageUrl);
    
    // If path has no extension, try to find the actual file in media table
    if (!$hasExtension && !empty($basename)) {
        try {
            $db = getDB();
            // Try multiple lookup strategies
            // 1. Look for exact match in file_path (with or without extension)
            $media = $db->fetchOne(
                "SELECT file_path, filename FROM media WHERE file_path = ? OR file_path LIKE ? OR file_path LIKE ? ORDER BY id DESC LIMIT 1",
                [$imageUrl, $imageUrl . '.%', '%' . $basename . '%']
            );
            
            // 2. If not found, try matching by filename (handle cases like "6963fcf3633ce_1768")
            if (!$media) {
                // Try matching the basename exactly in filename or file_path
                $media = $db->fetchOne(
                    "SELECT file_path, filename FROM media WHERE filename = ? OR filename LIKE ? OR file_path LIKE ? OR file_path LIKE ? ORDER BY id DESC LIMIT 1",
                    [$basename, $basename . '.%', '%/' . $basename . '%', '%/' . $basename . '.%']
                );
            }
            
            // 3. If still not found and basename contains underscore, try partial match
            if (!$media && strpos($basename, '_') !== false) {
                $parts = explode('_', $basename);
                if (!empty($parts[0])) {
                    $media = $db->fetchOne(
                        "SELECT file_path, filename FROM media WHERE filename LIKE ? OR file_path LIKE ? ORDER BY id DESC LIMIT 1",
                        ['%' . $parts[0] . '%', '%' . $parts[0] . '%']
                    );
                }
            }
            
            if ($media && !empty($media['file_path'])) {
                // Use the full file_path from database
                $imageUrl = $media['file_path'];
                // Re-normalize slashes
                $imageUrl = str_replace('\\', '/', $imageUrl);
                // Check if it now has extension
                $hasExtension = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $imageUrl);
            } elseif ($media && !empty($media['filename'])) {
                // Use filename to construct path
                $imageUrl = $uploadUrl . $media['filename'];
                $hasExtension = true; // Filename should have extension
            }
            
            // 4. If still no extension, try to find file on disk with common extensions
            if (!$hasExtension && defined('BASE_PATH')) {
                $uploadPath = defined('UPLOAD_PATH') ? UPLOAD_PATH : (BASE_PATH . '/uploads/');
                $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                foreach ($extensions as $ext) {
                    // Try with basename as-is
                    $testPath = $uploadPath . $basename . '.' . $ext;
                    if (file_exists($testPath)) {
                        $imageUrl = $uploadUrl . $basename . '.' . $ext;
                        $hasExtension = true;
                        break;
                    }
                    // Also try with just the part before underscore (if it exists)
                    if (strpos($basename, '_') !== false) {
                        $basePart = explode('_', $basename)[0];
                        $testPath = $uploadPath . $basePart . '.' . $ext;
                        if (file_exists($testPath)) {
                            $imageUrl = $uploadUrl . $basePart . '.' . $ext;
                            $hasExtension = true;
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // If database lookup fails, continue with original path
            error_log("Image URL normalization error: " . $e->getMessage());
        }
    }
    
    // If it starts with the correct upload URL, return as is (normalize double slashes)
    if (substr($imageUrl, 0, strlen($uploadUrl)) === $uploadUrl) {
        return preg_replace('#/+#', '/', $imageUrl);
    }
    
    // If it starts with /uploads/ but should have base path (e.g., /2026sjs/uploads/)
    if (substr($imageUrl, 0, 9) === '/uploads/') {
        // Extract filename from path
        $filename = basename($imageUrl);
        // If uploadUrl has base path, use it; otherwise keep /uploads/
        if (substr($uploadUrl, 0, 9) !== '/uploads/') {
            return preg_replace('#/+#', '/', $uploadUrl . $filename);
        }
        // Both are /uploads/, but check if we need to add base path
        if (strpos($uploadUrl, '/2026sjs/') !== false && strpos($imageUrl, '/2026sjs/') === false) {
            return preg_replace('#/+#', '/', $uploadUrl . $filename);
        }
        return preg_replace('#/+#', '/', $imageUrl);
    }
    
    // If it contains /2026sjs/uploads/ or similar base path
    if (preg_match('#/([^/]+)/uploads/(.+)$#', $imageUrl, $matches)) {
        $filename = basename($imageUrl);
        return preg_replace('#/+#', '/', $uploadUrl . $filename);
    }
    
    // If it's a relative path (starts with /)
    if (substr($imageUrl, 0, 1) === '/') {
        // Extract filename from path
        $filename = basename($imageUrl);
        return preg_replace('#/+#', '/', $uploadUrl . $filename);
    }
    
    // If it's a relative path without leading slash
    return preg_replace('#/+#', '/', $uploadUrl . ltrim($imageUrl, '/'));
}

/**
 * Get image preview HTML with proper error handling
 */
function getImagePreview($imageUrl, $options = []) {
    $width = $options['width'] ?? '160px';
    $height = $options['height'] ?? '96px';
    $alt = $options['alt'] ?? 'Image preview';
    $style = $options['style'] ?? '';
    $class = $options['class'] ?? '';
    
    $normalizedUrl = normalizeImageUrl($imageUrl);
    
    if (empty($normalizedUrl)) {
        return '<div style="width: ' . $width . '; height: ' . $height . '; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;">
                    <div style="text-align: center;">
                        <span style="font-size: 24px; color: #999;">🖼️</span>
                        <p style="font-size: 11px; color: #999; margin-top: 5px;">No image</p>
                    </div>
                </div>';
    }
    
    $defaultStyle = 'width: ' . $width . '; height: ' . $height . '; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
    $combinedStyle = $style ? $defaultStyle . ' ' . $style : $defaultStyle;
    
    return '<img src="' . htmlspecialchars($normalizedUrl) . '" 
                 alt="' . htmlspecialchars($alt) . '" 
                 style="' . $combinedStyle . '"
                 class="' . htmlspecialchars($class) . '"
                 onerror="this.onerror=null; this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"
                 onload="this.nextElementSibling.style.display=\'none\';">
            <div style="display: none; width: ' . $width . '; height: ' . $height . '; background: #f0f0f0; border-radius: 8px; border: 2px dashed #ccc; align-items: center; justify-content: center; position: absolute; top: 0; left: 0;">
                <div style="text-align: center;">
                    <span style="font-size: 24px; color: #999;">🖼️</span>
                    <p style="font-size: 11px; color: #999; margin-top: 5px;">Failed to load</p>
                </div>
            </div>';
}
?>
