<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->enum('transaction_type', ['SALE', 'RETURN', 'DUE_COLLECTION', 'OPENING_BALANCE', 'ADJUSTMENT']);
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->decimal('debit', 12, 2)->default(0.00);
            $table->decimal('credit', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->text('note')->nullable();
            $table->dateTime('transaction_date')->index();
            $table->timestamps();
        });

        Schema::create('supplier_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->enum('transaction_type', ['PURCHASE', 'RETURN', 'PAYMENT', 'OPENING_BALANCE', 'ADJUSTMENT']);
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->decimal('debit', 12, 2)->default(0.00);
            $table->decimal('credit', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->text('note')->nullable();
            $table->dateTime('transaction_date')->index();
            $table->timestamps();
        });

        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_cash', 12, 2)->default(0.00);
            $table->decimal('closing_cash', 12, 2)->nullable();
            $table->decimal('total_cash_sales', 12, 2)->default(0.00);
            $table->decimal('total_card_sales', 12, 2)->default(0.00);
            $table->decimal('total_mfs_sales', 12, 2)->default(0.00);
            $table->decimal('total_cash_expenses', 12, 2)->default(0.00);
            $table->decimal('expected_closing_cash', 12, 2)->default(0.00);
            $table->decimal('difference', 12, 2)->default(0.00);
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->date('date')->index();
            $table->string('payment_method')->default('CASH'); // CASH, BANK, MFS
            $table->string('receipt_image_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('supplier_ledgers');
        Schema::dropIfExists('customer_ledgers');
    }
};
