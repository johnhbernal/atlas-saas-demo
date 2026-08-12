<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function seedTwoTenants(): array
{
    foreach (['core' => 'Core', 'inventario' => 'Inventory'] as $code => $name) {
        Module::query()->create(['code' => $code, 'name' => $name, 'category' => $code === 'core' ? 'base' : 'addon']);
    }

    $a = Company::query()->create(['name' => 'Company A', 'slug' => 'company-a']);
    $b = Company::query()->create(['name' => 'Company B', 'slug' => 'company-b']);
    $inventario = Module::query()->where('code', 'inventario')->firstOrFail();
    $core = Module::query()->where('code', 'core')->firstOrFail();

    foreach ([$a, $b] as $company) {
        $company->modules()->attach([$core->id => ['enabled' => true], $inventario->id => ['enabled' => true]]);
    }

    $userA = User::query()->create([
        'name' => 'User A',
        'email' => 'a@test.com',
        'password' => Hash::make('password'),
        'current_company_id' => $a->id,
    ]);
    $userA->companies()->attach($a->id);

    $userB = User::query()->create([
        'name' => 'User B',
        'email' => 'b@test.com',
        'password' => Hash::make('password'),
        'current_company_id' => $b->id,
    ]);
    $userB->companies()->attach($b->id);

    $productA = Product::query()->create([
        'company_id' => $a->id,
        'sku' => 'A-1',
        'name' => 'Alpha SKU',
        'price' => 10,
        'stock_qty' => 5,
    ]);

    Product::query()->create([
        'company_id' => $b->id,
        'sku' => 'B-1',
        'name' => 'Beta SKU',
        'price' => 20,
        'stock_qty' => 9,
    ]);

    return compact('a', 'b', 'userA', 'userB', 'productA');
}

it('lists only products from the authenticated tenant', function () {
    ['userA' => $userA] = seedTwoTenants();

    $response = $this->actingAs($userA)
        ->withHeader('X-Company-Id', (string) $userA->current_company_id)
        ->getJson('/api/products');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.sku', 'A-1');
});

it('cannot read another tenant product by id', function () {
    ['userB' => $userB, 'productA' => $productA] = seedTwoTenants();

    $this->actingAs($userB)
        ->withHeader('X-Company-Id', (string) $userB->current_company_id)
        ->getJson('/api/products/'.$productA->id)
        ->assertNotFound();
});

it('rejects company header the user does not belong to', function () {
    ['userA' => $userA, 'b' => $b] = seedTwoTenants();

    $this->actingAs($userA)
        ->withHeader('X-Company-Id', (string) $b->id)
        ->getJson('/api/products')
        ->assertForbidden();
});
