<?php

namespace App\Actions\Inventory;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdjustBatchStockAction
{
    public function __construct(
        protected UnitConversionService $unitConversionService
    ) {}

    /**
     * Process stock adjustment.
     *
     * @param  array  $payload  = [
     *                          'branch_id' => ?int,
     *                          'user_id' => int,
     *                          'reason' => 'DAMAGE'|'EXPIRED'|'AUDIT_DISCREPANCY'|'THEFT'|'CORRECTION',
     *                          'notes' => ?string,
     *                          'items' => [
     *                          [
     *                          'product_id' => int,
     *                          'batch_id' => int,
     *                          'unit_id' => ?int,
     *                          'quantity' => int,
     *                          'adjustment_type' => 'ADDITION'|'DEDUCTION',
     *                          ]
     *                          ]
     *                          ]
     *
     * @throws Exception
     */
    public function execute(array $payload): StockAdjustment
    {
        return DB::transaction(function () use ($payload) {
            $adjustmentNo = 'ADJ-'.strtoupper(Str::random(3)).'-'.date('YmdHis');

            $totalLossValue = 0.00;

            $adjustment = StockAdjustment::create([
                'branch_id' => $payload['branch_id'] ?? null,
                'adjustment_no' => $adjustmentNo,
                'user_id' => $payload['user_id'],
                'reason' => $payload['reason'],
                'total_loss_value' => 0.00,
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($payload['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $batch = Batch::lockForUpdate()->findOrFail($item['batch_id']);
                $qty = (int) $item['quantity'];
                $unitId = $item['unit_id'] ?? $product->base_unit_id;

                $totalPieces = $this->unitConversionService->toBasePieces($product, $qty, $unitId);
                $multiplier = $qty > 0 ? (int) ($totalPieces / $qty) : 1;
                $adjType = strtoupper($item['adjustment_type']);

                if ($adjType === 'DEDUCTION') {
                    if ($batch->current_stock < $totalPieces) {
                        throw new Exception("Cannot deduct {$totalPieces} pieces. Batch only has {$batch->current_stock} pieces remaining.");
                    }
                    $batch->decrement('current_stock', $totalPieces);
                    $totalLossValue += ((float) $batch->purchase_rate * $totalPieces);
                } else {
                    $batch->increment('current_stock', $totalPieces);
                }

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'unit_id' => $unitId,
                    'quantity' => $qty,
                    'unit_multiplier' => $multiplier,
                    'total_pieces' => $totalPieces,
                    'adjustment_type' => $adjType,
                    'unit_cost' => $batch->purchase_rate,
                ]);
            }

            $adjustment->update(['total_loss_value' => round($totalLossValue, 2)]);

            return $adjustment;
        });
    }
}
