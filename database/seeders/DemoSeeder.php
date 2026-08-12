<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Module;
use App\Models\Product;
use App\Models\User;
use App\Services\Inventory\StockService;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('modules.catalog') as $code => $meta) {
            Module::query()->updateOrCreate(
                ['code' => $code],
                ['name' => $meta['name'], 'category' => $meta['category']],
            );
        }

        $alpha = Company::query()->create([
            'name' => 'Alpha Retail SAS',
            'slug' => 'alpha-retail',
            'nit' => '900111222-3',
        ]);

        $beta = Company::query()->create([
            'name' => 'Beta Supplies Ltda',
            'slug' => 'beta-supplies',
            'nit' => '900333444-5',
        ]);

        $moduleIds = Module::query()->pluck('id', 'code');
        foreach ([$alpha, $beta] as $company) {
            $company->modules()->sync([
                $moduleIds['core'] => ['enabled' => true],
                $moduleIds['inventario'] => ['enabled' => true],
                $moduleIds['reportes'] => ['enabled' => $company->is($alpha)],
            ]);
        }

        $admin = User::query()->create([
            'name' => 'John Demo',
            'email' => 'demo@atlas.test',
            'password' => Hash::make('password'),
            'current_company_id' => $alpha->id,
        ]);

        $admin->companies()->attach([
            $alpha->id => ['role' => 'admin'],
            $beta->id => ['role' => 'admin'],
        ]);

        $visitor = User::query()->create([
            'name' => 'Beta Only User',
            'email' => 'beta@atlas.test',
            'password' => Hash::make('password'),
            'current_company_id' => $beta->id,
        ]);
        $visitor->companies()->attach($beta->id, ['role' => 'ops']);

        $tenant = app(TenantContext::class);
        $stock = app(StockService::class);

        $tenant->set($alpha);
        $coffee = Product::query()->create([
            'company_id' => $alpha->id,
            'sku' => 'CAF-001',
            'name' => 'Café Premium 500g',
            'price' => 18000,
        ]);
        $stock->receive($coffee, 40, 9000, 'Initial PO');
        $stock->ship($coffee, 5, 'POS sale');

        $tenant->set($beta);
        $paper = Product::query()->create([
            'company_id' => $beta->id,
            'sku' => 'PAP-100',
            'name' => 'Resma carta',
            'price' => 22000,
        ]);
        $stock->receive($paper, 100, 12000, 'Warehouse inbound');

        $tenant->clear();
    }
}
