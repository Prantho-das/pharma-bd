<?php

namespace App\Actions\Pos;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Services\LedgerAccountingService;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessSaleReturnAction
{
    public function __construct(
        protected UnitConversionService $unitConversionService,
        protected LedgerAccountingService $ledgerAccountingService
    ) {}

    /**
     * Process sales return and refund.
     *
     * @param  array  $payload  = [
     *                          'sale_id' => int,
     *                          'branch_id' => ?int,
     *                          'user_id' => ?int,
     *                          'refund_type' => 'CASH'|'DUE_ADJUSTMENT',
     *                          'reason' => ?string,
     *                          'items' => [
     *                          [
     *                          'product_id' => int,
     *                          'batch_id' => ?int,
     *                          'unit_id' => ?int,
     *                          'quantity' => int,
     *                          'refund_unit_price' => float,
     *                          'is_damaged' => bool,
     *                          ]
     *                          ]
     *                          ]
     *
     * @throws Exception
     */
    public function execute(array $payload): SaleReturn
    {
        return DB::transaction(function () use ($payload) {
            $sale = Sale::lockForUpdate()->findOrFail($payload['sale_id']);
            $returnNo = 'RET-'.strtoupper(Str::random(3)).'-'.date('YmdHis');

            $totalRefund = 0.00;

            $saleReturn = SaleReturn::create([
                'branch_id' => $payload['branch_id'] ?? $sale->branch_id,
                'return_no' => $returnNo,
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'total_refund_amount' => 0.00,
                'refund_type' => $payload['refund_type'] ?? 'CASH',
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
                $isDamaged = (bool) ($itemData['is_damaged'] ?? false);
                $unitRefundPrice = (float) $itemData['refund_unit_price'];
                $itemRefundTotal = round($qty * $unitRefundPrice, 2);

                $totalPieces = $this->unitConversionService->toBasePieces($product, $qty, $unitId);
                $multiplier = $qty > 0 ? (int) ($totalPieces / $qty) : 1;

                $batchId = $itemData['batch_id'] ?? null;
                if ($batchId && ! $isDamaged) {
                    $batch = Batch::lockForUpdate()->find($batchId);
                    if ($batch) {
                        $batch->increment('current_stock', $totalPieces);
                    }
                }

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'product_id' => $product->id,
                    'batch_id' => $batchId,
                    'unit_id' => $unitId,
                    'quantity_returned' => $qty,
                    'unit_multiplier' => $multiplier,
                    'pieces_returned' => $totalPieces,
                    'refund_unit_price' => $unitRefundPrice,
                    'total_refund' => $itemRefundTotal,
                    'is_damaged' => $isDamaged,
                ]);

                $totalRefund += $itemRefundTotal;
            }

            if ($totalRefund <= 0) {
                throw new Exception('Return items cannot be empty or have zero value.');
            }

            $saleReturn->update(['total_refund_amount' => round($totalRefund, 2)]);

            // Accounting updates
            if ($sale->customer_id) {
                $customer = Customer::lockForUpdate()->find($sale->customer_id);
                if ($customer) {
                    if ($saleReturn->refund_type === 'DUE_ADJUSTMENT') {
                        $this->ledgerAccountingService->recordCustomerTransaction(
                            customerId: $customer->id,
                            type: 'RETURN',
                            amount: $totalRefund,
                            referenceId: $saleReturn->id,
                            note: "Refund applied to account for Return {$returnNo}",
                            branchId: $saleReturn->branch_id
                        );
                    }
                }
            }

            // Mark Sale as returned / partially returned
            $sale->update(['status' => 'RETURNED']);

            return $saleReturn->load(['items.product', 'items.batch', 'customer', 'sale']);
        });
    }
}
