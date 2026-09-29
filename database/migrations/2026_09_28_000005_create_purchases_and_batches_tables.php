<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('purchase_no')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('supplier_invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->decimal('sub_total', 12, 2)->default(0.00);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->enum('payment_status', ['PAID', 'PARTIAL', 'DUE'])->default('DUE');
            $table->enum('status', ['ORDERED', 'RECEIVED', 'CANCELLED'])->default('RECEIVED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->integer('bonus_quantity')->default(0);
            $table->integer('unit_multiplier')->default(1);
            $table->integer('total_pieces')->default(1);
            $table->decimal('purchase_price_per_unit', 12, 4)->default(0.0000);
            $table->decimal('mrp_per_unit', 12, 4)->default(0.0000);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_number')->index();
            $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->integer('initial_stock')->default(0); // in base atomic pieces
            $table->integer('current_stock')->default(0); // in base atomic pieces
            $table->decimal('purchase_rate', 12, 4)->default(0.0000); // per base piece
            $table->decimal('mrp_rate', 12, 4)->default(0.0000); // per base piece
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->index();
            $table->integer('alert_days_before_expiry')->default(90);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
