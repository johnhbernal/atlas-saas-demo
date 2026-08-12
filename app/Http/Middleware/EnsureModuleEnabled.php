<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureModuleEnabled
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        $company = $this->tenant->company();
        if ($company === null || ! $company->hasModule($moduleCode)) {
            return response()->json([
                'message' => "Module [{$moduleCode}] is not enabled for this company.",
            ], 403);
        }

        return $next($request);
    }
}
