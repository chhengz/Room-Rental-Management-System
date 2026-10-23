<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function create(array $invoiceData, array $items): Invoice
    {
        $this->ensureNoDuplicatePeriod($invoiceData);

        return DB::transaction(function () use ($invoiceData, $items): Invoice {
            $invoice = Invoice::create($invoiceData);
            $totals = $this->syncItemsAndTotals($invoice, $items, $invoiceData['discount_total'] ?? 0, $invoiceData['late_fee_total'] ?? 0);
            $invoice->update($totals);

            return $invoice->fresh(['items']);
        });
    }

    public function update(Invoice $invoice, array $invoiceData, array $items): Invoice
    {
        if (($invoiceData['status'] ?? $invoice->status) === 'void' && $invoice->status === 'paid') {
            throw ValidationException::withMessages([
                'status' => 'Paid invoices cannot be voided directly. Use payment reversal workflow.',
            ]);
        }

        $this->ensureNoDuplicatePeriod($invoiceData, $invoice->id);

        return DB::transaction(function () use ($invoice, $invoiceData, $items): Invoice {
            $invoice->update($invoiceData);
            $invoice->items()->delete();
            $totals = $this->syncItemsAndTotals($invoice, $items, $invoiceData['discount_total'] ?? 0, $invoiceData['late_fee_total'] ?? 0);
            $invoice->update($totals);

            return $invoice->fresh(['items']);
        });
    }

    private function ensureNoDuplicatePeriod(array $invoiceData, ?int $ignoreInvoiceId = null): void
    {
        $query = Invoice::query()
            ->where('tenant_id', $invoiceData['tenant_id'])
            ->where('period_start', $invoiceData['period_start'])
            ->where('period_end', $invoiceData['period_end'])
            ->where('status', '!=', 'void');

        if (! empty($invoiceData['rental_contract_id'])) {
            $query->where('rental_contract_id', $invoiceData['rental_contract_id']);
        }

        if ($ignoreInvoiceId !== null) {
            $query->whereKeyNot($ignoreInvoiceId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'period_start' => 'Duplicate invoice period for this tenant/contract.',
            ]);
        }
    }

    private function syncItemsAndTotals(Invoice $invoice, array $items, float $discountTotal, float $lateFeeTotal): array
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];
            $subtotal += $lineTotal;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'category' => $item['category'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
                'metadata' => $item['metadata'] ?? null,
            ]);
        }

        $total = round($subtotal - $discountTotal + $lateFeeTotal, 2);
        $paid = (float) $invoice->paid_amount;
        $balance = round($total - $paid, 2);

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'late_fee_total' => $lateFeeTotal,
            'total_amount' => $total,
            'balance_amount' => $balance,
        ];
    }
}
