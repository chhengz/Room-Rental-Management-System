<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends BaseApiController
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query()->with(['tenant', 'contract']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('issue_date', '>=', $request->string('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('issue_date', '<=', $request->string('to_date'));
        }

        return $this->success($query->latest('issue_date')->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invoice_number' => ['required', 'string', 'max:50', 'unique:invoices,invoice_number'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'rental_contract_id' => ['nullable', 'exists:rental_contracts,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['required', 'in:draft,issued,partially_paid,paid,overdue,void'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'late_fee_total' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.category' => ['required', 'string', 'max:100'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.metadata' => ['nullable', 'array'],
        ]);

        $items = $data['items'];
        unset($data['items']);
        $data['created_by'] = $request->user()?->id;

        return $this->success($this->invoiceService->create($data, $items), 'Invoice created.', 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return $this->success($invoice->load(['tenant', 'contract', 'items', 'allocations.payment']));
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'invoice_number' => ['sometimes', 'string', 'max:50', 'unique:invoices,invoice_number,'.$invoice->id],
            'tenant_id' => ['sometimes', 'exists:tenants,id'],
            'rental_contract_id' => ['nullable', 'exists:rental_contracts,id'],
            'issue_date' => ['sometimes', 'date'],
            'due_date' => ['sometimes', 'date'],
            'period_start' => ['sometimes', 'date'],
            'period_end' => ['sometimes', 'date'],
            'status' => ['sometimes', 'in:draft,issued,partially_paid,paid,overdue,void'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'late_fee_total' => ['nullable', 'numeric', 'min:0'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.category' => ['required_with:items', 'string', 'max:100'],
            'items.*.description' => ['required_with:items', 'string'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.metadata' => ['nullable', 'array'],
        ]);

        $items = $data['items'] ?? $invoice->items()->get(['category', 'description', 'quantity', 'unit_price', 'metadata'])->toArray();
        unset($data['items']);

        return $this->success($this->invoiceService->update($invoice, $data, $items), 'Invoice updated.');
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        if ($invoice->allocations()->exists()) {
            return $this->error('Invoice has payment allocations and cannot be deleted.', 422);
        }

        $invoice->delete();

        return $this->success(null, 'Invoice deleted.');
    }
}
