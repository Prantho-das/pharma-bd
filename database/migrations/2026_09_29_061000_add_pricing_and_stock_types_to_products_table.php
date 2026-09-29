<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'tax_type')) {
                $table->string('tax_type')->default('Exclusive')->after('tax_rate');
            }
            if (! Schema::hasColumn('products', 'stock_type')) {
                $table->string('stock_type')->default('Single')->after('tax_type'); // Single or Batch
            }
            if (! Schema::hasColumn('products', 'wholesale_price')) {
                $table->decimal('wholesale_price', 12, 4)->default(0.00)->after('stock_type');
            }
            if (! Schema::hasColumn('products', 'dealer_price')) {
                $table->decimal('dealer_price', 12, 4)->default(0.00)->after('wholesale_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['tax_type', 'stock_type', 'wholesale_price', 'dealer_price']);
        });
    }
};
