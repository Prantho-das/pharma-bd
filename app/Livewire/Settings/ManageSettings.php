<?php

namespace App\Livewire\Settings;

use App\Models\Branch;
use App\Models\Setting;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Business & POS Settings - PMS')]
class ManageSettings extends Component
{
    use WithFileUploads;

    // General & Business Info
    public string $businessCategory = 'Retail Pharmacy';

    public string $companyName = 'Acnoo Pharmacy';

    public bool $showCompanyName = true;

    public string $phone = '01712022529';

    public bool $showPhone = true;

    public string $email = 'acnoo@gmail.com';

    public bool $showEmail = true;

    public string $address = 'Dhaka, Bangladesh';

    public bool $showAddress = true;

    // POS & Calculation Settings
    public string $saleRoundingOption = 'None';

    public string $taxGstTitle = 'GST';

    public string $taxGstNumber = '1234';

    public bool $showTaxGstNumber = true;

    public string $productProfitOption = 'Markup (Profit based on purchase price) • Default';

    public string $postSaleMessage = 'Thank you for shopping with us! Get well soon.';

    public bool $showPostSaleMessage = true;

    public string $invoiceNoteLabel = 'Terms & Conditions';

    public string $invoiceNote = 'Medicines once sold cannot be returned without original receipt.';

    public bool $showInvoiceNote = true;

    // Logos & Scanner QR
    public $a4InvoiceLogo;

    public ?string $existingA4Logo = null;

    public $thermalInvoiceLogo;

    public ?string $existingThermalLogo = null;

    public $invoiceScannerLogo;

    public ?string $existingScannerLogo = null;

    public bool $showScannerLogo = true;

    public function mount()
    {
        $this->businessCategory = Setting::get('business_category', 'Retail Pharmacy');
        $this->companyName = Setting::get('company_name', Branch::first()?->name ?? 'Acnoo Pharmacy');
        $this->showCompanyName = (bool) Setting::get('show_company_name', true);

        $this->phone = Setting::get('phone', Branch::first()?->phone ?? '01712022529');
        $this->showPhone = (bool) Setting::get('show_phone', true);

        $this->email = Setting::get('email', Branch::first()?->email ?? 'acnoo@gmail.com');
        $this->showEmail = (bool) Setting::get('show_email', true);

        $this->address = Setting::get('address', Branch::first()?->address ?? 'Dhaka, Bangladesh');
        $this->showAddress = (bool) Setting::get('show_address', true);

        $this->saleRoundingOption = Setting::get('sale_rounding_option', 'None');
        $this->taxGstTitle = Setting::get('tax_gst_title', 'GST');
        $this->taxGstNumber = Setting::get('tax_gst_number', '1234');
        $this->showTaxGstNumber = (bool) Setting::get('show_tax_gst_number', true);

        $this->productProfitOption = Setting::get('product_profit_option', 'Markup (Profit based on purchase price) • Default');
        $this->postSaleMessage = Setting::get('post_sale_message', 'Thank you for shopping with us! Get well soon.');
        $this->showPostSaleMessage = (bool) Setting::get('show_post_sale_message', true);

        $this->invoiceNoteLabel = Setting::get('invoice_note_label', 'Terms & Conditions');
        $this->invoiceNote = Setting::get('invoice_note', 'Medicines once sold cannot be returned without original receipt.');
        $this->showInvoiceNote = (bool) Setting::get('show_invoice_note', true);

        $this->existingA4Logo = Setting::get('a4_invoice_logo');
        $this->existingThermalLogo = Setting::get('thermal_invoice_logo');
        $this->existingScannerLogo = Setting::get('invoice_scanner_logo');
        $this->showScannerLogo = (bool) Setting::get('show_scanner_logo', true);
    }

    public function saveSettings()
    {
        // Handle image uploads
        if ($this->a4InvoiceLogo) {
            $path = $this->a4InvoiceLogo->store('logos', 'public');
            Setting::set('a4_invoice_logo', $path);
            $this->existingA4Logo = $path;
        }

        if ($this->thermalInvoiceLogo) {
            $path = $this->thermalInvoiceLogo->store('logos', 'public');
            Setting::set('thermal_invoice_logo', $path);
            $this->existingThermalLogo = $path;
        }

        if ($this->invoiceScannerLogo) {
            $path = $this->invoiceScannerLogo->store('logos', 'public');
            Setting::set('invoice_scanner_logo', $path);
            $this->existingScannerLogo = $path;
        }

        Setting::set('business_category', $this->businessCategory);
        Setting::set('company_name', $this->companyName);
        Setting::set('show_company_name', $this->showCompanyName ? '1' : '0');

        Setting::set('phone', $this->phone);
        Setting::set('show_phone', $this->showPhone ? '1' : '0');

        Setting::set('email', $this->email);
        Setting::set('show_email', $this->showEmail ? '1' : '0');

        Setting::set('address', $this->address);
        Setting::set('show_address', $this->showAddress ? '1' : '0');

        Setting::set('sale_rounding_option', $this->saleRoundingOption);
        Setting::set('tax_gst_title', $this->taxGstTitle);
        Setting::set('tax_gst_number', $this->taxGstNumber);
        Setting::set('show_tax_gst_number', $this->showTaxGstNumber ? '1' : '0');

        Setting::set('product_profit_option', $this->productProfitOption);
        Setting::set('post_sale_message', $this->postSaleMessage);
        Setting::set('show_post_sale_message', $this->showPostSaleMessage ? '1' : '0');

        Setting::set('invoice_note_label', $this->invoiceNoteLabel);
        Setting::set('invoice_note', $this->invoiceNote);
        Setting::set('show_invoice_note', $this->showInvoiceNote ? '1' : '0');

        Setting::set('show_scanner_logo', $this->showScannerLogo ? '1' : '0');

        // Sync with primary branch
        $branch = Branch::first();
        if ($branch) {
            $branch->update([
                'name' => $this->companyName,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
            ]);
        }

        session()->flash('success', 'Settings updated successfully!');
    }

    public function render()
    {
        return view('livewire.settings.manage-settings');
    }
}
