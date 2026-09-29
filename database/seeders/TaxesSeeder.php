<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxesSeeder extends Seeder
{
    public function run(): void
    {
        $taxes = [
            ['name' => 'Exempt / Zero VAT (0%)', 'rate' => 0.00, 'is_active' => true],
            ['name' => 'Medicine MRP VAT (2.4%)', 'rate' => 2.40, 'is_active' => true],
            ['name' => 'Standard Medicine VAT (5%)', 'rate' => 5.00, 'is_active' => true],
            ['name' => 'Medical Devices & Equipment (7.5%)', 'rate' => 7.50, 'is_active' => true],
            ['name' => 'Cosmetics & Consumer Healthcare (15%)', 'rate' => 15.00, 'is_active' => true],
        ];

        foreach ($taxes as $t) {
            Tax::firstOrCreate(['name' => $t['name']], $t);
        }
    }
}
