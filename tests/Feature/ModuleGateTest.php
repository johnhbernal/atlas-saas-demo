<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function seedCompanyWithModules(array $enabled = [], array $disabled = []): array
{
    foreach (['core', 'inventario'] as $code) {
        Module::query()->create([
            'code' => $code,
            'name' => $code,
            'category' => $code === 'core' ? 'base' : 'addon',
        ]);
    }

    $company = Company::query()->create(['name' => 'Gate Co', 'slug' => 'gate-co']);
    $pivot = [];
    foreach ($enabled as $code) {
        $pivot[Module::query()->where('code', $code)->firstOrFail()->id] = ['enabled' => true];
    }
    foreach ($disabled as $code) {
        $pivot[Module::query()->where('code', $code)->firstOrFail()->id] = ['enabled' => false];
    }
    $company->modules()->attach($pivot);

    $user = User::query()->create([
        'name' => 'Gate User',
        'email' => 'gate@test.com',
        'password' => Hash::make('password'),
        'current_company_id' => $company->id,
    ]);
    $user->companies()->attach($company->id);

    Product::query()->create([
        'company_id' => $company->id,
        'sku' => 'G-1',
        'name' => 'Gated SKU',
        'price' => 10,
        'stock_qty' => 3,
    ]);

    return compact('company', 'user');
}

it('answers 401 on a data endpoint without a token', function () {
    $this->withHeader('X-Company-Id', '1')
        ->getJson('/api/products')
        ->assertUnauthorized();
});

it('answers 403 when the company does not have the inventario module attached', function () {
    ['user' => $user, 'company' => $company] = seedCompanyWithModules(enabled: ['core']);

    $this->actingAs($user)
        ->withHeader('X-Company-Id', (string) $company->id)
        ->getJson('/api/products')
        ->assertForbidden()
        ->assertJsonPath('message', 'Module [inventario] is not enabled for this company.');
});

it('answers 403 when the module is attached but disabled', function () {
    ['user' => $user, 'company' => $company] = seedCompanyWithModules(enabled: ['core'], disabled: ['inventario']);

    $this->actingAs($user)
        ->withHeader('X-Company-Id', (string) $company->id)
        ->getJson('/api/products')
        ->assertForbidden();
});

it('opens the module endpoints once inventario is enabled', function () {
    ['user' => $user, 'company' => $company] = seedCompanyWithModules(enabled: ['core', 'inventario']);

    $this->actingAs($user)
        ->withHeader('X-Company-Id', (string) $company->id)
        ->getJson('/api/products')
        ->assertOk()
        ->assertJsonPath('data.0.sku', 'G-1');
});
