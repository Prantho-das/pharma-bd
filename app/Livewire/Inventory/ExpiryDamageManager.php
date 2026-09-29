<?php

namespace App\Livewire\Inventory;

use App\Models\Batch;
use App\Models\StockAdjustment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Near-Expiry Return & Damage Loss Manager - PMS')]
class ExpiryDamageManager extends Component
{
    use WithPagination;

    public string $activeTab = 'near_expiry'; // near_expiry, expired, damage_log

    public int $expiryDaysThreshold = 60; // 30, 60, 90

    public string $search = '';

    // Damage write-off modal
    public bool $showDamageModal = false;

    public ?int $selectedBatchId = null;

    public ?Batch $selectedBatch = null;

    public int $damageQuantity = 1;

    public string $damageReason = 'Expired & Disposed'; // Expired & Disposed, Broken / Physical Damage, Temperature Excursion, Leakage / Quality Issue

    public string $damageNote = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function openDamageModal(int $batchId)
    {
        $this->selectedBatchId = $batchId;
        $this->selectedBatch = Batch::with(['product.baseUnit'])->findOrFail($batchId);
        $this->damageQuantity = min($this->selectedBatch->current_stock, max(1, $this->selectedBatch->current_stock));
        $this->showDamageModal = true;
    }

    public function recordDamageWriteOff()
    {
        $this->validate([
            'damageQuantity' => 'required|integer|min:1',
            'damageReason' => 'required|string',
        ]);

        if (! $this->selectedBatch) {
            return;
        }

        if ($this->damageQuantity > $this->selectedBatch->current_stock) {
            $this->errorMessage = "Damage quantity cannot exceed available batch stock ({$this->selectedBatch->current_stock}).";

            return;
        }

        DB::transaction(function () {
            $costPrice = (float) ($this->selectedBatch->purchase_rate ?? 0);
            $totalLoss = $costPrice * $this->damageQuantity;

            // Reduce batch stock
            $this->selectedBatch->decrement('current_stock', $this->damageQuantity);

            // Record stock adjustment loss
            StockAdjustment::create([
                'product_id' => $this->selectedBatch->product_id,
                'batch_id' => $this->selectedBatch->id,
                'user_id' => Auth::id() ?? 1,
                'adjustment_type' => 'DECREASE',
                'quantity' => $this->damageQuantity,
                'reason' => 'Damage Write-off: '.$this->damageReason.($this->damageNote ? ' ('.$this->damageNote.')' : '').' - Loss Valuation: ৳'.number_format($totalLoss, 2),
                'created_at' => now(),
            ]);
        });

        $this->showDamageModal = false;
        $this->successMessage = "Successfully recorded damage write-off for batch [{$this->selectedBatch->batch_number}].";
    }

    public function render()
    {
        $now = Carbon::now();
        $targetDate = Carbon::now()->addDays($this->expiryDaysThreshold);

        // Near Expiry Batches query (expiring between now and threshold)
        $nearExpiryQuery = Batch::with(['product.generic', 'product.manufacturer', 'product.baseUnit', 'purchaseItem.purchase.supplier'])
            ->where('current_stock', '>', 0)
            ->where('expiry_date', '>=', $now)
            ->where('expiry_date', '<=', $targetDate)
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('batch_number', 'like', $term)
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term)->orWhere('sku', 'like', $term));
                });
            })
            ->orderBy('expiry_date', 'asc')
            ->paginate(15, ['*'], 'near_page');

        // Expired Batches query (expiry < now)
        $expiredQuery = Batch::with(['product.generic', 'product.manufacturer', 'product.baseUnit', 'purchaseItem.purchase.supplier'])
            ->where('current_stock', '>', 0)
            ->where('expiry_date', '<', $now)
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('batch_number', 'like', $term)
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term)->orWhere('sku', 'like', $term));
                });
            })
            ->orderBy('expiry_date', 'desc')
            ->paginate(15, ['*'], 'expired_page');

        // Damage adjustments query
        $damageLogs = StockAdjustment::with(['product', 'batch', 'user'])
            ->where('adjustment_type', 'DECREASE')
            ->where('reason', 'like', '%Damage Write-off%')
            ->latest()
            ->paginate(15, ['*'], 'damage_page');

        $totalNearExpiryAtRisk = Batch::where('current_stock', '>', 0)
            ->where('expiry_date', '>=', $now)
            ->where('expiry_date', '<=', $targetDate)
            ->sum(DB::raw('current_stock * purchase_rate'));

        $totalExpiredValue = Batch::where('current_stock', '>', 0)
            ->where('expiry_date', '<', $now)
            ->sum(DB::raw('current_stock * purchase_rate'));

        return view('livewire.inventory.expiry-damage-manager', [
            'nearExpiryBatches' => $nearExpiryQuery,
            'expiredBatches' => $expiredQuery,
            'damageLogs' => $damageLogs,
            'totalNearExpiryAtRisk' => $totalNearExpiryAtRisk,
            'totalExpiredValue' => $totalExpiredValue,
        ]);
    }
}
