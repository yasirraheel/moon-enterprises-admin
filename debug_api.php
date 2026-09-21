<?php
// Debug API script to test server connectivity
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Test basic connectivity
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    echo json_encode([
        'success' => true,
        'message' => 'API Debug - Server is responding',
        'timestamp' => date('Y-m-d H:i:s'),
        'server_info' => [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'request_uri' => $_SERVER['REQUEST_URI']
        ],
        'test_endpoints' => [
            'settings' => 'https://moonenterprises.net/api/settings',
            'login' => 'https://moonenterprises.net/api/login',
            'register' => 'https://moonenterprises.net/api/register'
        ]
    ]);
    exit();
}

// Test POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    echo json_encode([
        'success' => true,
        'message' => 'POST request received successfully',
        'received_data' => $input,
        'headers' => getallheaders(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);
?>
