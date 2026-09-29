<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'multiplier' => 'integer',
        'purchase_price' => 'decimal:4',
        'mrp_price' => 'decimal:4',
        'is_default_purchase' => 'boolean',
        'is_default_sale' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
