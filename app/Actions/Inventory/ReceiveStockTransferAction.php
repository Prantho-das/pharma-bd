<?php

namespace App\Actions\Inventory;

use App\Models\Batch;
use App\Models\StockTransfer;
use Exception;
use Illuminate\Support\Facades\DB;

class ReceiveStockTransferAction
{
    /**
     * Receive or Reject stock transfer at destination branch.
     *
     * @param  string  $action  'RECEIVED'|'REJECTED'
     *
     * @throws Exception
     */
    public function execute(StockTransfer $transfer, string $action, int $receivedBy): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $action, $receivedBy) {
            $transfer = StockTransfer::lockForUpdate()->with('items.batch', 'items.product')->findOrFail($transfer->id);

            if ($transfer->status !== 'IN_TRANSIT' && $transfer->status !== 'PENDING') {
                throw new Exception("Stock transfer is already {$transfer->status}.");
            }

            if ($action === 'RECEIVED') {
                foreach ($transfer->items as $item) {
                    $sourceBatch = $item->batch;

                    // Find or create matching batch in destination branch
                    $destBatch = Batch::lockForUpdate()->firstOrCreate(
                        [
                            'branch_id' => $transfer->to_branch_id,
                            'product_id' => $item->product_id,
                            'batch_number' => $sourceBatch->batch_number,
                        ],
                        [
                            'base_unit_id' => $sourceBatch->base_unit_id,
                            'initial_stock' => $item->total_pieces,
                            'current_stock' => 0,
                            'purchase_rate' => $sourceBatch->purchase_rate,
                            'mrp_rate' => $sourceBatch->mrp_rate,
                            'manufacture_date' => $sourceBatch->manufacture_date,
                            'expiry_date' => $sourceBatch->expiry_date,
                            'alert_days_before_expiry' => $sourceBatch->alert_days_before_expiry,
                        ]
                    );

                    $destBatch->increment('current_stock', $item->total_pieces);
                }

                $transfer->update([
                    'status' => 'RECEIVED',
                    'received_by' => $receivedBy,
                    'received_at' => now(),
                ]);
            } elseif ($action === 'REJECTED') {
                // Return stock back to source branch
                foreach ($transfer->items as $item) {
                    if ($item->batch) {
                        $item->batch->increment('current_stock', $item->total_pieces);
                    }
                }

                $transfer->update([
                    'status' => 'REJECTED',
                    'received_by' => $receivedBy,
                    'received_at' => now(),
                ]);
            }

            return $transfer;
        });
    }
}
