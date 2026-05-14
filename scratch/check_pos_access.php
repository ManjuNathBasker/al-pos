<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/pos', 'GET')
);

// We need to simulate a login
$user = App\Models\User::find(1);
Auth::login($user);
session(['company_id' => 1]);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/pos', 'GET')
);

echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() == 403) {
    echo "403 Forbidden detected!\n";
} else {
    echo "Access granted (or redirected: " . $response->getStatusCode() . ")\n";
}
