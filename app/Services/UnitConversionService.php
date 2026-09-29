<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;

class UnitConversionService
{
    /**
     * Convert any unit quantity to base atomic pieces.
     */
    public function toBasePieces(Product $product, int $quantity, ?int $unitId = null): int
    {
        if ($quantity <= 0) {
            return 0;
        }

        if (! $unitId || $unitId === $product->base_unit_id) {
            return $quantity;
        }

        $productUnit = ProductUnit::where('product_id', $product->id)
            ->where('unit_id', $unitId)
            ->first();

        if (! $productUnit) {
            $unit = Unit::find($unitId);
            $multiplier = $unit ? (int) $unit->conversion_factor : 1;

            return $quantity * max(1, $multiplier);
        }

        return $quantity * max(1, $productUnit->multiplier);
    }

    /**
     * Convert base pieces back to display format for a specific target unit.
     */
    public function fromBasePieces(Product $product, int $totalPieces, int $targetUnitId): array
    {
        $productUnit = ProductUnit::where('product_id', $product->id)
            ->where('unit_id', $targetUnitId)
            ->first();

        $multiplier = $productUnit ? max(1, $productUnit->multiplier) : 1;

        $fullUnits = intdiv($totalPieces, $multiplier);
        $remainingPieces = $totalPieces % $multiplier;

        return [
            'units' => $fullUnits,
            'remaining_pieces' => $remainingPieces,
            'multiplier' => $multiplier,
        ];
    }

    /**
     * Calculate effective purchase cost per base piece including bonus scheme.
     * Formula: Total Billed Cost / Total Pieces Received (Billed + Bonus)
     */
    public function calculateEffectiveBaseCost(
        int $billedQuantity,
        int $bonusQuantity,
        int $unitMultiplier,
        float $billedTotalCost
    ): float {
        $totalPieces = ($billedQuantity + $bonusQuantity) * max(1, $unitMultiplier);

        if ($totalPieces <= 0) {
            return 0.0000;
        }

        return round($billedTotalCost / $totalPieces, 4);
    }

    /**
     * Calculate unit price for a packaging unit from base piece rate.
     */
    public function calculateUnitPrice(float $basePieceRate, int $unitMultiplier): float
    {
        return round($basePieceRate * max(1, $unitMultiplier), 4);
    }
}
