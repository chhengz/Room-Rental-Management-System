<?php

namespace App\Services;

use App\Models\RentalContract;
use Illuminate\Validation\ValidationException;

class ContractService
{
    public function ensureNoRoomConflict(array $data, ?int $ignoreContractId = null): void
    {
        $query = RentalContract::query()
            ->where('room_id', $data['room_id'])
            ->whereIn('status', ['upcoming', 'active'])
            ->where(function ($q) use ($data): void {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $data['start_date']);
            })
            ->where('start_date', '<=', $data['end_date'] ?? $data['start_date']);

        if ($ignoreContractId !== null) {
            $query->whereKeyNot($ignoreContractId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'room_id' => 'Room already has a conflicting active/upcoming contract.',
            ]);
        }
    }
}
