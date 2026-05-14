<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'owner_id'];

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
}
