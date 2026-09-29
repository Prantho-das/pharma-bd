<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use Exception;

class BatchAllocationService
{
    /**
     * Allocate stock deductions using FEFO (First-Expired, First-Out).
     * Returns an array of allocations with batch_id, allocated_pieces, unit_cost, etc.
     *
     * @throws Exception
     */
    public function allocateFefo(Product $product, int $requiredPieces, ?int $branchId = null): array
    {
        if ($requiredPieces <= 0) {
            return [];
        }

        $query = Batch::query()
            ->where('product_id', $product->id)
            ->where('current_stock', '>', 0)
            ->where('expiry_date', '>', now()->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->orderBy('created_at', 'asc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $batches = $query->lockForUpdate()->get();

        $totalAvailable = $batches->sum('current_stock');
        if ($totalAvailable < $requiredPieces) {
            throw new Exception("Insufficient stock for product [{$product->name}]. Required: {$requiredPieces} pieces, Available: {$totalAvailable} pieces.");
        }

        $allocations = [];
        $remaining = $requiredPieces;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min($batch->current_stock, $remaining);
            $allocations[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'pieces' => $deduct,
                'unit_cost' => (float) $batch->purchase_rate,
                'batch_model' => $batch,
            ];

            $remaining -= $deduct;
        }

        return $allocations;
    }

    /**
     * Deduct allocated stock from batch records atomically.
     */
    public function deductStock(array $allocations): void
    {
        foreach ($allocations as $item) {
            $batch = $item['batch_model'] ?? Batch::find($item['batch_id']);
            if ($batch) {
                $batch->decrement('current_stock', $item['pieces']);
            }
        }
    }

    /**
     * Restock / Return pieces back into a specific batch or latest batch.
     */
    public function returnStock(int $batchId, int $pieces): void
    {
        if ($pieces <= 0) {
            return;
        }

        $batch = Batch::find($batchId);
        if ($batch) {
            $batch->increment('current_stock', $pieces);
        }
    }
}
