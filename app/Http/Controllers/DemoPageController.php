<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Services\TenantContext;
use Illuminate\View\View;

class DemoPageController extends Controller
{
    public function __invoke(TenantContext $tenant): View
    {
        $company = Company::query()->where('slug', 'alpha-retail')->firstOrFail();
        $tenant->set($company);

        $products = Product::query()->orderBy('name')->get();
        $lowStock = Product::query()->where('stock_qty', '<', 5)->where('active', true)->count();
        $inventoryValue = (float) Product::query()
            ->selectRaw('COALESCE(SUM(stock_qty * avg_cost), 0) as value')
            ->value('value');

        $tenant->clear();

        return view('demo', [
            'company' => $company,
            'products' => $products,
            'modules' => $company->modules()->wherePivot('enabled', true)->pluck('code')->all(),
            'kpis' => [
                'status' => $lowStock > 0 ? 'ALERTA' : 'OK',
                'sku_count' => $products->count(),
                'low_stock_count' => $lowStock,
                'inventory_value' => $inventoryValue,
            ],
        ]);
    }
}
