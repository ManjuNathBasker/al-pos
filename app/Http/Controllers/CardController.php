<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Account;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $query = Card::with('settlementAccount');

        if ($request->filled('search')) {
            $query->where('bank_name', 'like', '%' . $request->search . '%')
                  ->orWhere('card_network', 'like', '%' . $request->search . '%');
        }

        $cards = $query->paginate(15)->withQueryString();

        return view('cards.index', compact('cards'));
    }

    public function create()
    {
        $accounts = Account::where('company_id', session('company_id'))->get();
        return view('cards.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name'             => 'required|string|max:255',
            'card_network'          => 'required|string|max:255',
            'card_type'             => 'required|string|max:255',
            'settlement_account_id' => 'required|exists:accounts,id',
            'service_charge'        => 'required|numeric|min:0|max:100',
            'mdr'                   => 'required|numeric|min:0|max:100',
            'processing_fee'        => 'required|numeric|min:0',
            'settlement_days'       => 'required|integer|min:0',
            'is_active'             => 'boolean',
            'notes'                 => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['company_id'] = session('company_id');

        Card::create($validated);

        return redirect()->route('cards.index')->with('success', 'Card created successfully.');
    }

    public function edit(Card $card)
    {
        $accounts = Account::where('company_id', session('company_id'))->get();
        return view('cards.edit', compact('card', 'accounts'));
    }

    public function update(Request $request, Card $card)
    {
        $validated = $request->validate([
            'bank_name'             => 'required|string|max:255',
            'card_network'          => 'required|string|max:255',
            'card_type'             => 'required|string|max:255',
            'settlement_account_id' => 'required|exists:accounts,id',
            'service_charge'        => 'required|numeric|min:0|max:100',
            'mdr'                   => 'required|numeric|min:0|max:100',
            'processing_fee'        => 'required|numeric|min:0',
            'settlement_days'       => 'required|integer|min:0',
            'is_active'             => 'boolean',
            'notes'                 => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $card->update($validated);

        return redirect()->route('cards.index')->with('success', 'Card updated successfully.');
    }

    public function destroy(Card $card)
    {
        $card->delete();
        return redirect()->route('cards.index')->with('success', 'Card deleted successfully.');
    }

    // API endpoint for fetching active cards in POS
    public function getActiveCards()
    {
        $cards = Card::where('is_active', true)->get();
        return response()->json($cards);
    }
}
