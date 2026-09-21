<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('admin_settings');
echo "Columns in admin_settings:\n";
print_r($columns);

$settings = \App\Models\AdminSettings::first();
echo "\nValue of app_logo: " . ($settings->app_logo ?? 'NULL') . "\n";
echo "Value of logo: " . ($settings->logo ?? 'NULL') . "\n";
