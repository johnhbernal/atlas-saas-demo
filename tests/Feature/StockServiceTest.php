<?php

use App\Models\Company;
use App\Models\Product;
use App\Services\Inventory\StockService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates weighted average cost on receive and decreases stock on ship', function () {
    $company = Company::query()->create(['name' => 'Stock Co', 'slug' => 'stock-co']);
    $tenant = app(TenantContext::class);
    $tenant->set($company);

    $product = Product::query()->create([
        'company_id' => $company->id,
        'sku' => 'SKU-1',
        'name' => 'Widget',
        'price' => 100,
        'stock_qty' => 0,
        'avg_cost' => 0,
    ]);

    $stock = app(StockService::class);
    $stock->receive($product, 10, 10.0);
    $product->refresh();
    expect((int) $product->stock_qty)->toBe(10)
        ->and((float) $product->avg_cost)->toBe(10.0);

    $stock->receive($product, 10, 20.0);
    $product->refresh();
    expect((int) $product->stock_qty)->toBe(20)
        ->and((float) $product->avg_cost)->toBe(15.0);

    $stock->ship($product, 5);
    $product->refresh();
    expect((int) $product->stock_qty)->toBe(15);

    $tenant->clear();
});

it('rejects shipping more than available stock', function () {
    $company = Company::query()->create(['name' => 'Stock Co 2', 'slug' => 'stock-co-2']);
    app(TenantContext::class)->set($company);

    $product = Product::query()->create([
        'company_id' => $company->id,
        'sku' => 'SKU-2',
        'name' => 'Gadget',
        'price' => 50,
        'stock_qty' => 2,
        'avg_cost' => 5,
    ]);

    expect(fn () => app(StockService::class)->ship($product, 3))
        ->toThrow(\InvalidArgumentException::class, 'Insufficient stock.');
});
