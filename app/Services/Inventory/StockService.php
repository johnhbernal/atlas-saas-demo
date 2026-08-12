<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Simple inventory engine: inbound updates weighted average cost;
 * outbound decreases qty with stock checks.
 */
final class StockService
{
    public function __construct(private readonly TenantContext $tenant) {}

    /**
     * @return array{product: Product, movement: StockMovement}
     */
    public function receive(Product $product, int $quantity, float $unitCost, ?string $note = null): array
    {
        $this->assertSameTenant($product);

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $quantity, $unitCost, $note) {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);

            $currentQty = (int) $product->stock_qty;
            $currentAvg = (float) $product->avg_cost;
            $incomingValue = $quantity * $unitCost;
            $existingValue = $currentQty * $currentAvg;
            $newQty = $currentQty + $quantity;
            $newAvg = $newQty > 0 ? ($existingValue + $incomingValue) / $newQty : 0.0;

            $product->stock_qty = $newQty;
            $product->avg_cost = round($newAvg, 2);
            $product->save();

            $movement = StockMovement::query()->create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'note' => $note,
            ]);

            return ['product' => $product->fresh(), 'movement' => $movement];
        });
    }

    /**
     * @return array{product: Product, movement: StockMovement}
     */
    public function ship(Product $product, int $quantity, ?string $note = null): array
    {
        $this->assertSameTenant($product);

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $quantity, $note) {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);

            if ($product->stock_qty < $quantity) {
                throw new InvalidArgumentException('Insufficient stock.');
            }

            $product->stock_qty = (int) $product->stock_qty - $quantity;
            $product->save();

            $movement = StockMovement::query()->create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $quantity,
                'unit_cost' => $product->avg_cost,
                'note' => $note,
            ]);

            return ['product' => $product->fresh(), 'movement' => $movement];
        });
    }

    private function assertSameTenant(Product $product): void
    {
        $tenantId = $this->tenant->companyId();
        if ($tenantId !== null && (int) $product->company_id !== $tenantId) {
            throw new InvalidArgumentException('Product does not belong to current tenant.');
        }
    }
}
