<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    protected $fillable = [
        'company_id',
        'module_key',
        'is_enabled',
        'config',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config'     => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
