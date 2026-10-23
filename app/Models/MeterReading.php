<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $fillable = [
        'utility_meter_id',
        'reading_date',
        'previous_reading',
        'current_reading',
        'consumption',
        'unit_rate',
        'fixed_charge',
        'charge_amount',
        'adjustment_reason',
        'recorded_by',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'unit_rate' => 'decimal:4',
        'fixed_charge' => 'decimal:2',
        'charge_amount' => 'decimal:2',
    ];

    public function meter(): BelongsTo
    {
        return $this->belongsTo(UtilityMeter::class, 'utility_meter_id');
    }
}
