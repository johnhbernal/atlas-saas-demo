<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Eloquent scope: when TenantContext has a company, filter by company_id.
 * Fail-open without context (CLI/seeders). API middleware sets context fail-closed.
 *
 * @template TModel of Model
 *
 * @implements Scope<TModel>
 */
class CompanyScope implements Scope
{
    /**
     * @param  Builder<TModel>  $builder
     * @param  TModel  $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->hasCompany()) {
            $builder->where($model->getTable().'.company_id', $context->companyId());
        }
    }
}
