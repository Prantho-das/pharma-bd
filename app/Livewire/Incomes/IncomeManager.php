<?php

namespace App\Livewire\Incomes;

use App\Models\Income;
use App\Models\IncomeCategory;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Incomes Management - PMS')]
class IncomeManager extends Component
{
    use WithPagination;

    public string $tab = 'incomes'; // 'incomes' or 'categories'

    public string $search = '';

    public int $perPage = 15;

    // Income Form
    public string $title = '';

    public ?int $categoryId = null;

    public float $amount = 0.00;

    public string $date = '';

    public string $paymentMethod = 'CASH';

    public string $note = '';

    // Category Form
    public string $categoryName = '';

    public string $categoryDescription = '';

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function saveIncome()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        Income::create([
            'branch_id' => auth()->user()?->branch_id,
            'category_id' => $this->categoryId,
            'user_id' => auth()->id(),
            'title' => $this->title,
            'amount' => $this->amount,
            'date' => $this->date,
            'payment_method' => $this->paymentMethod,
            'note' => $this->note,
        ]);

        session()->flash('success', 'Income record created successfully!');
        $this->reset(['title', 'amount', 'note', 'categoryId']);
    }

    public function saveCategory()
    {
        $this->validate(['categoryName' => 'required|string|max:255']);

        IncomeCategory::create([
            'branch_id' => auth()->user()?->branch_id,
            'name' => $this->categoryName,
            'description' => $this->categoryDescription,
        ]);

        session()->flash('success', 'Income category added!');
        $this->reset(['categoryName', 'categoryDescription']);
    }

    public function render()
    {
        $incomes = Income::with(['category', 'user'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest('date')
            ->paginate($this->perPage);

        return view('livewire.incomes.income-manager', [
            'incomes' => $incomes,
            'categories' => IncomeCategory::orderBy('name')->get(),
        ]);
    }
}
