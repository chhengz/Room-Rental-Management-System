<?php

namespace App\Services;

use App\Models\UtilityRate;
use Illuminate\Validation\ValidationException;

class UtilityService
{
    public function resolveRate(string $utilityType, string $readingDate): UtilityRate
    {
        $rate = UtilityRate::query()
            ->where('utility_type', $utilityType)
            ->where('effective_from', '<=', $readingDate)
            ->where(function ($q) use ($readingDate): void {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $readingDate);
            })
            ->orderByDesc('effective_from')
            ->first();

        if (! $rate) {
            throw ValidationException::withMessages([
                'utility_type' => 'No active utility rate found for reading date.',
            ]);
        }

        return $rate;
    }

    public function calculateCharge(float $previous, float $current, float $unitRate, float $fixedCharge): array
    {
        $consumption = round($current - $previous, 2);
        if ($consumption < 0) {
            throw ValidationException::withMessages([
                'current_reading' => 'Current reading cannot be lower than previous reading.',
            ]);
        }

        $charge = round(($consumption * $unitRate) + $fixedCharge, 2);

        return [
            'consumption' => $consumption,
            'charge_amount' => $charge,
        ];
    }
}
