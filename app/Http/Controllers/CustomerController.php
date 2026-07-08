<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

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
            ->paginate(15);

        // Calculate statistics
        $stats = [
            'total_orders' => $customer->orders()->count(),
            'total_spent' => $customer->orders()->where('status', 'paid')->sum('total_amount'),
            'avg_order_value' => $customer->orders()->where('status', 'paid')->avg('total_amount') ?? 0,
            'last_order_date' => $customer->orders()->latest()->value('created_at'),
        ];

        return view('customers.show', compact('customer', 'orders', 'stats'));
    }
}
