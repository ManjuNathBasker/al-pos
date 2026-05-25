<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['items.account', 'creator'])->latest();

        if ($request->filled('search')) {
            $query->where('journal_number', 'like', '%' . $request->search . '%')
                  ->orWhere('notes', 'like', '%' . $request->search . '%');
        }

        $entries = $query->paginate(20);
        $accounts = Account::where('status', true)->get();

        return view('accounting.journals.index', compact('entries', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'notes' => 'required|string',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.debit_amount' => 'required|numeric|min:0',
            'items.*.credit_amount' => 'required|numeric|min:0',
        ]);

        // Validate Balance
        $totalDebit = collect($request->items)->sum('debit_amount');
        $totalCredit = collect($request->items)->sum('credit_amount');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->with('error', 'Debits and Credits must balance.');
        }

        if ($totalDebit == 0) {
            return back()->with('error', 'Transaction amounts cannot be zero.');
        }

        DB::transaction(function () use ($request) {
            $journal = JournalEntry::create([
                'company_id' => auth()->user()->company_id,
                'transaction_date' => $request->transaction_date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                if ($item['debit_amount'] > 0 || $item['credit_amount'] > 0) {
                    JournalEntryItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $item['account_id'],
                        'debit_amount' => $item['debit_amount'],
                        'credit_amount' => $item['credit_amount'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Journal entry created successfully.');
    }
}
