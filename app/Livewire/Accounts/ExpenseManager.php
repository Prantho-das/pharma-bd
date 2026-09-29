<?php

namespace App\Livewire\Accounts;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pharmacy Expenses & Cash Payouts - PMS')]
class ExpenseManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedCategory = '';

    // Create Modal
    public bool $showModal = false;

    public string $title = '';

    public ?int $category_id = null;

    public float $amount = 0.00;

    public string $date = '';

    public string $payment_method = 'CASH';

    public string $note = '';

    // Quick Category Modal
    public bool $showCategoryModal = false;

    public string $newCategoryName = '';

    public string $newCategoryDesc = '';

    public ?string $successMessage = null;

    public function mount()
    {
        $this->date = now()->toDateString();
    }

    public function openCreateModal()
    {
        $this->reset(['title', 'category_id', 'amount', 'note']);
        $this->date = now()->toDateString();
        $this->payment_method = 'CASH';
        $this->showModal = true;
    }

    public function saveExpense()
    {
        $this->validate([
            'title' => 'required|string|min:2',
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'payment_method' => 'required|in:CASH,BANK,MFS',
        ]);

        Expense::create([
            'branch_id' => Auth::user()?->branch_id,
            'category_id' => $this->category_id,
            'user_id' => Auth::id() ?? 1,
            'title' => $this->title,
            'amount' => $this->amount,
            'date' => $this->date,
            'payment_method' => $this->payment_method,
            'note' => $this->note,
        ]);

        $this->showModal = false;
        $this->successMessage = 'Expense voucher recorded successfully!';
    }

    public function saveCategory()
    {
        $this->validate(['newCategoryName' => 'required|string|min:2']);

        $cat = ExpenseCategory::create([
            'branch_id' => Auth::user()?->branch_id,
            'name' => $this->newCategoryName,
            'description' => $this->newCategoryDesc,
        ]);

        $this->category_id = $cat->id;
        $this->showCategoryModal = false;
        $this->newCategoryName = '';
        $this->newCategoryDesc = '';
    }

    public function render()
    {
        $expenses = Expense::with(['category', 'user'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%'.trim($this->search).'%'))
            ->when($this->selectedCategory, fn ($q) => $q->where('category_id', $this->selectedCategory))
            ->latest('date')
            ->paginate(15);

        return view('livewire.accounts.expense-manager', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'totalExpenseAmount' => Expense::sum('amount'),
        ]);
    }
}
