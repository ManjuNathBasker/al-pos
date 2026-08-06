<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingService;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $orders = $customer->orders()
            ->with(['items', 'payments', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'orders_page');

        $walletTransactions = $customer->walletTransactions()
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'wallet_page');

        // Calculate statistics
        $stats = [
            'total_orders' => $customer->orders()->count(),
            'total_spent' => $customer->orders()->where('status', 'paid')->sum('total_amount'),
            'avg_order_value' => $customer->orders()->where('status', 'paid')->avg('total_amount') ?? 0,
            'last_order_date' => $customer->orders()->latest()->value('created_at'),
        ];

        return view('customers.show', compact('customer', 'orders', 'stats', 'walletTransactions'));
    }

    public function adjustWallet(Request $request, Customer $customer, AccountingService $accountingService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $amount = (float) $request->amount;
            $type = $request->type;
            
            if ($type === 'credit') {
                $customer->increment('wallet_balance', $amount);
            } else {
                if ($customer->wallet_balance < $amount) {
                    return back()->with('error', 'Insufficient wallet balance for this deduction.');
                }
                $customer->decrement('wallet_balance', $amount);
            }

            WalletTransaction::create([
                'company_id' => $customer->company_id,
                'customer_id' => $customer->id,
                'amount' => $amount,
                'type' => $type,
                'description' => $request->description ?: 'Manual adjustment',
            ]);

            // Call Accounting Service
            $accountingService->recordManualWalletAdjustment($customer, $amount, $type, $request->description);

            DB::commit();
            return back()->with('success', 'Wallet balance adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to adjust wallet balance: ' . $e->getMessage());
        }
    }
}
