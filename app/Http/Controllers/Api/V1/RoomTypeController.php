<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomTypeController extends BaseApiController
{
    public function index(): JsonResponse
    {
        return $this->success(RoomType::orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:room_types,name'],
            'default_capacity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        return $this->success(RoomType::create($data), 'Room type created.', 201);
    }

    public function show(RoomType $roomType): JsonResponse
    {
        return $this->success($roomType);
    }

    public function update(Request $request, RoomType $roomType): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:room_types,name,'.$roomType->id],
            'default_capacity' => ['sometimes', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);
        $roomType->update($data);

        return $this->success($roomType->fresh(), 'Room type updated.');
    }

    public function destroy(RoomType $roomType): JsonResponse
    {
        $roomType->delete();

        return $this->success(null, 'Room type deleted.');
    }
}
