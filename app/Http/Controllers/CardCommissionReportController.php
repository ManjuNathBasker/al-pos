<?php

namespace App\Http\Controllers;

use App\Models\CardType;
use App\Models\Order;
use Illuminate\Http\Request;

class CardCommissionReportController extends Controller
{
    public function index(Request $request)
    {
        $companyId = session('company_id');

        $cardTypes = CardType::where('company_id', $companyId)->orderBy('name')->get();

        $query = Order::with(['cardType', 'customer'])
                      ->where('company_id', $companyId)
                      ->whereNotNull('card_type_id')
                      ->where('status', 'paid');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('card_type_id')) {
            $query->where('card_type_id', $request->card_type_id);
        }
        if ($request->filled('handling')) {
            $query->whereHas('cardType', fn($q) => $q->where('commission_handling', $request->handling));
        }

        $orders = $query->latest()->paginate(25)->withQueryString();

        // Summary totals for the filtered set
        $totalsQuery = Order::where('company_id', $companyId)
                            ->whereNotNull('card_type_id')
                            ->where('status', 'paid');

        if ($request->filled('date_from')) $totalsQuery->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $totalsQuery->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('card_type_id')) $totalsQuery->where('card_type_id', $request->card_type_id);
        if ($request->filled('handling'))  $totalsQuery->whereHas('cardType', fn($q) => $q->where('commission_handling', $request->handling));

        $totals = $totalsQuery->selectRaw('
            COUNT(*) as total_orders,
            SUM(total_amount) as total_billed,
            SUM(card_commission_amount) as total_commission,
            SUM(card_commission_tax_amount) as total_commission_tax,
            SUM(card_commission_total_deduction) as total_deduction,
            SUM(card_net_received) as total_net_received
        ')->first();

        return view('reports.card-commission', compact('orders', 'cardTypes', 'totals'));
    }
}
