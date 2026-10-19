<?php
/**
 * Saves uploaded images to Supabase Storage when configured.
 * Falls back to the local uploads folder for development.
 */

function supabaseStorageConfigured(): bool {
    return appEnv('SUPABASE_URL') !== '' && appEnv('SUPABASE_SERVICE_ROLE_KEY') !== '';
}

function storeUploadedFile(string $tmpPath, string $filename, string $mime): string {
    if (supabaseStorageConfigured()) {
        $baseUrl = rtrim(appEnv('SUPABASE_URL'), '/');
        $bucket = appEnv('SUPABASE_STORAGE_BUCKET', 'uploads');
        $key = appEnv('SUPABASE_SERVICE_ROLE_KEY');
        $objectPath = rawurlencode($bucket) . '/' . rawurlencode($filename);
        $endpoint = $baseUrl . '/storage/v1/object/' . $objectPath;
        $bytes = file_get_contents($tmpPath);
        if ($bytes === false) {
            throw new Exception('Could not read the uploaded file');
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $bytes,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $key,
                'apikey: ' . $key,
                'Content-Type: ' . ($mime ?: 'application/octet-stream'),
                'x-upsert: true',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $status < 200 || $status >= 300) {
            throw new Exception('Supabase storage upload failed' . ($curlError ? ': ' . $curlError : '') . ($body ? ': ' . $body : ''));
        }

        return $baseUrl . '/storage/v1/object/public/' . $objectPath;
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
