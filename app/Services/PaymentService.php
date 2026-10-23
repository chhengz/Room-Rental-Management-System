<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function createWithAllocations(array $paymentData, array $allocations): Payment
    {
        return DB::transaction(function () use ($paymentData, $allocations): Payment {
            $payment = Payment::create($paymentData);
            $this->syncAllocations($payment, $allocations);

            return $payment->fresh(['allocations.invoice']);
        });
    }

    public function updateWithAllocations(Payment $payment, array $paymentData, array $allocations): Payment
    {
        return DB::transaction(function () use ($payment, $paymentData, $allocations): Payment {
            $payment->update($paymentData);
            $payment->allocations()->delete();
            $this->syncAllocations($payment, $allocations);

            return $payment->fresh(['allocations.invoice']);
        });
    }

    private function syncAllocations(Payment $payment, array $allocations): void
    {
        $sum = collect($allocations)->sum('amount');
        if (round((float) $sum, 2) > round((float) $payment->amount, 2)) {
            throw ValidationException::withMessages([
                'allocations' => 'Allocated amount cannot exceed payment amount.',
            ]);
        }

        $allocatedTotal = 0.0;
        foreach ($allocations as $allocation) {
            $invoice = Invoice::lockForUpdate()->findOrFail($allocation['invoice_id']);
            $amount = round((float) $allocation['amount'], 2);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'allocations' => 'Allocation amount must be greater than zero.',
                ]);
            }

            if ($amount > (float) $invoice->balance_amount) {
                throw ValidationException::withMessages([
                    'allocations' => "Allocation for invoice {$invoice->invoice_number} exceeds balance.",
                ]);
            }

            $payment->allocations()->create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
            ]);

            $invoice->paid_amount = round((float) $invoice->paid_amount + $amount, 2);
            $invoice->balance_amount = round((float) $invoice->total_amount - (float) $invoice->paid_amount, 2);
            $invoice->status = $invoice->balance_amount <= 0 ? 'paid' : 'partially_paid';
            $invoice->save();

            $allocatedTotal += $amount;
        }

        $payment->allocated_amount = $allocatedTotal;
        $payment->save();
    }
}
