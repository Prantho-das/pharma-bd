<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Suppliers Management - PMS')]
class SupplierManager extends Component
{
    use WithFileUploads, WithPagination;

    public string $tab = 'all'; // 'all', 'create', 'bulk'

    public string $search = '';

    public int $perPage = 15;

    public ?int $editingSupplierId = null;

    public string $name = '';

    public string $companyName = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public float $openingBalance = 0.00;

    public $csvFile;

    public function saveSupplier()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        Supplier::updateOrCreate(
            ['id' => $this->editingSupplierId],
            [
                'name' => $this->name,
                'company_name' => $this->companyName,
                'phone' => $this->phone ?: ('01'.rand(100000000, 999999999)),
                'email' => $this->email,
                'address' => $this->address,
                'opening_balance' => $this->openingBalance,
                'current_due' => $this->openingBalance,
            ]
        );

        session()->flash('success', 'Supplier saved successfully!');
        $this->reset(['editingSupplierId', 'name', 'companyName', 'phone', 'email', 'address', 'openingBalance']);
        $this->tab = 'all';
    }

    public function editSupplier(int $id)
    {
        $s = Supplier::findOrFail($id);
        $this->editingSupplierId = $s->id;
        $this->name = $s->name;
        $this->companyName = $s->company_name ?? '';
        $this->phone = $s->phone ?? '';
        $this->email = $s->email ?? '';
        $this->address = $s->address ?? '';
        $this->openingBalance = (float) $s->opening_balance;
        $this->tab = 'create';
    }

    public function processBulkUpload()
    {
        $this->validate(['csvFile' => 'required|mimes:csv,txt|max:5120']);

        $file = fopen($this->csvFile->getRealPath(), 'r');
        fgetcsv($file);
        $count = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (! empty($row[0])) {
                Supplier::create([
                    'name' => trim($row[0]),
                    'company_name' => trim($row[1] ?? ''),
                    'phone' => trim($row[2] ?? ('01'.rand(100000000, 999999999))),
                    'email' => trim($row[3] ?? ''),
                    'address' => trim($row[4] ?? ''),
                    'opening_balance' => (float) ($row[5] ?? 0),
                    'current_due' => (float) ($row[5] ?? 0),
                ]);
                $count++;
            }
        }
        fclose($file);

        session()->flash('success', "Imported {$count} suppliers successfully!");
        $this->tab = 'all';
    }

    public function render()
    {
        $suppliers = Supplier::when($this->search, function ($q) {
            $term = '%'.trim($this->search).'%';
            $q->where('name', 'like', $term)
                ->orWhere('company_name', 'like', $term)
                ->orWhere('phone', 'like', $term);
        })
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.suppliers.supplier-manager', [
            'suppliers' => $suppliers,
        ]);
    }
}
