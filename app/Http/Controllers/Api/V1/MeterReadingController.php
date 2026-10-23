<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\MeterReading;
use App\Models\UtilityMeter;
use App\Services\UtilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeterReadingController extends BaseApiController
{
    public function __construct(private readonly UtilityService $utilityService) {}

    public function index(Request $request): JsonResponse
    {
        $query = MeterReading::query()->with(['meter.room']);
        if ($request->filled('utility_meter_id')) {
            $query->where('utility_meter_id', $request->integer('utility_meter_id'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('reading_date', '>=', $request->string('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('reading_date', '<=', $request->string('to_date'));
        }

        return $this->success($query->latest('reading_date')->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'utility_meter_id' => ['required', 'exists:utility_meters,id'],
            'reading_date' => ['required', 'date'],
            'previous_reading' => ['required', 'numeric', 'min:0'],
            'current_reading' => ['required', 'numeric', 'min:0'],
            'adjustment_reason' => ['nullable', 'string'],
        ]);

        $meter = UtilityMeter::query()->findOrFail($data['utility_meter_id']);
        $rate = $this->utilityService->resolveRate($meter->type, $data['reading_date']);
        $charge = $this->utilityService->calculateCharge(
            (float) $data['previous_reading'],
            (float) $data['current_reading'],
            (float) $rate->unit_rate,
            (float) $rate->fixed_charge
        );

        $reading = MeterReading::create(array_merge($data, [
            'consumption' => $charge['consumption'],
            'unit_rate' => $rate->unit_rate,
            'fixed_charge' => $rate->fixed_charge,
            'charge_amount' => $charge['charge_amount'],
            'recorded_by' => $request->user()?->id,
        ]));

        return $this->success($reading, 'Meter reading created.', 201);
    }

    public function show(MeterReading $meterReading): JsonResponse
    {
        return $this->success($meterReading->load('meter.room'));
    }

    public function update(Request $request, MeterReading $meterReading): JsonResponse
    {
        $data = $request->validate([
            'reading_date' => ['sometimes', 'date'],
            'previous_reading' => ['sometimes', 'numeric', 'min:0'],
            'current_reading' => ['sometimes', 'numeric', 'min:0'],
            'adjustment_reason' => ['nullable', 'string'],
        ]);

        $payload = array_merge($meterReading->toArray(), $data);
        $charge = $this->utilityService->calculateCharge(
            (float) $payload['previous_reading'],
            (float) $payload['current_reading'],
            (float) $meterReading->unit_rate,
            (float) $meterReading->fixed_charge
        );
        $payload['consumption'] = $charge['consumption'];
        $payload['charge_amount'] = $charge['charge_amount'];

        $meterReading->update($payload);

        return $this->success($meterReading->fresh(), 'Meter reading updated.');
    }

    public function destroy(MeterReading $meterReading): JsonResponse
    {
        $meterReading->delete();

        return $this->success(null, 'Meter reading deleted.');
    }
}
