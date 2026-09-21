<?php

use App\Models\User;
use App\Models\Orders;
use App\Models\Categories;
use App\Models\GamePrompts;

// 1. Find an approved dealer
$dealer = User::where('dealer_status', 'approved')->first();

if (!$dealer) {
    echo "No approved dealer found.\n";
    exit;
}

echo "Found Dealer: {$dealer->username} (ID: {$dealer->id})\n";

// 2. Find orders for this dealer
$orders = Orders::where('user_id', $dealer->id)->get();

echo "Found {$orders->count()} orders for this dealer.\n";

if ($orders->isEmpty()) {
    echo "No orders for this dealer. Checking any user with dealer_status='approved' having orders...\n";
    $dealerWithOrders = User::where('dealer_status', 'approved')->whereHas('orders')->first();
    if ($dealerWithOrders) {
        $dealer = $dealerWithOrders;
        echo "Switched to Dealer: {$dealer->username} (ID: {$dealer->id})\n";
        $orders = Orders::where('user_id', $dealer->id)->get();
    } else {
        echo "No dealers with orders found.\n";
        exit;
    }
}

// 3. Analyze a few orders
foreach ($orders->take(5) as $order) {
    echo "Order ID: {$order->id}, Game: '{$order->game_name}', RTTP: '{$order->rttp}', Exported: " . ($order->is_exported ? 'Yes' : 'No') . "\n";
    
    // Check if this matches any category
    $category = Categories::where('name', $order->game_name)->first();
    if ($category) {
        echo "  Matches Category: {$category->name} (ID: {$category->id})\n";
        
        // Check if it matches any prompt for this category
        // Logic from Controller: Parse prompt number start/end
        // We need to simulate the controller logic roughly or just check if any prompt covers this RTTP
        
        // Let's just grab all prompts and see if any cover this RTTP
        $prompts = GamePrompts::all();
        $matchedPrompt = false;
        
        foreach ($prompts as $prompt) {
             // Basic check - simplistic parsing for debug
             // Assuming simple numbers for now or using the controller logic if I could import it easily.
             // Let's just print the prompt ranges to see visually
             // echo "  - Checking Prompt: {$prompt->prompt} ({$prompt->number_start} - {$prompt->number_end})\n";
             
             // A quick regex check if possible, or just skip complexity for now.
             // If the order has RTTP '123' and prompt is 100-200, it should match.
        }
    } else {
        echo "  NO Category match for game_name: '{$order->game_name}'\n";
    }
}

// 4. Check specific case if provided (none provided, so general check above)
