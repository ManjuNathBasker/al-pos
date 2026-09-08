<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CardType;
use Illuminate\Http\Request;

class CardTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = CardType::with('expenseAccount');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $cardTypes = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('card-types.index', compact('cardTypes'));
    }

    public function create()
    {
        $accounts = Account::where('company_id', session('company_id'))
                           ->where('status', true)
                           ->orderBy('account_name')
                           ->get();
        return view('card-types.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'commission_type'    => 'required|in:percentage,fixed',
            'commission_value'   => 'required|numeric|min:0|max:100',
            'commission_handling'=> 'required|in:ignore,auto_write_off,settlement_tracking',
            'expense_account_id' => 'nullable|exists:accounts,id',
            'status'             => 'boolean',
        ]);

        $validated['status']     = $request->boolean('status', true);
        $validated['company_id'] = session('company_id');
        $validated['created_by'] = auth()->id();

        CardType::create($validated);

        return redirect()->route('card-types.index')
                         ->with('success', 'Card Type created successfully.');
    }

    public function edit(CardType $cardType)
    {
        $accounts = Account::where('company_id', session('company_id'))
                           ->where('status', true)
                           ->orderBy('account_name')
                           ->get();
        return view('card-types.edit', compact('cardType', 'accounts'));
    }

    public function update(Request $request, CardType $cardType)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'commission_type'    => 'required|in:percentage,fixed',
            'commission_value'   => 'required|numeric|min:0|max:100',
            'commission_handling'=> 'required|in:ignore,auto_write_off,settlement_tracking',
            'expense_account_id' => 'nullable|exists:accounts,id',
            'status'             => 'boolean',
        ]);

        $validated['status']     = $request->boolean('status', true);
        $validated['updated_by'] = auth()->id();

        $cardType->update($validated);

        return redirect()->route('card-types.index')
                         ->with('success', 'Card Type updated successfully.');
    }

    public function destroy(CardType $cardType)
    {
        $cardType->delete();
        return redirect()->route('card-types.index')
                         ->with('success', 'Card Type deleted successfully.');
    }

    /**
     * API — return active card types for the current company (used by POS).
     */
    public function getActive()
    {
        $cardTypes = CardType::where('status', true)
                             ->orderBy('name')
                             ->get(['id', 'name', 'commission_type', 'commission_value', 'commission_handling', 'expense_account_id']);

        return response()->json($cardTypes);
    }
}
