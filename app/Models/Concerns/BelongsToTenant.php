<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Tenant;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Automatically filter all queries by current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Tenant::current()) {
                $builder->where(
                    $builder->getModel()->qualifyColumn('tenant_id'),
                    Tenant::current()->id
                );
            }
        });

        // Automatically set tenant_id on create
        static::creating(function ($model) {
            if (Tenant::current() && empty($model->tenant_id)) {
                $model->tenant_id = Tenant::current()->id;
            }
        });
    }
}
