<?php

namespace App\Actions\Inventory;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateStockTransferAction
{
    public function __construct(
        protected UnitConversionService $unitConversionService
    ) {}

    /**
     * Dispatch stock transfer between branches.
     *
     * @param  array  $payload  = [
     *                          'from_branch_id' => int,
     *                          'to_branch_id' => int,
     *                          'sent_by' => int,
     *                          'notes' => ?string,
     *                          'items' => [
     *                          [
     *                          'product_id' => int,
     *                          'batch_id' => int,
     *                          'unit_id' => ?int,
     *                          'quantity' => int,
     *                          ]
     *                          ]
     *                          ]
     *
     * @throws Exception
     */
    public function execute(array $payload): StockTransfer
    {
        return DB::transaction(function () use ($payload) {
            if ($payload['from_branch_id'] === $payload['to_branch_id']) {
                throw new Exception('Source and destination branch cannot be the same.');
            }

            $transferNo = 'TRF-'.strtoupper(Str::random(3)).'-'.date('YmdHis');

            $transfer = StockTransfer::create([
                'transfer_no' => $transferNo,
                'from_branch_id' => $payload['from_branch_id'],
                'to_branch_id' => $payload['to_branch_id'],
                'status' => 'IN_TRANSIT',
                'sent_by' => $payload['sent_by'] ?? auth()->id(),
                'dispatched_at' => now(),
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($payload['items'] as $itemData) {
                $qty = (int) ($itemData['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $product = Product::findOrFail($itemData['product_id']);
                $batch = Batch::lockForUpdate()->findOrFail($itemData['batch_id']);
                $unitId = $itemData['unit_id'] ?? $product->base_unit_id;

                $totalPieces = $this->unitConversionService->toBasePieces($product, $qty, $unitId);
                $multiplier = $qty > 0 ? (int) ($totalPieces / $qty) : 1;

                if ($batch->current_stock < $totalPieces) {
                    throw new Exception("Source branch does not have enough stock for {$product->name} (Batch: {$batch->batch_number}). Available: {$batch->current_stock}, Requested: {$totalPieces}");
                }

                // Decrement from source branch batch
                $batch->decrement('current_stock', $totalPieces);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'unit_id' => $unitId,
                    'quantity' => $qty,
                    'unit_multiplier' => $multiplier,
                    'total_pieces' => $totalPieces,
                ]);
            }

            return $transfer->load(['items.product', 'items.batch', 'fromBranch', 'toBranch', 'sentBy']);
        });
    }
}
