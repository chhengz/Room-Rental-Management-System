<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends BaseApiController
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Payment::query()->with(['tenant', 'allocations.invoice']);
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }
        if ($request->filled('method')) {
            $query->where('method', $request->string('method'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->string('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->string('to_date'));
        }

        return $this->success($query->latest('payment_date')->paginate((int) $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_number' => ['required', 'string', 'max:50', 'unique:payments,payment_number'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', 'in:cash,bank_transfer,card,other'],
            'status' => ['required', 'in:recorded,reversed'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.invoice_id' => ['required', 'exists:invoices,id'],
            'allocations.*.amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $allocations = $data['allocations'];
        unset($data['allocations']);
        $data['recorded_by'] = $request->user()?->id;

        return $this->success($this->paymentService->createWithAllocations($data, $allocations), 'Payment recorded.', 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        return $this->success($payment->load(['tenant', 'allocations.invoice']));
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $data = $request->validate([
            'payment_number' => ['sometimes', 'string', 'max:50', 'unique:payments,payment_number,'.$payment->id],
            'tenant_id' => ['sometimes', 'exists:tenants,id'],
            'payment_date' => ['sometimes', 'date'],
            'amount' => ['sometimes', 'numeric', 'gt:0'],
            'method' => ['sometimes', 'in:cash,bank_transfer,card,other'],
            'status' => ['sometimes', 'in:recorded,reversed'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['sometimes', 'array', 'min:1'],
            'allocations.*.invoice_id' => ['required_with:allocations', 'exists:invoices,id'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'gt:0'],
        ]);

        $allocations = $data['allocations'] ?? $payment->allocations()->get(['invoice_id', 'amount'])->toArray();
        unset($data['allocations']);

        DB::transaction(function () use ($payment): void {
            foreach ($payment->allocations as $allocation) {
                $invoice = $allocation->invoice()->lockForUpdate()->first();
                if ($invoice) {
                    $invoice->paid_amount = round((float) $invoice->paid_amount - (float) $allocation->amount, 2);
                    $invoice->balance_amount = round((float) $invoice->total_amount - (float) $invoice->paid_amount, 2);
                    $invoice->status = $invoice->balance_amount <= 0 ? 'paid' : ((float) $invoice->paid_amount > 0 ? 'partially_paid' : 'issued');
                    $invoice->save();
                }
            }
        });

        return $this->success($this->paymentService->updateWithAllocations($payment, $data, $allocations), 'Payment updated.');
    }

    public function destroy(Payment $payment): JsonResponse
    {
        if ($payment->status === 'reversed') {
            $payment->delete();

            return $this->success(null, 'Payment deleted.');
        }

        return $this->error('Recorded payments must be reversed, not deleted.', 422);
    }
}
