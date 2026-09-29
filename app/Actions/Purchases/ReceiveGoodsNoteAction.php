<?php

namespace App\Actions\Purchases;

use App\Models\Batch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\LedgerAccountingService;
use App\Services\UnitConversionService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReceiveGoodsNoteAction
{
    public function __construct(
        protected UnitConversionService $unitConversionService,
        protected LedgerAccountingService $ledgerAccountingService
    ) {}

    /**
     * Process Goods Receive Note (GRN) & create inventory batches.
     *
     * @param  array  $payload  = [
     *                          'branch_id' => ?int,
     *                          'supplier_id' => int,
     *                          'supplier_invoice_no' => ?string,
     *                          'invoice_date' => ?string,
     *                          'delivery_date' => ?string,
     *                          'paid_amount' => float,
     *                          'notes' => ?string,
     *                          'items' => [
     *                          [
     *                          'product_id' => int,
     *                          'batch_number' => string,
     *                          'expiry_date' => string,
     *                          'unit_id' => ?int,
     *                          'quantity' => int,
     *                          'bonus_quantity' => int,
     *                          'purchase_price_per_unit' => float,
     *                          'mrp_per_unit' => float,
     *                          ]
     *                          ]
     *                          ]
     *
     * @throws Exception
     */
    public function execute(array $payload): Purchase
    {
        return DB::transaction(function () use ($payload) {
            $purchaseNo = 'PO-'.strtoupper(Str::random(3)).'-'.date('YmdHis');

            $subTotal = 0.00;
            $itemsData = [];

            foreach ($payload['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = $item['unit_id'] ?? $product->base_unit_id;

                $productUnit = ProductUnit::where('product_id', $product->id)
                    ->where('unit_id', $unitId)
                    ->first();
                $multiplier = $productUnit ? max(1, $productUnit->multiplier) : 1;

                $qty = (int) $item['quantity'];
                $bonusQty = (int) ($item['bonus_quantity'] ?? 0);
                $totalPieces = ($qty + $bonusQty) * $multiplier;

                $cost = (float) $item['purchase_price_per_unit'] * $qty;
                $subTotal += $cost;

                // Effective purchase rate per base piece
                $effectiveBaseRate = $this->unitConversionService->calculateEffectiveBaseCost(
                    $qty,
                    $bonusQty,
                    $multiplier,
                    $cost
                );

                $mrpBaseRate = round((float) $item['mrp_per_unit'] / $multiplier, 4);

                $itemsData[] = [
                    'product' => $product,
                    'unit_id' => $unitId,
                    'multiplier' => $multiplier,
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'],
                    'quantity' => $qty,
                    'bonus_quantity' => $bonusQty,
                    'total_pieces' => $totalPieces,
                    'purchase_price_per_unit' => $item['purchase_price_per_unit'],
                    'mrp_per_unit' => $item['mrp_per_unit'],
                    'total_cost' => $cost,
                    'effective_base_rate' => $effectiveBaseRate,
                    'mrp_base_rate' => $mrpBaseRate,
                ];
            }

            $discount = (float) ($payload['discount'] ?? 0);
            $taxAmount = (float) ($payload['tax_amount'] ?? 0);
            $grandTotal = round(max(0, $subTotal - $discount + $taxAmount), 2);
            $paidAmount = (float) ($payload['paid_amount'] ?? 0);
            $dueAmount = round(max(0, $grandTotal - $paidAmount), 2);

            $paymentStatus = 'PAID';
            if ($dueAmount > 0) {
                $paymentStatus = $paidAmount > 0 ? 'PARTIAL' : 'DUE';
            }

            $purchase = Purchase::create([
                'branch_id' => $payload['branch_id'] ?? null,
                'purchase_no' => $purchaseNo,
                'supplier_id' => $payload['supplier_id'],
                'supplier_invoice_no' => $payload['supplier_invoice_no'] ?? null,
                'invoice_date' => $payload['invoice_date'] ?? now()->toDateString(),
                'delivery_date' => $payload['delivery_date'] ?? now()->toDateString(),
                'sub_total' => $subTotal,
                'discount' => $discount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'status' => 'RECEIVED',
                'notes' => $payload['notes'] ?? null,
            ]);

            // Save items & create batches
            foreach ($itemsData as $data) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $data['product']->id,
                    'batch_number' => $data['batch_number'],
                    'expiry_date' => $data['expiry_date'],
                    'unit_id' => $data['unit_id'],
                    'quantity' => $data['quantity'],
                    'bonus_quantity' => $data['bonus_quantity'],
                    'unit_multiplier' => $data['multiplier'],
                    'total_pieces' => $data['total_pieces'],
                    'purchase_price_per_unit' => $data['purchase_price_per_unit'],
                    'mrp_per_unit' => $data['mrp_per_unit'],
                    'total_cost' => $data['total_cost'],
                ]);

                // Create Batch in base pieces
                Batch::create([
                    'branch_id' => $payload['branch_id'] ?? null,
                    'product_id' => $data['product']->id,
                    'batch_number' => $data['batch_number'],
                    'purchase_item_id' => $purchaseItem->id,
                    'base_unit_id' => $data['product']->base_unit_id,
                    'initial_stock' => $data['total_pieces'],
                    'current_stock' => $data['total_pieces'],
                    'purchase_rate' => $data['effective_base_rate'],
                    'mrp_rate' => $data['mrp_base_rate'],
                    'expiry_date' => $data['expiry_date'],
                ]);
            }

            // Record in Supplier Ledger
            $this->ledgerAccountingService->recordSupplierTransaction(
                supplierId: $payload['supplier_id'],
                transactionType: 'PURCHASE',
                debit: $paidAmount, // Paid amount reduces payable
                credit: $grandTotal, // Bill amount increases payable
                referenceId: $purchase->id,
                note: "Purchase GRN #{$purchaseNo}",
                branchId: $payload['branch_id'] ?? null
            );

            return $purchase;
        });
    }
}
