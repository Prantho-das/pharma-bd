<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'invoice_date' => 'date',
        'delivery_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class)->orderBy('payment_date', 'desc');
    }

    /**
     * Aging days elapsed since invoice date
     */
    public function getAgingDaysAttribute(): int
    {
        $date = $this->invoice_date ?? $this->created_at;
        return $date ? (int) Carbon::parse($date)->diffInDays(now()) : 0;
    }

    /**
     * Aging Bucket Category (0-30, 31-60, 61-90, 90+)
     */
    public function getAgingBucketAttribute(): string
    {
        $days = $this->aging_days;

        if ($days <= 30) {
            return '0-30 Days';
        } elseif ($days <= 60) {
            return '31-60 Days';
        } elseif ($days <= 90) {
            return '61-90 Days';
        } else {
            return '90+ Days (Overdue)';
        }
    }
}
