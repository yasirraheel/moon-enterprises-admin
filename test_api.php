<?php
// Simple API test script
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Test the settings endpoint
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['test'])) {
    echo json_encode([
        'success' => true,
        'message' => 'API is working',
        'data' => [
            'app_name' => 'GEO ENTERPRISES',
            'status' => 'online'
        ]
    ]);
    exit();
}

// Test login endpoint
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_login'])) {
    echo json_encode([
        'success' => true,
        'message' => 'Login endpoint is accessible',
        'data' => [
            'user' => [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com'
            ],
            'token' => 'test_token_123'
        ]
    ]);
    exit();
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
?>
