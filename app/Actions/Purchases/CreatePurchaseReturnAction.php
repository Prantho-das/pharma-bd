<?php

namespace App\Actions\Purchases;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Services\LedgerAccountingService;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePurchaseReturnAction
{
    public function __construct(
        protected UnitConversionService $unitConversionService,
        protected LedgerAccountingService $ledgerAccountingService
    ) {}

    /**
     * Process purchase return to supplier.
     *
     * @param  array  $payload  = [
     *                          'purchase_id' => ?int,
     *                          'supplier_id' => int,
     *                          'branch_id' => ?int,
     *                          'user_id' => ?int,
     *                          'refund_status' => 'PENDING'|'ADJUSTED'|'REFUNDED',
     *                          'reason' => ?string,
     *                          'items' => [
     *                          [
     *                          'product_id' => int,
     *                          'batch_id' => ?int,
     *                          'unit_id' => ?int,
     *                          'quantity' => int,
     *                          'return_price_per_unit' => float,
     *                          ]
     *                          ]
     *                          ]
     *
     * @throws Exception
     */
    public function execute(array $payload): PurchaseReturn
    {
        return DB::transaction(function () use ($payload) {
            $supplier = Supplier::lockForUpdate()->findOrFail($payload['supplier_id']);
            $returnNo = 'PR-RET-'.strtoupper(Str::random(3)).'-'.date('YmdHis');
            $totalRefund = 0.00;

            $purchaseReturn = PurchaseReturn::create([
                'branch_id' => $payload['branch_id'] ?? auth()->user()?->branch_id,
                'return_no' => $returnNo,
                'purchase_id' => $payload['purchase_id'] ?? null,
                'supplier_id' => $supplier->id,
                'total_refund_amount' => 0.00,
                'refund_status' => $payload['refund_status'] ?? 'ADJUSTED',
                'reason' => $payload['reason'] ?? null,
                'user_id' => $payload['user_id'] ?? auth()->id(),
            ]);

            foreach ($payload['items'] as $itemData) {
                $qty = (int) ($itemData['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $product = Product::findOrFail($itemData['product_id']);
                $unitId = $itemData['unit_id'] ?? $product->base_unit_id;
                $unitPrice = (float) $itemData['return_price_per_unit'];
                $itemRefundTotal = round($qty * $unitPrice, 2);

                $totalPieces = $this->unitConversionService->toBasePieces($product, $qty, $unitId);
                $multiplier = $qty > 0 ? (int) ($totalPieces / $qty) : 1;

                $batchId = $itemData['batch_id'] ?? null;
                if ($batchId) {
                    $batch = Batch::lockForUpdate()->findOrFail($batchId);
                    if ($batch->current_stock < $totalPieces) {
                        throw new Exception("Insufficient stock in batch {$batch->batch_number} to return {$totalPieces} pieces.");
                    }
                    $batch->decrement('current_stock', $totalPieces);
                }

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $product->id,
                    'batch_id' => $batchId,
                    'unit_id' => $unitId,
                    'quantity_returned' => $qty,
                    'unit_multiplier' => $multiplier,
                    'pieces_returned' => $totalPieces,
                    'return_price_per_unit' => $unitPrice,
                    'total_refund' => $itemRefundTotal,
                ]);

                $totalRefund += $itemRefundTotal;
            }

            if ($totalRefund <= 0) {
                throw new Exception('Please specify valid items with quantity and price for purchase return.');
            }

            $purchaseReturn->update(['total_refund_amount' => round($totalRefund, 2)]);

            // Automatically post debit note to supplier ledger
            $this->ledgerAccountingService->recordSupplierTransaction(
                supplierId: $supplier->id,
                type: 'RETURN',
                amount: $totalRefund,
                referenceId: $purchaseReturn->id,
                note: "Debit note for Purchase Return {$returnNo}",
                branchId: $purchaseReturn->branch_id
            );

            return $purchaseReturn->load(['items.product', 'items.batch', 'supplier', 'purchase']);
        });
    }
}
