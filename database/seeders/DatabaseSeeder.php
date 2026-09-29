<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Doctor;
use App\Models\ExpenseCategory;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Branch
        $branch = Branch::create([
            'name' => 'Dhaka Central Branch',
            'code' => 'DHK-01',
            'address' => 'Road #11, Banani, Dhaka - 1213',
            'phone' => '01711-000111',
            'email' => 'branch.dhaka@pharmacy.com',
            'invoice_prefix' => 'INV-DHK-',
            'is_active' => true,
        ]);

        // 2. Admin User
        $user = User::create([
            'branch_id' => $branch->id,
            'name' => 'Jarvis Pharmacist',
            'email' => 'admin@pharmacy.com',
            'password' => Hash::make('password'),
            'role' => 'Super Admin',
            'phone' => '01811-999888',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 3. Units
        $unitPiece = Unit::create(['name' => 'Piece', 'short_code' => 'pcs', 'conversion_factor' => 1, 'is_base_unit' => true]);
        $unitStrip = Unit::create(['name' => 'Strip', 'short_code' => 'str', 'conversion_factor' => 10, 'is_base_unit' => false]);
        $unitBox = Unit::create(['name' => 'Box', 'short_code' => 'bx', 'conversion_factor' => 100, 'is_base_unit' => false]);
        $unitCarton = Unit::create(['name' => 'Carton', 'short_code' => 'ctn', 'conversion_factor' => 1000, 'is_base_unit' => false]);
        $unitBottle = Unit::create(['name' => 'Bottle', 'short_code' => 'btl', 'conversion_factor' => 1, 'is_base_unit' => false]);

        // 4. Categories
        $catAnalgesic = Category::create(['name' => 'Analgesics & Antipyretics', 'slug' => 'analgesics-antipyretics', 'description' => 'Pain relief and fever reduction']);
        $catAntiulcer = Category::create(['name' => 'Gastric & Anti-ulcerants', 'slug' => 'gastric-anti-ulcerants', 'description' => 'PPI and antacid medications']);
        $catAntibiotic = Category::create(['name' => 'Antibiotics', 'slug' => 'antibiotics', 'description' => 'Bacterial infection treatments']);
        $catAntihistamine = Category::create(['name' => 'Antihistamines', 'slug' => 'antihistamines', 'description' => 'Allergy and cold relief']);

        // 5. Manufacturers
        $mfgSquare = Manufacturer::create(['name' => 'Square Pharmaceuticals Ltd.', 'contact_person' => 'Md. Hasan', 'phone' => '01711-222333', 'address' => 'Dhaka']);
        $mfgBeximco = Manufacturer::create(['name' => 'Beximco Pharmaceuticals Ltd.', 'contact_person' => 'Ahmed Kabir', 'phone' => '01711-444555', 'address' => 'Tongil, Gazipur']);
        $mfgIncepta = Manufacturer::create(['name' => 'Incepta Pharmaceuticals', 'contact_person' => 'Tanvir Ahmed', 'phone' => '01711-666777', 'address' => 'Savar, Dhaka']);

        // 6. Generics
        $genPara = Generic::create(['name' => 'Paracetamol 500mg', 'indication' => 'Fever, headache, pain', 'side_effects' => 'Rare nausea', 'precautions' => 'Liver impairment']);
        $genOme = Generic::create(['name' => 'Omeprazole 20mg', 'indication' => 'Gastric acidity, peptic ulcer', 'side_effects' => 'Headache, diarrhoea', 'precautions' => 'Pregnancy category C']);
        $genAzith = Generic::create(['name' => 'Azithromycin 500mg', 'indication' => 'Respiratory and skin infections', 'side_effects' => 'Abdominal cramp', 'precautions' => 'Arrhythmia risk']);
        $genFexo = Generic::create(['name' => 'Fexofenadine 120mg', 'indication' => 'Seasonal allergic rhinitis', 'side_effects' => 'Drowsiness rare', 'precautions' => 'Renal adjustment']);

        // 7. Doctors
        $doc1 = Doctor::create(['name' => 'Prof. Dr. Rafiqul Islam', 'degrees' => 'MBBS, FCPS (Medicine)', 'bmdc_reg_no' => 'A-18492', 'hospital_name' => 'Square Hospital', 'phone' => '01700-112233']);
        $doc2 = Doctor::create(['name' => 'Dr. Nusrat Jahan', 'degrees' => 'MBBS, MD (Chest)', 'bmdc_reg_no' => 'A-29401', 'hospital_name' => 'Dhaka Medical College', 'phone' => '01700-445566']);

        // 8. Customers & Suppliers
        $cust1 = Customer::create(['branch_id' => $branch->id, 'name' => 'Tanvir Hossain', 'phone' => '01712-345678', 'email' => 'tanvir@gmail.com', 'address' => 'Banani, Dhaka', 'opening_balance' => 0, 'current_due' => 450.00]);
        $cust2 = Customer::create(['branch_id' => $branch->id, 'name' => 'Sabrina Akter', 'phone' => '01912-876543', 'email' => 'sabrina@gmail.com', 'address' => 'Gulshan 2, Dhaka', 'opening_balance' => 0, 'current_due' => 0.00]);

        $supp1 = Supplier::create(['name' => 'Rahim Distributor', 'company_name' => 'Square Pharma Agency', 'phone' => '01811-123123', 'address' => 'Mohakhali, Dhaka', 'current_due' => 12500.00]);
        $supp2 = Supplier::create(['name' => 'Karim Trade Agency', 'company_name' => 'Beximco Agency', 'phone' => '01811-456456', 'address' => 'Tejgaon, Dhaka', 'current_due' => 8400.00]);

        // 9. Products & Multi-Unit Packages
        // Product 1: Napa Extra (Square)
        $p1 = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Napa Extra 500mg/65mg Tablet',
            'generic_id' => $genPara->id,
            'category_id' => $catAnalgesic->id,
            'manufacturer_id' => $mfgSquare->id,
            'base_unit_id' => $unitPiece->id,
            'sku' => 'MED-NAPA-EX',
            'barcode' => '8941100101',
            'rack_location' => 'Shelf A-01',
            'min_stock_alert' => 50,
            'tax_rate' => 0.00,
            'status' => 'active',
        ]);
        ProductUnit::create(['product_id' => $p1->id, 'unit_id' => $unitPiece->id, 'multiplier' => 1, 'purchase_price' => 2.10, 'mrp_price' => 2.50, 'is_default_sale' => true]);
        ProductUnit::create(['product_id' => $p1->id, 'unit_id' => $unitStrip->id, 'multiplier' => 10, 'purchase_price' => 21.00, 'mrp_price' => 25.00, 'is_default_sale' => false]);
        ProductUnit::create(['product_id' => $p1->id, 'unit_id' => $unitBox->id, 'multiplier' => 200, 'purchase_price' => 420.00, 'mrp_price' => 500.00, 'is_default_sale' => false]);

        // Batch for Napa Extra
        Batch::create([
            'branch_id' => $branch->id,
            'product_id' => $p1->id,
            'batch_number' => 'B-NAPA-2026A',
            'base_unit_id' => $unitPiece->id,
            'initial_stock' => 1000,
            'current_stock' => 850,
            'purchase_rate' => 2.1000,
            'mrp_rate' => 2.5000,
            'expiry_date' => now()->addMonths(14)->toDateString(),
        ]);

        // Product 2: Seclo 20mg (Square)
        $p2 = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Seclo 20mg Capsule',
            'generic_id' => $genOme->id,
            'category_id' => $catAntiulcer->id,
            'manufacturer_id' => $mfgSquare->id,
            'base_unit_id' => $unitPiece->id,
            'sku' => 'MED-SECLO-20',
            'barcode' => '8941100202',
            'rack_location' => 'Shelf B-02',
            'min_stock_alert' => 40,
            'status' => 'active',
        ]);
        ProductUnit::create(['product_id' => $p2->id, 'unit_id' => $unitPiece->id, 'multiplier' => 1, 'purchase_price' => 5.20, 'mrp_price' => 6.00, 'is_default_sale' => true]);
        ProductUnit::create(['product_id' => $p2->id, 'unit_id' => $unitStrip->id, 'multiplier' => 10, 'purchase_price' => 52.00, 'mrp_price' => 60.00, 'is_default_sale' => false]);

        Batch::create([
            'branch_id' => $branch->id,
            'product_id' => $p2->id,
            'batch_number' => 'B-SECLO-991',
            'base_unit_id' => $unitPiece->id,
            'initial_stock' => 500,
            'current_stock' => 420,
            'purchase_rate' => 5.2000,
            'mrp_rate' => 6.0000,
            'expiry_date' => now()->addMonths(20)->toDateString(),
        ]);

        // Product 3: Azithral 500mg (Beximco)
        $p3 = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Azithral 500mg Tablet',
            'generic_id' => $genAzith->id,
            'category_id' => $catAntibiotic->id,
            'manufacturer_id' => $mfgBeximco->id,
            'base_unit_id' => $unitPiece->id,
            'sku' => 'MED-AZITH-500',
            'barcode' => '8941100303',
            'rack_location' => 'Shelf C-01',
            'min_stock_alert' => 30,
            'requires_prescription' => true,
            'status' => 'active',
        ]);
        ProductUnit::create(['product_id' => $p3->id, 'unit_id' => $unitPiece->id, 'multiplier' => 1, 'purchase_price' => 30.00, 'mrp_price' => 35.00, 'is_default_sale' => true]);
        ProductUnit::create(['product_id' => $p3->id, 'unit_id' => $unitStrip->id, 'multiplier' => 6, 'purchase_price' => 180.00, 'mrp_price' => 210.00, 'is_default_sale' => false]);

        Batch::create([
            'branch_id' => $branch->id,
            'product_id' => $p3->id,
            'batch_number' => 'B-AZITH-772',
            'base_unit_id' => $unitPiece->id,
            'initial_stock' => 300,
            'current_stock' => 210,
            'purchase_rate' => 30.0000,
            'mrp_rate' => 35.0000,
            'expiry_date' => now()->addMonths(8)->toDateString(),
        ]);

        // Product 4: Fexo 120mg (Square)
        $p4 = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Fexo 120mg Tablet',
            'generic_id' => $genFexo->id,
            'category_id' => $catAntihistamine->id,
            'manufacturer_id' => $mfgSquare->id,
            'base_unit_id' => $unitPiece->id,
            'sku' => 'MED-FEXO-120',
            'barcode' => '8941100404',
            'rack_location' => 'Shelf D-03',
            'min_stock_alert' => 25,
            'status' => 'active',
        ]);
        ProductUnit::create(['product_id' => $p4->id, 'unit_id' => $unitPiece->id, 'multiplier' => 1, 'purchase_price' => 8.50, 'mrp_price' => 10.00, 'is_default_sale' => true]);
        ProductUnit::create(['product_id' => $p4->id, 'unit_id' => $unitStrip->id, 'multiplier' => 10, 'purchase_price' => 85.00, 'mrp_price' => 100.00, 'is_default_sale' => false]);

        Batch::create([
            'branch_id' => $branch->id,
            'product_id' => $p4->id,
            'batch_number' => 'B-FEXO-334',
            'base_unit_id' => $unitPiece->id,
            'initial_stock' => 400,
            'current_stock' => 350,
            'purchase_rate' => 8.5000,
            'mrp_rate' => 10.0000,
            'expiry_date' => now()->addMonths(16)->toDateString(),
        ]);

        // 10. Expense Categories
        ExpenseCategory::create(['branch_id' => $branch->id, 'name' => 'Electricity & Utility', 'description' => 'Monthly electricity bill']);
        ExpenseCategory::create(['branch_id' => $branch->id, 'name' => 'Staff Allowance & Lunch', 'description' => 'Daily staff food & refreshment']);
        ExpenseCategory::create(['branch_id' => $branch->id, 'name' => 'Packaging & Bags', 'description' => 'Plastic & paper carry bags']);
    }
}
