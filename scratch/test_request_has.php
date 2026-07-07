<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

// 1. Simulate a request with modules array
$request = Request::create('/test', 'PUT', [
    'modules' => [
        'restaurant_mode' => '1',
    ]
]);

echo "request->has('modules.restaurant_mode'): " . ($request->has('modules.restaurant_mode') ? 'true' : 'false') . "\n";
echo "request->has('modules.table_management'): " . ($request->has('modules.table_management') ? 'true' : 'false') . "\n";

// Let's also check with request->input()
echo "request->input('modules.restaurant_mode'): " . var_export($request->input('modules.restaurant_mode'), true) . "\n";
echo "request->input('modules.table_management'): " . var_export($request->input('modules.table_management'), true) . "\n";
