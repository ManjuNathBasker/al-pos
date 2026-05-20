<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');

        // Summary stats
        $stats = [
            'total_purchases' => Purchase::whereBetween('purchase_date', [$startDate, $endDate])->count(),
            'total_amount'    => Purchase::whereBetween('purchase_date', [$startDate, $endDate])->sum('total_amount'),
            'total_paid'      => DB::table('purchase_payments')
                                    ->where('company_id', session('company_id'))
                                    ->whereBetween('payment_date', [$startDate, $endDate])
                                    ->sum('paid_amount'),
            'total_due'       => Purchase::where('payment_status', '!=', 'paid')->sum(DB::raw('total_amount - (SELECT COALESCE(SUM(paid_amount), 0) FROM purchase_payments WHERE purchase_payments.purchase_id = purchases.id)')),
        ];

        // Top Suppliers
        $topSuppliers = Supplier::withCount('purchases')
            ->withSum('purchases', 'total_amount')
            ->orderByDesc('purchases_sum_total_amount')
            ->limit(5)
            ->get();

        // Monthly purchases chart data
        $monthlyData = Purchase::selectRaw('DATE_FORMAT(purchase_date, "%Y-%m") as month, SUM(total_amount) as total')
            ->whereBetween('purchase_date', [now()->subMonths(6), now()])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('reports.purchases', compact('stats', 'topSuppliers', 'monthlyData', 'startDate', 'endDate'));
    }
}
