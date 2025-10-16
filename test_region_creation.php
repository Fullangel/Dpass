<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test route existence
echo "Testing route names:\n";
echo "1. admin.regions.index: " . (route('admin.regions.index') ?: 'NOT FOUND') . "\n";
echo "2. admin.regions.create: " . (route('admin.regions.create') ?: 'NOT FOUND') . "\n";
echo "3. admin.regions.store: " . (route('admin.regions.store') ?: 'NOT FOUND') . "\n";
echo "4. regions.index: " . (route('regions.index') ?: 'NOT FOUND') . "\n";

echo "\nRoute list for regions:\n";
$routes = app('router')->getRoutes();
foreach ($routes as $route) {
    if (strpos($route->getName(), 'regions') !== false) {
        echo "Name: " . $route->getName() . " | URI: " . $route->uri() . " | Method: " . implode(',', $route->methods()) . "\n";
    }
}