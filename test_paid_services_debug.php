<?php
// Test script to debug paid services purchase status
require_once 'vendor/autoload.php';

use App\Models\PaidService;
use App\Models\PaidServiceSale;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PAID SERVICES DEBUG TEST ===\n\n";

// 1. Check all paid services
echo "1. All Paid Services:\n";
$services = PaidService::all();
foreach ($services as $service) {
    echo "   ID: {$service->id}, Title: {$service->title}, Price: {$service->price}, Active: " . ($service->is_active ? 'Yes' : 'No') . "\n";
}

echo "\n2. All Paid Service Sales:\n";
$sales = PaidServiceSale::with(['user', 'service'])->get();
foreach ($sales as $sale) {
    echo "   Sale ID: {$sale->id}, User: {$sale->user->username} (ID: {$sale->user_id}), Service: {$sale->service->title} (ID: {$sale->service_id}), Amount: {$sale->amount}, Status: {$sale->status}\n";
}

echo "\n3. Test User Purchases (User ID 1):\n";
$userPurchases = PaidServiceSale::where('user_id', 1)
    ->where('status', 'active')
    ->pluck('service_id')
    ->toArray();
echo "   Purchased Service IDs: " . implode(', ', $userPurchases) . "\n";

echo "\n4. Test Authentication Check:\n";
// Simulate what the API does
$services = PaidService::active()->ordered()->get();
$userPurchases = PaidServiceSale::where('user_id', 1)
    ->where('status', 'active')
    ->pluck('service_id')
    ->toArray();

foreach ($services as $service) {
    $hasPurchased = in_array($service->id, $userPurchases);
    echo "   Service {$service->id} ({$service->title}): Has Purchased = " . ($hasPurchased ? 'YES' : 'NO') . "\n";
}

echo "\n=== END DEBUG ===\n";
