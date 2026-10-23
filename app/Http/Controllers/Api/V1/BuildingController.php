<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Building;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuildingController extends BaseApiController
{
    public function __construct(private readonly ActivityLogService $activityLogService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Building::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        return $this->success($query->orderBy('name')->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:buildings,code'],
            'address' => ['nullable', 'string'],
            'total_floors' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $building = Building::create($data);
        $this->activityLogService->log('building.created', $building, null, $building->toArray());

        return $this->success($building, 'Building created.', 201);
    }

    public function show(Building $building): JsonResponse
    {
        return $this->success($building->loadCount('rooms'));
    }

    public function update(Request $request, Building $building): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:buildings,code,'.$building->id],
            'address' => ['nullable', 'string'],
            'total_floors' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $before = $building->toArray();
        $building->update($data);
        $this->activityLogService->log('building.updated', $building, $before, $building->fresh()->toArray());

        return $this->success($building->fresh(), 'Building updated.');
    }

    public function destroy(Building $building): JsonResponse
    {
        $before = $building->toArray();
        $building->delete();
        $this->activityLogService->log('building.deleted', $building, $before, null);

        return $this->success(null, 'Building deleted.');
    }
}
