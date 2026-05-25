<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('parent')->latest()->paginate(20);
        
        // Calculate current balances dynamically for display
        $accounts->getCollection()->transform(function ($account) {
            $account->current_balance = $account->calculateBalance();
            return $account;
        });

        $parentAccounts = Account::whereNull('parent_account_id')->get();
        return view('accounting.accounts.index', compact('accounts', 'parentAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_code' => 'nullable|string|max:50',
            'account_type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'parent_account_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'numeric|min:0',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        Account::create($validated);

        return back()->with('success', 'Account created successfully.');
    }

    public function update(Request $request, Account $account)
    {
        if ($account->is_system) {
            return back()->with('error', 'Cannot modify system accounts.');
        }

        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_code' => 'nullable|string|max:50',
            'account_type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'parent_account_id' => 'nullable|exists:accounts,id',
            'status' => 'boolean',
        ]);

        $account->update($validated);

        return back()->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        if ($account->is_system) {
            return back()->with('error', 'Cannot delete system accounts.');
        }

        if ($account->journalItems()->exists()) {
            return back()->with('error', 'Cannot delete account with existing transactions.');
        }

        $account->delete();
        return back()->with('success', 'Account deleted successfully.');
    }
}
