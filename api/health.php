<?php
/**
 * Health Check Endpoint
 */
header('Content-Type: application/json');
echo json_encode([
    'status' => 'OK',
    'message' => 'Excellence Academy API is running',
    'timestamp' => date('c')
]);
