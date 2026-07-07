<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\ModuleSetting;

echo "COMPANIES:\n";
foreach (Company::all() as $company) {
    echo "- ID: {$company->id}, Name: {$company->name}, Business Type: {$company->business_type}\n";
    echo "  MODULE SETTINGS:\n";
    $settings = ModuleSetting::where('company_id', $company->id)->get();
    if ($settings->isEmpty()) {
        echo "    No module settings records found.\n";
    }
    foreach ($settings as $setting) {
        echo "    * {$setting->module_key}: " . ($setting->is_enabled ? 'ENABLED' : 'DISABLED') . "\n";
    }
}
