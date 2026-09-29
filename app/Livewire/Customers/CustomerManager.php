<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Customers Management - PMS')]
class CustomerManager extends Component
{
    use WithFileUploads, WithPagination;

    public string $tab = 'all'; // 'all', 'create', 'bulk'

    public string $search = '';

    public int $perPage = 15;

    // Create Form
    public ?int $editingCustomerId = null;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public float $openingBalance = 0.00;

    public float $creditLimit = 0.00;

    public $csvFile;

    public function mount()
    {
        $this->tab = request()->query('action', request()->query('type') === 'Customer' && request()->query('bulk') ? 'bulk' : 'all');
    }

    public function saveCustomer()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        Customer::updateOrCreate(
            ['id' => $this->editingCustomerId],
            [
                'name' => $this->name,
                'phone' => $this->phone ?: ('01'.rand(100000000, 999999999)),
                'email' => $this->email,
                'address' => $this->address,
                'opening_balance' => $this->openingBalance,
                'current_due' => $this->openingBalance,
                'credit_limit' => $this->creditLimit,
            ]
        );

        session()->flash('success', 'Customer saved successfully!');
        $this->reset(['editingCustomerId', 'name', 'phone', 'email', 'address', 'openingBalance', 'creditLimit']);
        $this->tab = 'all';
    }

    public function editCustomer(int $id)
    {
        $c = Customer::findOrFail($id);
        $this->editingCustomerId = $c->id;
        $this->name = $c->name;
        $this->phone = $c->phone ?? '';
        $this->email = $c->email ?? '';
        $this->address = $c->address ?? '';
        $this->openingBalance = (float) $c->opening_balance;
        $this->creditLimit = (float) $c->credit_limit;
        $this->tab = 'create';
    }

    public function processBulkUpload()
    {
        $this->validate(['csvFile' => 'required|mimes:csv,txt|max:5120']);

        $file = fopen($this->csvFile->getRealPath(), 'r');
        fgetcsv($file); // skip header
        $count = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (! empty($row[0])) {
                Customer::create([
                    'name' => trim($row[0]),
                    'phone' => trim($row[1] ?? ('01'.rand(100000000, 999999999))),
                    'email' => trim($row[2] ?? ''),
                    'address' => trim($row[3] ?? ''),
                    'opening_balance' => (float) ($row[4] ?? 0),
                    'current_due' => (float) ($row[4] ?? 0),
                ]);
                $count++;
            }
        }
        fclose($file);

        session()->flash('success', "Imported {$count} customers successfully!");
        $this->tab = 'all';
    }

    public function render()
    {
        $customers = Customer::when($this->search, function ($q) {
            $term = '%'.trim($this->search).'%';
            $q->where('name', 'like', $term)
                ->orWhere('phone', 'like', $term)
                ->orWhere('email', 'like', $term);
        })
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.customers.customer-manager', [
            'customers' => $customers,
        ]);
    }
}
