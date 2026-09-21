<?php
// Test script to debug authentication
require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\PaidServiceSale;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== AUTHENTICATION DEBUG TEST ===\n\n";

// 1. Check if user exists and has purchases
echo "1. User Check (ID 1):\n";
$user = User::find(1);
if ($user) {
    echo "   User found: {$user->username} (ID: {$user->id})\n";
    echo "   Balance: {$user->balance}\n";
    
    // Check purchases
    $purchases = PaidServiceSale::where('user_id', $user->id)->get();
    echo "   Total purchases: " . $purchases->count() . "\n";
    foreach ($purchases as $purchase) {
        echo "   - Service ID: {$purchase->service_id}, Amount: {$purchase->amount}, Status: {$purchase->status}\n";
    }
} else {
    echo "   User not found!\n";
}

echo "\n2. Test Authentication Token:\n";
// Test with a sample token (you'll need to get a real token)
echo "   To test with real token, use:\n";
echo "   curl -H 'Authorization: Bearer YOUR_TOKEN' http://yoursite.com/api/paid-services/authenticated\n";

echo "\n3. Check Sanctum Configuration:\n";
$sanctumConfig = config('sanctum');
echo "   Stateful domains: " . implode(', ', $sanctumConfig['stateful'] ?? []) . "\n";
echo "   Expiration: " . $sanctumConfig['expiration'] . " minutes\n";

echo "\n=== END DEBUG ===\n";
