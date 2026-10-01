<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseOrder;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Purchase Orders (PO) - PMS')]
class PurchaseOrderManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 15;

    public ?PurchaseOrder $selectedPo = null;
    public bool $showViewModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function viewPo(int $poId)
    {
        $this->selectedPo = PurchaseOrder::with(['supplier', 'items.product', 'items.unit', 'branch', 'creator', 'purchases'])->find($poId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedPo = null;
    }

    public function cancelPo(int $poId)
    {
        $po = PurchaseOrder::find($poId);
        if ($po && in_array($po->status, ['DRAFT', 'ORDERED'])) {
            $po->update(['status' => 'CANCELLED']);
            session()->flash('success', "পারচেজ অর্ডার #{$po->po_number} বাতিল করা হয়েছে।");
        }
    }

    public function render()
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'purchases'])
            ->latest('order_date');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('po_number', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', function ($sq) {
                      $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('company_name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                  });
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $purchaseOrders = $query->paginate($this->perPage);

        return view('livewire.purchases.purchase-order-manager', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }
}
