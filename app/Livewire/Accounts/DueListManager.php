<?php

namespace App\Livewire\Accounts;

use App\Models\Customer;
use App\Models\Sale;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Customer Due List - PMS')]
class DueListManager extends Component
{
    use WithPagination;

    public string $view = 'total'; // 'total' (Customer Ledger Due) or 'walk' (Cash Sales Due)

    public string $search = '';

    public int $perPage = 15;

    public function mount()
    {
        $this->view = request()->query('type') === 'walk' ? 'walk' : 'total';
    }

    public function render()
    {
        if ($this->view === 'total') {
            $dues = Customer::where('current_due', '>', 0)
                ->when($this->search, function ($q) {
                    $term = '%'.trim($this->search).'%';
                    $q->where('name', 'like', $term)->orWhere('phone', 'like', $term);
                })
                ->orderByDesc('current_due')
                ->paginate($this->perPage);
        } else {
            $dues = Sale::where('due_amount', '>', 0)
                ->with(['customer', 'user'])
                ->when($this->search, function ($q) {
                    $term = '%'.trim($this->search).'%';
                    $q->where('invoice_no', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
                })
                ->latest('id')
                ->paginate($this->perPage);
        }

        return view('livewire.accounts.due-list-manager', [
            'dues' => $dues,
            'totalDueSum' => Customer::sum('current_due'),
            'walkDueSum' => Sale::where('due_amount', '>', 0)->sum('due_amount'),
        ]);
    }
}
