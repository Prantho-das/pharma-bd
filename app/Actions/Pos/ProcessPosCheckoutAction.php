<?php

namespace App\Actions\Pos;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Services\BatchAllocationService;
use App\Services\LedgerAccountingService;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessPosCheckoutAction
{
    public function __construct(
        protected BatchAllocationService $batchAllocationService,
        protected UnitConversionService $unitConversionService,
        protected LedgerAccountingService $ledgerAccountingService
    ) {}

    /**
     * Process POS sale checkout atomically.
     *
     * @param  array  $payload  = [
     *                          'branch_id' => ?int,
     *                          'user_id' => int,
     *                          'customer_id' => ?int,
     *                          'doctor_id' => ?int,
     *                          'items' => [
     *                          ['product_id' => int, 'unit_id' => ?int, 'quantity' => int, 'unit_price' => float, 'discount' => float]
     *                          ],
     *                          'sub_total' => float,
     *                          'discount_type' => 'fixed'|'percentage',
     *                          'discount_amount' => float,
     *                          'tax_amount' => float,
     *                          'round_off' => float,
     *                          'grand_total' => float,
     *                          'payments' => [
     *                          ['method' => 'CASH'|'CARD'|'BKASH'|'NAGAD'|'ROCKET'|'BANK_TRANSFER'|'DUE', 'amount' => float, 'ref' => ?string, 'card_last_four' => ?string]
     *                          ],
     *                          'notes' => ?string,
     *                          'prescription_image_path' => ?string,
     *                          ]
     *
     * @throws Exception
     */
    public function execute(array $payload): Sale
    {
        return DB::transaction(function () use ($payload) {
            $invoiceNo = 'INV-'.strtoupper(Str::random(3)).'-'.date('YmdHis');

            $paidAmount = 0.00;
            $dueAmount = 0.00;

            foreach ($payload['payments'] as $p) {
                if (strtoupper($p['method']) === 'DUE') {
                    $dueAmount += (float) $p['amount'];
                } else {
                    $paidAmount += (float) $p['amount'];
                }
            }

            $grandTotal = (float) $payload['grand_total'];
            $changeAmount = max(0, $paidAmount - $grandTotal);
            $effectivePaid = min($paidAmount, $grandTotal);
            $effectiveDue = max(0, $grandTotal - $effectivePaid);

            $paymentStatus = 'PAID';
            if ($effectiveDue > 0) {
                $paymentStatus = $effectivePaid > 0 ? 'PARTIAL' : 'DUE';
            }

            $sale = Sale::create([
                'branch_id' => $payload['branch_id'] ?? null,
                'invoice_no' => $invoiceNo,
                'customer_id' => $payload['customer_id'] ?? null,
                'doctor_id' => $payload['doctor_id'] ?? null,
                'user_id' => $payload['user_id'],
                'sale_date' => now(),
                'sub_total' => $payload['sub_total'],
                'discount_type' => $payload['discount_type'] ?? 'fixed',
                'discount_amount' => $payload['discount_amount'] ?? 0,
                'tax_amount' => $payload['tax_amount'] ?? 0,
                'round_off' => $payload['round_off'] ?? 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $effectivePaid,
                'due_amount' => $effectiveDue,
                'change_amount' => $changeAmount,
                'payment_status' => $paymentStatus,
                'status' => 'COMPLETED',
                'notes' => $payload['notes'] ?? null,
                'prescription_image_path' => $payload['prescription_image_path'] ?? null,
            ]);

            // Process Items and FEFO Allocations
            foreach ($payload['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitId = $item['unit_id'] ?? $product->base_unit_id;

                $totalPieces = $this->unitConversionService->toBasePieces($product, $quantity, $unitId);
                $unitMultiplier = $quantity > 0 ? (int) ($totalPieces / $quantity) : 1;

                // Allocate from batches using FEFO
                $allocations = $this->batchAllocationService->allocateFefo($product, $totalPieces, $payload['branch_id'] ?? null);

                // Deduct batches
                $this->batchAllocationService->deductStock($allocations);

                // Record line items (if split across batches, save each or composite)
                foreach ($allocations as $alloc) {
                    $allocPieces = $alloc['pieces'];
                    $ratio = $allocPieces / max(1, $totalPieces);
                    $lineTotal = round(((float) $item['unit_price'] * $quantity - (float) ($item['discount'] ?? 0)) * $ratio, 2);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'batch_id' => $alloc['batch_id'],
                        'unit_id' => $unitId,
                        'quantity' => $quantity,
                        'unit_multiplier' => $unitMultiplier,
                        'total_pieces' => $allocPieces,
                        'unit_price' => $item['unit_price'],
                        'unit_cost' => $alloc['unit_cost'],
                        'sub_total' => round((float) $item['unit_price'] * $quantity * $ratio, 2),
                        'discount' => round((float) ($item['discount'] ?? 0) * $ratio, 2),
                        'total_amount' => $lineTotal,
                    ]);
                }
            }

            // Record Split Payments
            foreach ($payload['payments'] as $payment) {
                if ((float) $payment['amount'] > 0) {
                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'branch_id' => $payload['branch_id'] ?? null,
                        'customer_id' => $payload['customer_id'] ?? null,
                        'payment_method' => strtoupper($payment['method']),
                        'amount' => $payment['amount'],
                        'transaction_reference' => $payment['ref'] ?? null,
                        'card_last_four' => $payment['card_last_four'] ?? null,
                        'received_by' => $payload['user_id'],
                        'payment_date' => now(),
                    ]);
                }
            }

            // Record Customer Ledger if customer is attached
            if (! empty($payload['customer_id'])) {
                // Sale invoice increases due
                $this->ledgerAccountingService->recordCustomerTransaction(
                    customerId: $payload['customer_id'],
                    transactionType: 'SALE',
                    debit: $grandTotal,
                    credit: $effectivePaid,
                    referenceId: $sale->id,
                    note: "Invoice #{$invoiceNo}",
                    branchId: $payload['branch_id'] ?? null
                );
            }

            return $sale;
        });
    }
}
