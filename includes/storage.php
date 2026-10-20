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

function supabaseHttp(string $method, string $path, $body = null, array $headers = []): array {
    $key = appEnv('SUPABASE_SERVICE_ROLE_KEY');
    $url = rtrim(appEnv('SUPABASE_URL'), '/') . $path;
    $headerLines = array_merge([
        'Authorization: Bearer ' . $key,
        'apikey: ' . $key,
    ], $headers);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
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
            'timeout' => 25,
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
        'ALTER TABLE hero_content ALTER COLUMN background_image TYPE TEXT',
        'ALTER TABLE hero_content ALTER COLUMN slides TYPE TEXT',
    ];
    foreach ($statements as $sql) {
        try {
            $db->query($sql);
        } catch (Exception $e) {
            error_log('Column widen skipped: ' . $e->getMessage());
        }
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
