<?php

namespace App\Livewire\Products;

use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Services\MedicineDirectoryService;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('National Medicine Cloud Directory & Full Sync - PMS')]
class MedicineDirectoryStudio extends Component
{
    public string $activeTab = 'search'; // 'search' or 'full_sync'

    // Search Mode
    public string $search = 'Napa';
    public array $searchResults = [];
    public array $selectedMedicines = [];
    public bool $selectAll = false;
    public ?string $feedbackMessage = null;

    // Quick Import Modal State
    public bool $showImportModal = false;
    public array $editingItem = [];
    public float $customBuyPrice = 0.00;
    public float $customMrpPrice = 0.00;

    // Full Sync / Crawler State
    public string $selectedAlpha = 'a';
    public int $crawlPages = 3;
    public bool $isCrawling = false;
    public array $crawlLogs = [];
    public int $crawledCount = 0;

    public function mount(MedicineDirectoryService $service)
    {
        $this->performSearch($service);
    }

    public function updatedSearch()
    {
        $service = app(MedicineDirectoryService::class);
        $this->performSearch($service);
    }

    public function performSearch(MedicineDirectoryService $service)
    {
        $this->selectedMedicines = [];
        $this->selectAll = false;
        $this->searchResults = $service->search($this->search ?: 'Napa');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedMedicines = array_keys($this->searchResults);
        } else {
            $this->selectedMedicines = [];
        }
    }

    public function openImportModal(int $index)
    {
        if (isset($this->searchResults[$index])) {
            $this->editingItem = $this->searchResults[$index];
            $this->customMrpPrice = (float) ($this->editingItem['unit_price'] ?? 2.50);
            $this->customBuyPrice = round($this->customMrpPrice * 0.85, 2);
            $this->showImportModal = true;
        }
    }

    public function confirmSingleImport(MedicineDirectoryService $service)
    {
        if (! empty($this->editingItem)) {
            $product = $service->importMedicine($this->editingItem, $this->customBuyPrice, $this->customMrpPrice);
            $this->showImportModal = false;
            $this->feedbackMessage = "ঔষধ [{$product->name}] সফলভাবে আপনার ক্যাটালগে যুক্ত হয়েছে!";
            $this->performSearch($service);
        }
    }

    public function instantImport(int $index, MedicineDirectoryService $service)
    {
        if (isset($this->searchResults[$index])) {
            $item = $this->searchResults[$index];
            $product = $service->importMedicine($item);
            $this->feedbackMessage = "ঔষধ [{$product->name}] ১-ক্লিকে ক্যাটালগে যুক্ত হয়েছে!";
            $this->performSearch($service);
        }
    }

    public function importSelected(MedicineDirectoryService $service)
    {
        if (empty($this->selectedMedicines)) {
            $this->feedbackMessage = 'দয়া করে কমপক্ষে ১টি ওষুধ সিলেক্ট করুন।';
            return;
        }

        $count = 0;
        foreach ($this->selectedMedicines as $idx) {
            if (isset($this->searchResults[$idx])) {
                $service->importMedicine($this->searchResults[$idx]);
                $count++;
            }
        }

        $this->selectedMedicines = [];
        $this->selectAll = false;
        $this->feedbackMessage = "মোট {$count} টি ঔষধ সফলভাবে ক্যাটালগে যুক্ত হয়েছে!";
        $this->performSearch($service);
    }

    /**
     * Crawl specific letter from Medex
     */
    public function crawlAlpha(string $alpha, MedicineDirectoryService $service)
    {
        $this->selectedAlpha = strtolower($alpha);
        $this->crawlLogs = [];
        $this->crawledCount = 0;

        $this->crawlLogs[] = "🌐 Starting Medex Cloud fetch for letter '{$this->selectedAlpha}' (Max {$this->crawlPages} pages)...";

        for ($page = 1; $page <= $this->crawlPages; $page++) {
            $items = $service->fetchMedexBrandsPage($this->selectedAlpha, $page);
            if (empty($items)) {
                $this->crawlLogs[] = "⚠️ Page {$page}: No items returned or rate limit hit. Finished letter '{$this->selectedAlpha}'.";
                break;
            }

            $importedThisPage = 0;
            foreach ($items as $item) {
                $service->importMedicine($item);
                $importedThisPage++;
                $this->crawledCount++;
            }

            $this->crawlLogs[] = "✓ Page {$page}: Imported {$importedThisPage} brands into database.";
        }

        $this->crawlLogs[] = "🎉 Completed letter '{$this->selectedAlpha}'. Total {$this->crawledCount} medicines imported!";
        $this->feedbackMessage = "Medex থেকে '{$this->selectedAlpha}' অক্ষরের মোট {$this->crawledCount} টি ঔষধ সিস্টেমে ইমপোর্ট হয়েছে!";
    }

    /**
     * 1-Click Sync of National BD Master Repository
     */
    public function syncNationalMaster(MedicineDirectoryService $service)
    {
        $items = $service->getNationalMedicineDataset();
        $count = 0;
        foreach ($items as $item) {
            $service->importMedicine($item);
            $count++;
        }

        $this->feedbackMessage = "⚡ বাংলাদেশ ন্যাশনাল ড্রাগ ডিরেক্টরি থেকে {$count} টি শীর্ষ ঔষধ সফলভাবে সিঙ্ক ও ইমপোর্ট হয়েছে!";
        $this->performSearch($service);
    }

    public function render()
    {
        return view('livewire.products.medicine-directory-studio', [
            'totalProducts' => Product::count(),
            'totalGenerics' => Generic::count(),
            'totalManufacturers' => Manufacturer::count(),
        ]);
    }
}
