<?php

namespace App\Livewire\Pos;

use App\Actions\Pos\ProcessSaleReturnAction;
use App\Models\Sale;
use App\Models\SaleReturn;
use Exception;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Sales Returns & Refunds - PMS')]
class SaleReturnManager extends Component
{
    use WithPagination;

    public string $activeTab = 'create'; // 'create' or 'history'

    public string $searchInvoice = '';

    public ?int $selectedSaleId = null;

    public ?Sale $selectedSale = null;

    // Return Form State
    public string $refundType = 'CASH'; // 'CASH' or 'DUE_ADJUSTMENT'

    public string $reason = '';

    public array $returnItems = []; // keyed by sale_item_id

    public ?SaleReturn $latestReturn = null;

    public bool $showSuccessModal = false;

    // History Tab State
    public string $historySearch = '';

    public int $perPage = 15;

    public function searchSale()
    {
        $this->resetErrorBag();
        $term = trim($this->searchInvoice);
        if (empty($term)) {
            $this->addError('searchInvoice', 'Please enter an invoice number to search.');

            return;
        }

        $sale = Sale::with(['items.product', 'items.batch', 'items.unit', 'customer', 'branch', 'doctor'])
            ->where('invoice_no', $term)
            ->first();

        if (! $sale) {
            $this->addError('searchInvoice', "No invoice found matching '{$term}'.");
            $this->selectedSale = null;
            $this->returnItems = [];

            return;
        }

        $this->selectedSale = $sale;
        $this->selectedSaleId = $sale->id;
        $this->returnItems = [];

        foreach ($sale->items as $item) {
            $this->returnItems[$item->id] = [
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'unit_id' => $item->unit_id,
                'product_name' => $item->product->name,
                'batch_number' => $item->batch?->batch_number ?? 'N/A',
                'unit_name' => $item->unit?->name ?? 'Pcs',
                'max_qty' => (int) $item->quantity,
                'qty' => 0,
                'unit_price' => (float) $item->unit_price,
                'is_damaged' => false,
            ];
        }
    }

    public function calculateTotalRefund(): float
    {
        $total = 0.00;
        foreach ($this->returnItems as $item) {
            $qty = (int) ($item['qty'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            if ($qty > 0) {
                $total += ($qty * $price);
            }
        }

        return round($total, 2);
    }

    public function submitReturn(ProcessSaleReturnAction $action)
    {
        $this->resetErrorBag();

        if (! $this->selectedSale) {
            $this->addError('general', 'No active sale invoice selected.');

            return;
        }

        $itemsToReturn = [];
        foreach ($this->returnItems as $item) {
            $qty = (int) ($item['qty'] ?? 0);
            if ($qty > 0) {
                if ($qty > $item['max_qty']) {
                    $this->addError("returnItems.{$item['sale_item_id']}.qty", "Return qty cannot exceed sold qty ({$item['max_qty']}).");

                    return;
                }

                $itemsToReturn[] = [
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $qty,
                    'refund_unit_price' => $item['unit_price'],
                    'is_damaged' => (bool) $item['is_damaged'],
                ];
            }
        }

        if (empty($itemsToReturn)) {
            $this->addError('general', 'Please enter a return quantity (> 0) for at least one item.');

            return;
        }

        try {
            $this->latestReturn = $action->execute([
                'sale_id' => $this->selectedSale->id,
                'branch_id' => $this->selectedSale->branch_id,
                'user_id' => auth()->id(),
                'refund_type' => $this->refundType,
                'reason' => $this->reason,
                'items' => $itemsToReturn,
            ]);

            $this->showSuccessModal = true;
            $this->selectedSale = null;
            $this->returnItems = [];
            $this->searchInvoice = '';
            $this->reason = '';
        } catch (Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->latestReturn = null;
    }

    public function render()
    {
        $historyQuery = SaleReturn::with(['sale', 'customer', 'user', 'items.product', 'items.batch'])
            ->when($this->historySearch, function ($q) {
                $term = '%'.trim($this->historySearch).'%';
                $q->where('return_no', 'like', $term)
                    ->orWhereHas('sale', fn ($s) => $s->where('invoice_no', 'like', $term))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->latest('id');

        $returns = $historyQuery->paginate($this->perPage);

        return view('livewire.pos.sale-return-manager', [
            'returns' => $returns,
            'totalRefundCalculated' => $this->calculateTotalRefund(),
        ]);
    }
}
