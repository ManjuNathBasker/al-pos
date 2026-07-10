<?php

namespace App\Http\Controllers;

use App\Models\CardTransaction;
use Illuminate\Http\Request;

class CardTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = CardTransaction::with(['card', 'customer', 'branch', 'order']);

        if ($request->filled('search')) {
            $query->where('bank_name', 'like', '%' . $request->search . '%')
                  ->orWhere('approval_number', 'like', '%' . $request->search . '%')
                  ->orWhere('transaction_reference', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('settlement_status')) {
            $query->where('settlement_status', $request->settlement_status);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('transactions.index', compact('transactions'));
    }
}
