<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::find(1);
if ($user) {
    $user->password = Hash::make('password');
    $user->save();
    echo "Password for admin@yummy.com has been set to 'password'\n";
} else {
    echo "User 1 not found\n";
}
