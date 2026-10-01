<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add has_batch_tracking to products if not exists
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'has_batch_tracking')) {
                $table->boolean('has_batch_tracking')->default(true)->after('stock_type');
            }
        });

        // 2. Create purchase_payments table for split / installment payments
        if (! Schema::hasTable('purchase_payments')) {
            Schema::create('purchase_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->string('payment_method')->default('CASH'); // CASH, BANK_TRANSFER, CHEQUE, BKASH, NAGAD, ROCKET
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->date('payment_date')->index();
                $table->string('transaction_reference')->nullable();
                $table->string('cheque_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'has_batch_tracking')) {
                $table->dropColumn('has_batch_tracking');
            }
        });
    }
};
