<?php
// Test Laravel routes
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get all routes
$routes = app('router')->getRoutes();

echo "<h2>API Routes Test</h2>";
echo "<pre>";

$apiRoutes = [];
foreach ($routes as $route) {
    if (strpos($route->uri(), 'api/') === 0) {
        $apiRoutes[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName()
        ];
    }
}

echo "Found " . count($apiRoutes) . " API routes:\n\n";

foreach ($apiRoutes as $route) {
    echo "Method: " . $route['method'] . "\n";
    echo "URI: " . $route['uri'] . "\n";
    echo "Name: " . ($route['name'] ?? 'N/A') . "\n";
    echo "Action: " . $route['action'] . "\n";
    echo "---\n";
}

echo "</pre>";

// Test specific endpoints
echo "<h3>Testing API Endpoints:</h3>";

// Test settings endpoint
echo "<h4>Testing /api/settings:</h4>";
try {
    $request = Request::create('/api/settings', 'GET');
    $response = app()->handle($request);
    echo "Status: " . $response->getStatusCode() . "<br>";
    echo "Response: " . $response->getContent() . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h4>Testing /api/login (POST):</h4>";
try {
    $request = Request::create('/api/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);
    $response = app()->handle($request);
    echo "Status: " . $response->getStatusCode() . "<br>";
    echo "Response: " . $response->getContent() . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
