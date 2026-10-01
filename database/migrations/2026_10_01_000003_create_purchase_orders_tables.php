<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('po_number', 50)->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->date('order_date')->index();
                $table->date('expected_delivery_date')->nullable();
                $table->string('status', 30)->default('ORDERED')->index(); // DRAFT, ORDERED, PARTIALLY_RECEIVED, RECEIVED, CANCELLED
                $table->integer('total_items')->default(0);
                $table->decimal('total_ordered_qty', 12, 2)->default(0);
                $table->decimal('total_received_qty', 12, 2)->default(0);
                $table->decimal('sub_total', 14, 2)->default(0);
                $table->decimal('discount', 14, 2)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('grand_total', 14, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->decimal('quantity_ordered', 12, 2)->default(1);
                $table->decimal('quantity_received', 12, 2)->default(0);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('mrp_per_unit', 12, 2)->default(0);
                $table->decimal('total_cost', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('purchases') && ! Schema::hasColumn('purchases', 'purchase_order_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->foreignId('purchase_order_id')->nullable()->after('supplier_id')->constrained('purchase_orders')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'purchase_order_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropConstrainedForeignId('purchase_order_id');
            });
        }

        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
