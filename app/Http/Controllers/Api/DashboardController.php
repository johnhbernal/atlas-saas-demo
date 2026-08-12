<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function __invoke(): JsonResponse
    {
        $company = $this->tenant->company();
        $skus = Product::query()->count();
        $lowStock = Product::query()->where('stock_qty', '<', 5)->where('active', true)->count();
        $movementsToday = StockMovement::query()->whereDate('created_at', now()->toDateString())->count();
        $inventoryValue = (float) Product::query()
            ->selectRaw('COALESCE(SUM(stock_qty * avg_cost), 0) as value')
            ->value('value');

        return response()->json([
            'company' => [
                'id' => $company?->id,
                'name' => $company?->name,
                'slug' => $company?->slug,
            ],
            'kpis' => [
                'status' => $lowStock > 0 ? 'ALERTA' : 'OK',
                'sku_count' => $skus,
                'low_stock_count' => $lowStock,
                'movements_today' => $movementsToday,
                'inventory_value' => round($inventoryValue, 2),
            ],
            'modules' => $company?->modules()->wherePivot('enabled', true)->pluck('code') ?? [],
        ]);
    }
}
