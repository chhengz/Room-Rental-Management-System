<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Tenant::query();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(function ($q) use ($term): void {
                $q->where('full_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        return $this->success($query->orderBy('full_name')->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive,blacklisted'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->success(Tenant::create($data), 'Tenant created.', 201);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return $this->success($tenant->load([
            'rentalContracts.room.building',
            'invoices',
            'payments',
        ]));
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:active,inactive,blacklisted'],
            'notes' => ['nullable', 'string'],
        ]);

        $tenant->update($data);

        return $this->success($tenant->fresh(), 'Tenant updated.');
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        if ($tenant->rentalContracts()->exists() || $tenant->invoices()->exists() || $tenant->payments()->exists()) {
            return $this->error('Tenant has related records and cannot be deleted.', 422);
        }

        $tenant->delete();

        return $this->success(null, 'Tenant deleted.');
    }
}
