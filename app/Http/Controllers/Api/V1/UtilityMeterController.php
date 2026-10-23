<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\UtilityMeter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UtilityMeterController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = UtilityMeter::query()->with('room.building');
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->integer('room_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return $this->success($query->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'type' => ['required', 'in:electricity,water'],
            'meter_code' => ['required', 'string', 'max:100', 'unique:utility_meters,meter_code'],
            'installed_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return $this->success(UtilityMeter::create($data), 'Utility meter created.', 201);
    }

    public function show(UtilityMeter $utilityMeter): JsonResponse
    {
        return $this->success($utilityMeter->load(['room', 'readings']));
    }

    public function update(Request $request, UtilityMeter $utilityMeter): JsonResponse
    {
        $data = $request->validate([
            'room_id' => ['sometimes', 'exists:rooms,id'],
            'type' => ['sometimes', 'in:electricity,water'],
            'meter_code' => ['sometimes', 'string', 'max:100', 'unique:utility_meters,meter_code,'.$utilityMeter->id],
            'installed_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $utilityMeter->update($data);

        return $this->success($utilityMeter->fresh(), 'Utility meter updated.');
    }

    public function destroy(UtilityMeter $utilityMeter): JsonResponse
    {
        if ($utilityMeter->readings()->exists()) {
            return $this->error('Utility meter has readings and cannot be deleted.', 422);
        }

        $utilityMeter->delete();

        return $this->success(null, 'Utility meter deleted.');
    }
}
