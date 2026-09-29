<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'initial_stock' => 'integer',
        'current_stock' => 'integer',
        'purchase_rate' => 'decimal:4',
        'mrp_rate' => 'decimal:4',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'alert_days_before_expiry' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // Scopes
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('current_stock', '>', 0)
            ->where('expiry_date', '>', now()->toDateString());
    }

    public function scopeFefo(Builder $query): Builder
    {
        return $query->available()->orderBy('expiry_date', 'asc')->orderBy('created_at', 'asc');
    }

    public function scopeNearExpiry(Builder $query, int $days = 90): Builder
    {
        $today = now()->toDateString();
        $targetDate = now()->addDays($days)->toDateString();

        return $query->where('current_stock', '>', 0)
            ->whereBetween('expiry_date', [$today, $targetDate])
            ->orderBy('expiry_date', 'asc');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expiry_date', '<=', now()->toDateString())
            ->orderBy('expiry_date', 'asc');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isNearExpiry(): bool
    {
        return ! $this->isExpired() && $this->expiry_date->diffInDays(now()) <= $this->alert_days_before_expiry;
    }
}
