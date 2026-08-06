<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletReportController extends Controller
{
    public function index(Request $request)
    {
        $companyId = session('company_id');
        
        $query = WalletTransaction::with(['customer', 'order'])
            ->whereHas('customer', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $totalWalletBalance = Customer::where('company_id', $companyId)->sum('wallet_balance');
        $customers = Customer::where('company_id', $companyId)->orderBy('name')->get();

        return view('reports.wallet', compact('transactions', 'totalWalletBalance', 'customers'));
    }
}
