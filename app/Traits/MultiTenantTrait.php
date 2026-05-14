<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait MultiTenantTrait
{
    protected static function bootMultiTenantTrait()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                // Auto-assign company_id if column exists
                if (Schema::hasColumn($model->getTable(), 'company_id') && ! $model->company_id) {
                    $model->company_id = session('company_id');
                }
                
                // Auto-assign created_by if column exists
                if (Schema::hasColumn($model->getTable(), 'created_by')) {
                    $model->created_by = Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                // Auto-assign updated_by if column exists
                if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                    $model->updated_by = Auth::id();
                }
            }
        });

        static::addGlobalScope('company', function (Builder $builder) {
            $companyId = session('company_id');
            
            if (Auth::check() && $companyId) {
                // Only filter if table has company_id column
                $builder->where($builder->getQuery()->from . '.company_id', $companyId);
            }
        });
    }
}
