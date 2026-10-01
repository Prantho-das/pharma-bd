<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createIndexIfNotExists('products', 'products_name_index', 'CREATE INDEX IF NOT EXISTS products_name_index ON products (name)');
        $this->createIndexIfNotExists('products', 'products_status_name_index', 'CREATE INDEX IF NOT EXISTS products_status_name_index ON products (status, name)');
        $this->createIndexIfNotExists('batches', 'batches_product_expiry_stock_index', 'CREATE INDEX IF NOT EXISTS batches_product_expiry_stock_index ON batches (product_id, expiry_date, current_stock)');
        $this->createIndexIfNotExists('batches', 'batches_stock_expiry_index', 'CREATE INDEX IF NOT EXISTS batches_stock_expiry_index ON batches (current_stock, expiry_date)');
        $this->createIndexIfNotExists('customers', 'customers_phone_index', 'CREATE INDEX IF NOT EXISTS customers_phone_index ON customers (phone)');
        $this->createIndexIfNotExists('generics', 'generics_name_index', 'CREATE INDEX IF NOT EXISTS generics_name_index ON generics (name)');
    }

    private function createIndexIfNotExists(string $table, string $indexName, string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            // Index might already exist or driver mismatch
        }
    }

    public function down(): void
    {
        $indexes = [
            'products_name_index',
            'products_status_name_index',
            'batches_product_expiry_stock_index',
            'batches_stock_expiry_index',
            'customers_phone_index',
            'generics_name_index',
        ];

        foreach ($indexes as $index) {
            try {
                DB::statement("DROP INDEX IF EXISTS {$index}");
            } catch (\Throwable $e) {
            }
        }
    }
};
