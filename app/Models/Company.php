<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'owner_id', 'business_type', 'settings'];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the owner of the company.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get users assigned to this company.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get module settings for the company.
     */
    public function moduleSettings()
    {
        return $this->hasMany(ModuleSetting::class);
    }

    /**
     * Check if a specific module is enabled.
     */
    public function isModuleEnabled(string $moduleKey): bool
    {
        return $this->moduleSettings()->where('module_key', $moduleKey)->where('is_enabled', true)->exists();
    }
}
