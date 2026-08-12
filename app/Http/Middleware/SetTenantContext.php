<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves tenant from authenticated user's current_company_id
 * or X-Company-Id header (must be a company the user belongs to).
 */
final class SetTenantContext
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $headerId = $request->header('X-Company-Id');
        $companyId = $headerId !== null && $headerId !== ''
            ? (int) $headerId
            : $user->current_company_id;

        if ($companyId === null) {
            return response()->json(['message' => 'Tenant company is required.'], 422);
        }

        $belongs = $user->companies()->where('companies.id', $companyId)->exists();
        if (! $belongs) {
            return response()->json(['message' => 'Forbidden for this company.'], 403);
        }

        $company = Company::query()->findOrFail($companyId);
        $this->tenant->set($company);

        if ((int) $user->current_company_id !== (int) $company->id) {
            $user->forceFill(['current_company_id' => $company->id])->save();
        }

        return $next($request);
    }
}
