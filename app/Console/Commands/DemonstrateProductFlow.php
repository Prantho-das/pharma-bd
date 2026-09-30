<?php

namespace App\Console\Commands;

use App\Actions\Inventory\AdjustBatchStockAction;
use App\Actions\Pos\ProcessPosCheckoutAction;
use App\Actions\Pos\ProcessSaleReturnAction;
use App\Actions\Purchases\ReceiveGoodsNoteAction;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\UnitConversionService;
use Illuminate\Console\Command;

class DemonstrateProductFlow extends Command
{
    protected $signature = 'demo:product-flow';
    protected $description = 'Demonstrate complete lifecycle including Purchase, POS Sale, Customer Return, and Damage Adjustment';

    public function handle(
        ReceiveGoodsNoteAction $grnAction,
        ProcessPosCheckoutAction $posCheckoutAction,
        ProcessSaleReturnAction $saleReturnAction,
        AdjustBatchStockAction $stockAdjustmentAction,
        UnitConversionService $unitConversionService
    ): int {
        $this->info("==========================================================================");
        $this->info("   PHARMACY MANAGEMENT SYSTEM: END-TO-END PRODUCT, RETURN & DAMAGE FLOW   ");
        $this->info("==========================================================================");

        $branch = Branch::first() ?? Branch::create([
            'name' => 'Main Pharmacy Branch',
            'code' => 'MAIN-01',
            'is_active' => true,
        ]);

        $user = User::first() ?? User::create([
            'branch_id' => $branch->id,
            'name' => 'Pharmacist Staff',
            'email' => 'pharmacist@demo.com',
            'password' => bcrypt('password'),
            'role' => 'Pharmacist',
            'status' => 'active',
        ]);

        $supplier = Supplier::first() ?? Supplier::create([
            'name' => 'Square Pharma Distribution Agency',
            'phone' => '01700-000111',
            'address' => 'Mohakhali, Dhaka',
        ]);

        $customer = Customer::first() ?? Customer::create([
            'branch_id' => $branch->id,
            'name' => 'Mr. Rafiqul Islam (Walk-in Customer)',
            'phone' => '01800-000222',
            'address' => 'Banani, Dhaka',
        ]);

        $genParacetamol = Generic::firstOrCreate(['name' => 'Paracetamol 120mg/5ml']);
        $genEsomeprazole = Generic::firstOrCreate(['name' => 'Esomeprazole 20mg']);
        $catSyrup = Category::firstOrCreate(['name' => 'Syrups & Suspensions', 'slug' => 'syrups-suspensions']);
        $catCapsule = Category::firstOrCreate(['name' => 'Capsules & Tablets', 'slug' => 'capsules-tablets']);
        $mfgSquare = Manufacturer::firstOrCreate(['name' => 'Square Pharmaceuticals Ltd.']);

        $unitBottle = Unit::firstOrCreate(['name' => 'Bottle', 'short_code' => 'btl', 'conversion_factor' => 1, 'is_base_unit' => true]);
        $unitPiece  = Unit::firstOrCreate(['name' => 'Piece', 'short_code' => 'pcs', 'conversion_factor' => 1, 'is_base_unit' => true]);
        $unitStrip  = Unit::firstOrCreate(['name' => 'Strip', 'short_code' => 'str', 'conversion_factor' => 10, 'is_base_unit' => false]);
        $unitBox    = Unit::firstOrCreate(['name' => 'Box', 'short_code' => 'bx', 'conversion_factor' => 100, 'is_base_unit' => false]);

        $this->line("\n<fg=yellow;options=bold>STEP 1: CREATING PRODUCTS & MULTI-UNIT CATALOG</>");

        // Product 1: Simple Single-Unit Product (Syrup)
        $simpleSku = 'MED-ACE-SYRUP-' . rand(100, 999);
        $simpleProduct = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Ace Syrup 100ml (Paracetamol)',
            'generic_id' => $genParacetamol->id,
            'category_id' => $catSyrup->id,
            'manufacturer_id' => $mfgSquare->id,
            'base_unit_id' => $unitBottle->id,
            'sku' => $simpleSku,
            'barcode' => '894001001',
            'rack_location' => 'Rack S-01',
            'min_stock_alert' => 10,
            'status' => 'active',
        ]);

        ProductUnit::create([
            'product_id' => $simpleProduct->id,
            'unit_id' => $unitBottle->id,
            'multiplier' => 1,
            'purchase_price' => 28.00,
            'mrp_price' => 35.00,
            'is_default_purchase' => true,
            'is_default_sale' => true,
        ]);

        $this->info(" [x] Product 1 Created (Simple Single-Unit): {$simpleProduct->name}");
        $this->line("     - Base Unit: Bottle (1:1), MRP: 35.00 BDT, Purchase: 28.00 BDT");

        // Product 2: Multi-Unit Converted Product (Capsule: Box -> Strip -> Piece)
        $multiSku = 'MED-MAXPRO-20-' . rand(100, 999);
        $multiProduct = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Maxpro 20mg Capsule (Esomeprazole)',
            'generic_id' => $genEsomeprazole->id,
            'category_id' => $catCapsule->id,
            'manufacturer_id' => $mfgSquare->id,
            'base_unit_id' => $unitPiece->id,
            'sku' => $multiSku,
            'barcode' => '894002002',
            'rack_location' => 'Rack C-05',
            'min_stock_alert' => 50,
            'status' => 'active',
        ]);

        // Units: Piece (1x), Strip (10x), Box (100x)
        ProductUnit::create([
            'product_id' => $multiProduct->id,
            'unit_id' => $unitPiece->id,
            'multiplier' => 1,
            'purchase_price' => 6.00,
            'mrp_price' => 7.00,
            'is_default_sale' => true,
        ]);
        ProductUnit::create([
            'product_id' => $multiProduct->id,
            'unit_id' => $unitStrip->id,
            'multiplier' => 10,
            'purchase_price' => 60.00,
            'mrp_price' => 70.00,
            'is_default_sale' => false,
        ]);
        ProductUnit::create([
            'product_id' => $multiProduct->id,
            'unit_id' => $unitBox->id,
            'multiplier' => 100,
            'purchase_price' => 600.00,
            'mrp_price' => 700.00,
            'is_default_purchase' => true,
            'is_default_sale' => false,
        ]);

        $this->info(" [x] Product 2 Created (Unit-Converted): {$multiProduct->name}");
        $this->line("     - Base Unit: Piece (1x) => MRP: 7.00 BDT | Purchase: 6.00 BDT");
        $this->line("     - Strip Unit: 10 Pieces (10x) => MRP: 70.00 BDT | Purchase: 60.00 BDT");
        $this->line("     - Box Unit: 100 Pieces (100x) => MRP: 700.00 BDT | Purchase: 600.00 BDT");

        $this->line("\n<fg=yellow;options=bold>STEP 2: PURCHASE & BATCH INBOUND (GRN)</>");

        $batchAceNumber = 'BATCH-ACE-' . rand(100, 999);
        $batchMaxNumber = 'BATCH-MAX-' . rand(100, 999);

        // Inbound Purchase Order
        $purchasePayload = [
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'supplier_invoice_no' => 'CHALAN-9921',
            'invoice_date' => now()->toDateString(),
            'delivery_date' => now()->toDateString(),
            'discount' => 0.00,
            'tax_amount' => 0.00,
            'paid_amount' => 7400.00, // Total: (50 * 28) + (10 * 600) = 1400 + 6000 = 7400
            'notes' => 'Stock Inbound GRN',
            'items' => [
                [
                    'product_id' => $simpleProduct->id,
                    'batch_number' => $batchAceNumber,
                    'expiry_date' => now()->addMonths(18)->toDateString(),
                    'unit_id' => $unitBottle->id,
                    'quantity' => 50, // 50 Bottles
                    'bonus_quantity' => 0,
                    'purchase_price_per_unit' => 28.00,
                    'mrp_per_unit' => 35.00,
                ],
                [
                    'product_id' => $multiProduct->id,
                    'batch_number' => $batchMaxNumber,
                    'expiry_date' => now()->addMonths(24)->toDateString(),
                    'unit_id' => $unitBox->id,
                    'quantity' => 10, // 10 Boxes (= 1,000 base pieces)
                    'bonus_quantity' => 0,
                    'purchase_price_per_unit' => 600.00,
                    'mrp_per_unit' => 700.00,
                ],
            ],
        ];

        $purchase = $grnAction->execute($purchasePayload);
        $this->info(" [x] Purchase Received: {$purchase->purchase_no}");
        $this->line("     - Total Bill: {$purchase->grand_total} BDT | Paid: {$purchase->paid_amount} BDT | Status: {$purchase->payment_status}");
        $this->line("     - Ace Syrup: 50 Bottles (Batch: {$batchAceNumber})");
        $this->line("     - Maxpro: 10 Boxes => 1,000 Atomic Pieces stored in Batch: {$batchMaxNumber}");

        $this->line("\n<fg=yellow;options=bold>STEP 3: POS SALE (OUTBOUND BILLING)</>");

        // Sale 1: Simple Product Sale (Customer buys 3 Bottles)
        $sale1Payload = [
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'doctor_id' => null,
            'items' => [
                [
                    'product_id' => $simpleProduct->id,
                    'unit_id' => $unitBottle->id,
                    'quantity' => 3,
                    'unit_price' => 35.00,
                    'discount' => 0.00,
                ]
            ],
            'sub_total' => 105.00,
            'discount_type' => 'fixed',
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'round_off' => 0.00,
            'grand_total' => 105.00,
            'payments' => [
                ['method' => 'CASH', 'amount' => 105.00, 'ref' => 'CASH-01']
            ],
            'notes' => 'Sale of Ace Syrup (3 Bottles)',
        ];
        $sale1 = $posCheckoutAction->execute($sale1Payload);
        $this->info(" [x] POS Sale 1 Completed (Invoice #{$sale1->invoice_no})");
        $this->line("     - Sold: 3 Bottles of Ace Syrup @ 35.00 BDT = 105.00 BDT");

        // Sale 2: Unit-Converted Product Sale (Customer buys 2 Strips + 5 Pieces = 25 Base Pieces)
        $sale2Payload = [
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'doctor_id' => null,
            'items' => [
                [
                    'product_id' => $multiProduct->id,
                    'unit_id' => $unitStrip->id,
                    'quantity' => 2, // 2 Strips = 20 pieces
                    'unit_price' => 70.00,
                    'discount' => 0.00,
                ],
                [
                    'product_id' => $multiProduct->id,
                    'unit_id' => $unitPiece->id,
                    'quantity' => 5, // 5 Pieces
                    'unit_price' => 7.00,
                    'discount' => 0.00,
                ]
            ],
            'sub_total' => 175.00, // 2*70 + 5*7 = 140 + 35 = 175
            'discount_type' => 'fixed',
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'round_off' => 0.00,
            'grand_total' => 175.00,
            'payments' => [
                ['method' => 'BKASH', 'amount' => 175.00, 'ref' => 'TRX-BKASH-8819']
            ],
            'notes' => 'Sale of Maxpro (2 Strips + 5 Pieces)',
        ];
        $sale2 = $posCheckoutAction->execute($sale2Payload);
        $this->info(" [x] POS Sale 2 Completed (Invoice #{$sale2->invoice_no})");
        $this->line("     - Sold: 2 Strips (20 pieces) + 5 Pieces = 25 Total Base Pieces @ 175.00 BDT");

        $this->line("\n<fg=yellow;options=bold>STEP 4: CUSTOMER SALE RETURN & REFUND WORKFLOW</>");
        
        // Customer returns 1 Strip of Maxpro 20mg from Sale 2 (Unopened, good condition)
        $maxproBatch = Batch::where('product_id', $multiProduct->id)->where('batch_number', $batchMaxNumber)->first();
        $sale2MaxproItem = $sale2->items()->where('product_id', $multiProduct->id)->where('unit_id', $unitStrip->id)->first();

        $returnPayload = [
            'sale_id' => $sale2->id,
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'refund_type' => 'CASH',
            'reason' => 'Customer purchased excess medication (Returned 1 full strip)',
            'items' => [
                [
                    'product_id' => $multiProduct->id,
                    'batch_id' => $maxproBatch->id,
                    'unit_id' => $unitStrip->id,
                    'quantity' => 1, // Return 1 Strip = 10 Pieces
                    'refund_unit_price' => 70.00,
                    'is_damaged' => false, // Salable condition -> Restores to batch!
                ]
            ]
        ];

        $saleReturn = $saleReturnAction->execute($returnPayload);
        $this->info(" [x] Sales Return Processed (Return Slip #{$saleReturn->return_no})");
        $this->line("     - Returned Item: 1 Strip (10 Pieces) of Maxpro 20mg Capsule");
        $this->line("     - Refund Amount: 70.00 BDT (Cash Refund to Customer)");
        $this->line("     - Stock Restoration: +10 Atomic Pieces automatically added back to Batch [{$batchMaxNumber}]");

        $this->line("\n<fg=yellow;options=bold>STEP 5: DAMAGE & BREAKAGE STOCK ADJUSTMENT (LOSS LOGGING)</>");

        // 2 Bottles of Ace Syrup fell from rack and broke (Physical Damage)
        $aceBatch = Batch::where('product_id', $simpleProduct->id)->where('batch_number', $batchAceNumber)->first();

        $adjustmentPayload = [
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'reason' => 'DAMAGE',
            'notes' => 'Shelf breakage during inventory organization (2 Bottles broken)',
            'items' => [
                [
                    'product_id' => $simpleProduct->id,
                    'batch_id' => $aceBatch->id,
                    'unit_id' => $unitBottle->id,
                    'quantity' => 2, // 2 Bottles broken
                    'adjustment_type' => 'DEDUCTION',
                ]
            ]
        ];

        $damageAdjustment = $stockAdjustmentAction->execute($adjustmentPayload);
        $this->info(" [x] Damage Adjustment Logged (Slip #{$damageAdjustment->adjustment_no})");
        $this->line("     - Reason: DAMAGE / BREAKAGE");
        $this->line("     - Deducted: 2 Bottles of Ace Syrup from Batch [{$batchAceNumber}]");
        $this->line("     - Total Financial Loss Recorded: {$damageAdjustment->total_loss_value} BDT (2 x 28.00 BDT purchase cost)");

        $this->line("\n<fg=yellow;options=bold>STEP 6: FINAL INVENTORY AUDIT & RECONCILIATION TABLE</>");

        $simpleProduct->refresh();
        $multiProduct->refresh();
        $aceBatch->refresh();
        $maxproBatch->refresh();

        $multiConversion = $unitConversionService->fromBasePieces($multiProduct, $multiProduct->totalStockPieces(), $unitStrip->id);

        $this->table(
            ['Product Name', 'Purchased', 'Sold', 'Returned', 'Damaged', 'Final Current Stock', 'Stock Composition'],
            [
                [
                    $simpleProduct->name,
                    '50 Bottles',
                    '3 Bottles',
                    '0',
                    '2 Bottles',
                    $simpleProduct->totalStockPieces() . ' Bottles',
                    '45 Bottles (Batch stock: ' . $aceBatch->current_stock . ')'
                ],
                [
                    $multiProduct->name,
                    '10 Boxes (1,000 pcs)',
                    '25 Pieces (2 Str + 5 Pcs)',
                    '+10 Pieces (1 Strip)',
                    '0',
                    $multiProduct->totalStockPieces() . ' Pieces',
                    "{$multiConversion['units']} Strips + {$multiConversion['remaining_pieces']} Pieces (Batch stock: {$maxproBatch->current_stock} pcs)"
                ]
            ]
        );

        $this->line("\n<fg=green;options=bold>[SUCCESS] Full Lifecycle (Inbound -> Sales -> Customer Return -> Damage Adjustment) completed 100% cleanly!</>");
        return Command::SUCCESS;
    }
}
