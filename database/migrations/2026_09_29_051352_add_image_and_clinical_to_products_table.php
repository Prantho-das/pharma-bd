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
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('name');
            $table->string('strength')->nullable()->after('name');
            $table->string('dosage_form')->nullable()->after('strength');
            $table->foreignId('medicine_type_id')->nullable()->constrained('medicine_types')->nullOnDelete()->after('category_id');
            $table->foreignId('box_size_id')->nullable()->constrained('box_sizes')->nullOnDelete()->after('base_unit_id');
            $table->boolean('is_cold_chain')->default(false)->after('requires_prescription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['medicine_type_id']);
            $table->dropForeign(['box_size_id']);
            $table->dropColumn(['image_path', 'strength', 'dosage_form', 'medicine_type_id', 'box_size_id', 'is_cold_chain']);
        });
    }
};
