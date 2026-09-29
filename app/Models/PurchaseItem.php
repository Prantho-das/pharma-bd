<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'bonus_quantity' => 'integer',
        'unit_multiplier' => 'integer',
        'total_pieces' => 'integer',
        'purchase_price_per_unit' => 'decimal:4',
        'mrp_per_unit' => 'decimal:4',
        'total_cost' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function batch(): HasOne
    {
        return $this->hasOne(Batch::class);
    }
}
