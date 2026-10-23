<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoomController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Room::query()->with(['building', 'roomType']);

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->integer('building_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('floor')) {
            $query->where('floor', $request->integer('floor'));
        }
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->integer('room_type_id'));
        }
        if ($request->filled('min_price')) {
            $query->where('rental_rate', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('rental_rate', '<=', $request->input('max_price'));
        }
        if ($request->filled('search')) {
            $query->where('room_number', 'like', '%'.$request->string('search').'%');
        }

        return $this->success($query->orderBy('building_id')->orderBy('room_number')->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'room_number' => ['required', 'string', 'max:30'],
            'floor' => ['required', 'integer', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'rental_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,occupied,reserved,maintenance,inactive'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
        ]);

        $room = Room::create($data);

        return $this->success($room->load(['building', 'roomType']), 'Room created.', 201);
    }

    public function show(Room $room): JsonResponse
    {
        return $this->success($room->load([
            'building',
            'roomType',
            'rentalContracts' => fn ($q) => $q->latest()->limit(5),
        ]));
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate([
            'building_id' => ['sometimes', 'exists:buildings,id'],
            'room_type_id' => ['sometimes', 'exists:room_types,id'],
            'room_number' => ['sometimes', 'string', 'max:30'],
            'floor' => ['sometimes', 'integer', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'rental_rate' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:available,occupied,reserved,maintenance,inactive'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
        ]);

        if (array_key_exists('status', $data) && in_array($data['status'], ['available', 'inactive'], true)) {
            $hasActiveContract = $room->rentalContracts()
                ->whereIn('status', ['upcoming', 'active'])
                ->exists();
            if ($hasActiveContract) {
                throw ValidationException::withMessages([
                    'status' => 'Room with active/upcoming contracts cannot be set to available or inactive.',
                ]);
            }
        }

        $room->update($data);

        return $this->success($room->fresh(['building', 'roomType']), 'Room updated.');
    }

    public function destroy(Room $room): JsonResponse
    {
        if ($room->rentalContracts()->whereIn('status', ['upcoming', 'active'])->exists()) {
            return $this->error('Cannot delete room with active or upcoming contracts.', 422);
        }

        $room->delete();

        return $this->success(null, 'Room deleted.');
    }
}
