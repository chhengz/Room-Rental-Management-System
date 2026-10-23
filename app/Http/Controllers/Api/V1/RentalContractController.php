<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RentalContract;
use App\Services\ContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalContractController extends BaseApiController
{
    public function __construct(private readonly ContractService $contractService) {}

    public function index(Request $request): JsonResponse
    {
        $query = RentalContract::query()->with(['tenant', 'room.building']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->integer('room_id'));
        }

        return $this->success($query->latest()->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contract_number' => ['required', 'string', 'max:50', 'unique:rental_contracts,contract_number'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'billing_frequency' => ['required', 'in:monthly,quarterly,yearly'],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
            'status' => ['required', 'in:upcoming,active,expired,renewed,terminated'],
            'terms' => ['nullable', 'string'],
        ]);

        $this->contractService->ensureNoRoomConflict($data);
        $contract = RentalContract::create($data);

        return $this->success($contract->load(['tenant', 'room']), 'Contract created.', 201);
    }

    public function show(RentalContract $contract): JsonResponse
    {
        return $this->success($contract->load(['tenant', 'room.building', 'invoices']));
    }

    public function update(Request $request, RentalContract $contract): JsonResponse
    {
        $data = $request->validate([
            'contract_number' => ['sometimes', 'string', 'max:50', 'unique:rental_contracts,contract_number,'.$contract->id],
            'tenant_id' => ['sometimes', 'exists:tenants,id'],
            'room_id' => ['sometimes', 'exists:rooms,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date'],
            'rent_amount' => ['sometimes', 'numeric', 'min:0'],
            'deposit_amount' => ['sometimes', 'numeric', 'min:0'],
            'billing_frequency' => ['sometimes', 'in:monthly,quarterly,yearly'],
            'due_day' => ['sometimes', 'integer', 'min:1', 'max:31'],
            'status' => ['sometimes', 'in:upcoming,active,expired,renewed,terminated'],
            'terms' => ['nullable', 'string'],
            'terminated_at' => ['nullable', 'date'],
            'termination_reason' => ['nullable', 'string'],
        ]);

        $payload = array_merge($contract->toArray(), $data);
        $this->contractService->ensureNoRoomConflict($payload, $contract->id);

        $contract->update($data);

        return $this->success($contract->fresh(['tenant', 'room']), 'Contract updated.');
    }

    public function destroy(RentalContract $contract): JsonResponse
    {
        if ($contract->invoices()->exists()) {
            return $this->error('Contract has invoices and cannot be deleted.', 422);
        }

        $contract->delete();

        return $this->success(null, 'Contract deleted.');
    }
}
