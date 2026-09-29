<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_code' => 'pcs', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Tablet', 'short_code' => 'tab', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Capsule', 'short_code' => 'cap', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Strip', 'short_code' => 'str', 'conversion_factor' => 10, 'is_base_unit' => false],
            ['name' => 'Box', 'short_code' => 'box', 'conversion_factor' => 100, 'is_base_unit' => false],
            ['name' => 'Bottle', 'short_code' => 'btl', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Sachet', 'short_code' => 'sct', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Vial', 'short_code' => 'vial', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Ampoule', 'short_code' => 'amp', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Tube', 'short_code' => 'tube', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Jar', 'short_code' => 'jar', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Drops', 'short_code' => 'drp', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Injection', 'short_code' => 'inj', 'conversion_factor' => 1, 'is_base_unit' => true],
            ['name' => 'Carton', 'short_code' => 'ctn', 'conversion_factor' => 1000, 'is_base_unit' => false],
            ['name' => 'Pack', 'short_code' => 'pck', 'conversion_factor' => 1, 'is_base_unit' => false],
        ];

        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u['name']], $u);
        }
    }
}
