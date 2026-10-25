<?php
/**
 * Saves uploaded images to Supabase Storage when configured.
 * Falls back to the local uploads folder for development.
 */

function supabaseStorageConfigured(): bool {
    return appEnv('SUPABASE_URL') !== '' && appEnv('SUPABASE_SERVICE_ROLE_KEY') !== '';
}

function supabasePublicUrl(string $bucket, string $objectPath): string {
    $encoded = rawurlencode($bucket);
    foreach (explode('/', $objectPath) as $segment) {
        if ($segment !== '') {
            $encoded .= '/' . rawurlencode($segment);
        }
    }
    return rtrim(appEnv('SUPABASE_URL'), '/') . '/storage/v1/object/public/' . $encoded;
}

function supabaseHttp(string $method, string $path, $body = null, array $headers = [], int $timeout = 25): array {
    $key = appEnv('SUPABASE_SERVICE_ROLE_KEY');
    $url = rtrim(appEnv('SUPABASE_URL'), '/') . $path;
    $headerLines = array_merge([
        'Authorization: Bearer ' . $key,
        'apikey: ' . $key,
    ], $headers);
    $timeout = max(1, $timeout);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => min(4, $timeout),
            CURLOPT_TIMEOUT => $timeout,
        ];
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            throw new Exception('Supabase request failed: ' . $error);
        }
        return [$status, $response];
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headerLines),
            'content' => $body ?? '',
            'ignore_errors' => true,
            'timeout' => $timeout,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    $status = 0;
    foreach ($http_response_header ?? [] as $line) {
        if (preg_match('/^HTTP\/\S+\s+(\d+)/', $line, $matches)) {
            $status = (int) $matches[1];
        }
    }
    if ($response === false) {
        throw new Exception('Supabase request failed');
    }
    return [$status, $response];
}

function listSupabaseImagesPage(): array {
    if (!supabaseStorageConfigured()) {
        return [];
    }

    $bucket = appEnv('SUPABASE_STORAGE_BUCKET', 'uploads');
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    try {
        [$status, $body] = supabaseHttp(
            'POST',
            '/storage/v1/object/list/' . rawurlencode($bucket),
            json_encode([
                'prefix' => '',
                'limit' => 80,
                'offset' => 0,
                'sortBy' => ['column' => 'name', 'order' => 'desc'],
            ]),
            ['Content-Type: application/json'],
            4
        );
    } catch (Exception $e) {
        error_log('Storage list skipped: ' . $e->getMessage());
        return [];
    }

    $page = json_decode($body, true);
    if ($status < 200 || $status >= 300 || !is_array($page)) {
        return [];
    }

    $found = [];
    foreach ($page as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = (string) ($item['name'] ?? '');
        if ($name === '' || $name === '.emptyFolderPlaceholder' || strpos($name, '/') !== false) {
            continue;
        }
        $isFolder = !isset($item['id']) || $item['id'] === null || $item['id'] === '';
        if ($isFolder) {
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            continue;
        }
        $found[] = [
            'name' => $name,
            'url' => supabasePublicUrl($bucket, $name),
            'size' => (int) ($item['metadata']['size'] ?? 0),
            'mime' => $item['metadata']['mimetype'] ?? 'image/jpeg',
        ];
    }

    return $found;
}

function listSupabaseImages(): array {
    if (!supabaseStorageConfigured()) {
        return [];
    }

    $bucket = appEnv('SUPABASE_STORAGE_BUCKET', 'uploads');
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $found = [];
    $prefixes = [''];
    $seen = [];

    while ($prefixes && count($seen) < 40 && count($found) < 1000) {
        $prefix = array_shift($prefixes);
        if (isset($seen[$prefix])) {
            continue;
        }
        $seen[$prefix] = true;
        $offset = 0;

        do {
            [$status, $body] = supabaseHttp(
                'POST',
                '/storage/v1/object/list/' . rawurlencode($bucket),
                json_encode([
                    'prefix' => $prefix,
                    'limit' => 100,
                    'offset' => $offset,
                    'sortBy' => ['column' => 'name', 'order' => 'asc'],
                ]),
                ['Content-Type: application/json']
            );
            $page = json_decode($body, true);
            if ($status < 200 || $status >= 300 || !is_array($page)) {
                break;
            }

            foreach ($page as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $name = (string) ($item['name'] ?? '');
                if ($name === '' || $name === '.emptyFolderPlaceholder') {
                    continue;
                }
                $path = $prefix === '' ? $name : rtrim($prefix, '/') . '/' . $name;
                $isFolder = !isset($item['id']) || $item['id'] === null || $item['id'] === '';
                if ($isFolder) {
                    $prefixes[] = rtrim($path, '/') . '/';
                    continue;
                }
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) {
                    continue;
                }
                $found[] = [
                    'name' => $path,
                    'url' => supabasePublicUrl($bucket, $path),
                    'size' => (int) ($item['metadata']['size'] ?? 0),
                    'mime' => $item['metadata']['mimetype'] ?? 'image/jpeg',
                ];
            }
            $offset += 100;
        } while (count($page) === 100 && $offset < 1000);
    }

    return $found;
}

function ensureImageColumns($db): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $statements = [
        'ALTER TABLE media ALTER COLUMN file_path TYPE TEXT',
        'ALTER TABLE media ALTER COLUMN filename TYPE TEXT',
        'ALTER TABLE media ALTER COLUMN original_name TYPE TEXT',
        'ALTER TABLE hero_content ALTER COLUMN background_image TYPE TEXT USING background_image::text',
        'ALTER TABLE hero_content ALTER COLUMN slides TYPE TEXT USING slides::text',
    ];
    foreach ($statements as $sql) {
        try {
            $db->query($sql);
        } catch (Exception $e) {
            error_log('Column widen skipped: ' . $e->getMessage());
        }
    }
}

function savedWebsiteImageUrls($db): array {
    $urls = [];
    $add = function ($url) use (&$urls) {
        $url = trim((string) $url);
        if ($url === '' || stripos($url, 'data:') === 0) {
            return;
        }
        if (!preg_match('#^(https?:)?//#i', $url) && strpos($url, '/') !== 0) {
            return;
        }
        if (!in_array($url, $urls, true)) {
            $urls[] = $url;
        }
    };

    try {
        $rows = $db->fetchAll("SELECT background_image, slides FROM hero_content");
    } catch (Exception $e) {
        return [];
    }

    foreach ($rows as $row) {
        $add($row['background_image'] ?? '');
        $raw = $row['slides'] ?? '';
        if (is_array($raw)) {
            $list = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            $list = is_array($decoded) ? $decoded : [$raw];
        } else {
            $list = [];
        }
        foreach ($list as $slide) {
            if (is_string($slide)) {
                $add($slide);
            } elseif (is_array($slide)) {
                foreach (['url', 'image', 'src', 'file_path', 'file_url'] as $key) {
                    if (!empty($slide[$key]) && is_string($slide[$key])) {
                        $add($slide[$key]);
                        break;
                    }
                }
            }
        }
    }

    return $urls;
}

function syncSavedImagesIntoMedia($db): void {
    ensureImageColumns($db);
    try {
        $rows = $db->fetchAll("SELECT filename, file_path FROM media");
    } catch (Exception $e) {
        return;
    }

    $existing = [];
    foreach ($rows as $row) {
        foreach ([$row['file_path'] ?? '', $row['filename'] ?? ''] as $value) {
            $value = (string) $value;
            if ($value === '') {
                continue;
            }
            $existing[$value] = true;
            $existing[basename(parse_url($value, PHP_URL_PATH) ?: $value)] = true;
        }
    }

    $mimeByExt = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    foreach (savedWebsiteImageUrls($db) as $url) {
        $base = basename(parse_url($url, PHP_URL_PATH) ?: $url);
        if ($base === '' || $base === '/' || isset($existing[$url]) || isset($existing[$base])) {
            continue;
        }
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
        try {
            $db->insert(
                "INSERT INTO media (section_id, filename, original_name, file_path, file_size, file_type, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [null, $base, $base, $url, 0, $mimeByExt[$ext] ?? 'image/jpeg']
            );
            $existing[$url] = true;
            $existing[$base] = true;
        } catch (Exception $e) {
            error_log('Could not record saved image ' . $base . ': ' . $e->getMessage());
        }
    }
}

function imageFileName(string $url): string {
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    $path = parse_url($url, PHP_URL_PATH);
    $name = rawurldecode(basename(str_replace('\\', '/', $path ?: $url)));
    if ($name === '' || $name === '.' || $name === '..') {
        return '';
    }
    return strtolower($name);
}

function sameImage(string $left, string $right): bool {
    $left = trim($left);
    $right = trim($right);
    if ($left === '' || $right === '') {
        return false;
    }
    if ($left === $right) {
        return true;
    }
    $leftName = imageFileName($left);
    $rightName = imageFileName($right);
    return $leftName !== '' && $leftName === $rightName;
}

function deleteStoredFile(string $fileUrl): void {
    $fileUrl = trim($fileUrl);
    if ($fileUrl === '') {
        return;
    }

    $path = str_replace('\\', '/', parse_url($fileUrl, PHP_URL_PATH) ?: $fileUrl);
    if (supabaseStorageConfigured() && preg_match('#/storage/v1/object/public/([^/]+)/(.+)$#', $path, $matches)) {
        $bucket = rawurldecode($matches[1]);
        $object = rawurldecode($matches[2]);
        $encoded = rawurlencode($bucket);
        foreach (explode('/', $object) as $segment) {
            if ($segment !== '') {
                $encoded .= '/' . rawurlencode($segment);
            }
        }
        [$status] = supabaseHttp('DELETE', '/storage/v1/object/' . $encoded);
        if ($status >= 300 && $status !== 404) {
            throw new Exception('The picture could not be removed from storage.');
        }
    } elseif (supabaseStorageConfigured()) {
        $name = imageFileName($fileUrl);
        if ($name !== '') {
            $bucket = appEnv('SUPABASE_STORAGE_BUCKET', 'uploads');
            [$status] = supabaseHttp(
                'DELETE',
                '/storage/v1/object/' . rawurlencode($bucket) . '/' . rawurlencode($name)
            );
            if ($status >= 300 && $status !== 404) {
                throw new Exception('The picture could not be removed from storage.');
            }
        }
    }

    $name = imageFileName($fileUrl);
    if ($name === '') {
        return;
    }
    $uploadDir = defined('UPLOAD_PATH') ? UPLOAD_PATH : (dirname(__DIR__) . '/uploads/');
    $local = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $name;
    if (is_file($local)) {
        @unlink($local);
    }
}

function removeSavedImage($db, string $url): void {
    $url = trim($url);
    if ($url === '') {
        return;
    }

    try {
        $rows = $db->fetchAll("SELECT id, background_image, slides FROM hero_content");
    } catch (Exception $e) {
        return;
    }

    foreach ($rows as $row) {
        $background = (string) ($row['background_image'] ?? '');
        $nextBackground = sameImage($background, $url) ? '' : $background;
        $raw = $row['slides'] ?? '';
        if (is_array($raw)) {
            $list = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            $list = is_array($decoded) ? $decoded : [$raw];
        } else {
            $list = [];
        }

        $kept = [];
        foreach ($list as $slide) {
            $slideUrl = '';
            if (is_string($slide)) {
                $slideUrl = $slide;
            } elseif (is_array($slide)) {
                foreach (['url', 'image', 'src', 'file_path', 'file_url'] as $key) {
                    if (!empty($slide[$key]) && is_string($slide[$key])) {
                        $slideUrl = $slide[$key];
                        break;
                    }
                }
            }
            if ($slideUrl === '' || sameImage($slideUrl, $url)) {
                continue;
            }
            $kept[] = $slideUrl;
        }

        $previous = [];
        foreach ($list as $slide) {
            if (is_string($slide)) {
                $previous[] = $slide;
            }
        }
        if ($nextBackground === $background && $kept === array_values($previous) && count($kept) === count($list)) {
            continue;
        }

        $db->update(
            "UPDATE hero_content SET slides = ?, background_image = ? WHERE id = ?",
            [json_encode(array_values($kept)), $nextBackground, $row['id']]
        );
    }
}

function storeUploadedFile(string $tmpPath, string $filename, string $mime): string {
    if (supabaseStorageConfigured()) {
        $bucket = appEnv('SUPABASE_STORAGE_BUCKET', 'uploads');
        $bytes = file_get_contents($tmpPath);
        if ($bytes === false) {
            throw new Exception('Could not read the uploaded file');
        }

        $encoded = rawurlencode($bucket);
        foreach (explode('/', $filename) as $segment) {
            if ($segment !== '') {
                $encoded .= '/' . rawurlencode($segment);
            }
        }
        [$status, $body] = supabaseHttp(
            'POST',
            '/storage/v1/object/' . $encoded,
            $bytes,
            [
                'Content-Type: ' . ($mime ?: 'application/octet-stream'),
                'x-upsert: true',
            ]
        );

        if ($status < 200 || $status >= 300) {
            throw new Exception('Supabase storage upload failed: ' . $body);
        }

        return supabasePublicUrl($bucket, $filename);
    }

    $uploadDir = defined('UPLOAD_PATH') ? UPLOAD_PATH : (dirname(__DIR__) . '/uploads/');
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new Exception('Failed to create the uploads folder');
    }
    if (!is_writable($uploadDir)) {
        throw new Exception('Uploads folder is not writable');
    }

    $destination = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmpPath, $destination)) {
        throw new Exception('Failed to save the uploaded file');
    }

    return (defined('UPLOAD_URL') ? UPLOAD_URL : '/uploads/') . $filename;
}
