<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtilityRate extends Model
{
    protected $fillable = [
        'utility_type',
        'effective_from',
        'effective_to',
        'unit_rate',
        'fixed_charge',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'unit_rate' => 'decimal:4',
        'fixed_charge' => 'decimal:2',
    ];
}
