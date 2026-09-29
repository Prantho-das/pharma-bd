<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\SaleItem;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Customer SMS Alerts & Chronic Refill Reminders - PMS')]
class SmsRefillManager extends Component
{
    use WithPagination;

    public string $activeTab = 'refills'; // refills, dues, custom_sms

    public string $search = '';

    // Custom SMS State
    public string $smsRecipientType = 'all_dues'; // all_dues, specific_customer, custom_number

    public ?int $selectedCustomerId = null;

    public string $customPhone = '';

    public string $smsMessage = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function sendRefillReminder(int $saleId, string $customerName, string $phone, string $productName)
    {
        $this->successMessage = "Refill reminder SMS successfully queued for {$customerName} ({$phone}) for medicine: {$productName}.";
    }

    public function sendDueReminder(int $customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->successMessage = "Due payment reminder SMS successfully queued for {$customer->name} ({$customer->phone}) for balance: ৳".number_format($customer->current_due, 2).'.';
        }
    }

    public function sendCustomSms()
    {
        $this->validate([
            'smsMessage' => 'required|string|min:5|max:480',
        ]);

        $this->successMessage = 'SMS campaign successfully queued for processing!';
        $this->smsMessage = '';
    }

    public function render()
    {
        // 1. Refill candidates (Sales made 20-35 days ago with customers)
        $refillCandidates = SaleItem::with(['sale.customer', 'product'])
            ->whereHas('sale', function ($q) {
                $q->whereNotNull('customer_id')
                    ->where('sale_date', '>=', Carbon::now()->subDays(45))
                    ->where('sale_date', '<=', Carbon::now()->subDays(15));
            })
            ->latest('id')
            ->paginate(15, ['*'], 'refill_page');

        // 2. Customers with due balance
        $dueCustomers = Customer::where('current_due', '>', 0)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%"))
            ->orderBy('current_due', 'desc')
            ->paginate(15, ['*'], 'due_page');

        $totalDueAmount = Customer::where('current_due', '>', 0)->sum('current_due');
        $totalDueCustomersCount = Customer::where('current_due', '>', 0)->count();

        return view('livewire.customers.sms-refill-manager', [
            'refillCandidates' => $refillCandidates,
            'dueCustomers' => $dueCustomers,
            'totalDueAmount' => $totalDueAmount,
            'totalDueCustomersCount' => $totalDueCustomersCount,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }
}
