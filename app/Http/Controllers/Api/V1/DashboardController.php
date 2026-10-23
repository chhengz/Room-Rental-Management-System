<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\RentalContract;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $from = $request->input('from_date', now()->startOfMonth()->toDateString());
        $to = $request->input('to_date', now()->endOfMonth()->toDateString());

        $roomsTotal = Room::count();
        $occupied = Room::where('status', 'occupied')->count();
        $available = Room::where('status', 'available')->count();
        $reserved = Room::where('status', 'reserved')->count();
        $maintenance = Room::where('status', 'maintenance')->count();

        $invoices = Invoice::whereBetween('issue_date', [$from, $to])->where('status', '!=', 'void');
        $periodInvoices = (clone $invoices)->sum('total_amount');
        $periodOutstanding = (clone $invoices)->sum('balance_amount');
        $periodCollected = Payment::whereBetween('payment_date', [$from, $to])->sum('allocated_amount');
        $periodExpenses = Expense::whereBetween('expense_date', [$from, $to])->sum('amount');
        $utilities = MeterReading::whereBetween('reading_date', [$from, $to])->sum('charge_amount');

        $overdueInvoices = Invoice::where('status', '!=', 'void')
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('balance_amount', '>', 0)
            ->count();

        $contractExpirations = RentalContract::whereDate('end_date', '>=', now()->toDateString())
            ->whereDate('end_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        return $this->success([
            'rooms' => [
                'total' => $roomsTotal,
                'occupied' => $occupied,
                'available' => $available,
                'reserved' => $reserved,
                'maintenance' => $maintenance,
                'occupancy_rate' => $roomsTotal > 0 ? round(($occupied / $roomsTotal) * 100, 2) : 0,
            ],
            'finance' => [
                'invoices_total' => round((float) $periodInvoices, 2),
                'collected_total' => round((float) $periodCollected, 2),
                'outstanding_total' => round((float) $periodOutstanding, 2),
                'expenses_total' => round((float) $periodExpenses, 2),
                'utilities_total' => round((float) $utilities, 2),
                'net_operating_result' => round((float) $periodCollected - (float) $periodExpenses, 2),
                'overdue_invoice_count' => $overdueInvoices,
            ],
            'contracts' => [
                'upcoming_expiration_30_days' => $contractExpirations,
            ],
        ]);
    }
}
