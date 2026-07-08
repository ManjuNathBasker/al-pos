<?php

namespace App\Http\Controllers;

use App\Models\BankSettlement;
use App\Models\CardTransaction;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankSettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = BankSettlement::with(['cardTransaction.card', 'adjustmentEntry']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $settlements = $query->latest()->paginate(15)->withQueryString();
        
        // Fetch pending transactions to allow settling them
        $pendingTransactions = CardTransaction::where('settlement_status', 'pending')->get();

        return view('settlements.index', compact('settlements', 'pendingTransactions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_transaction_id'      => 'required|exists:card_transactions,id',
            'actual_settlement_amount' => 'required|numeric|min:0',
            'bank_charges'             => 'required|numeric|min:0',
            'processing_charges'       => 'required|numeric|min:0',
            'bank_statement_reference' => 'nullable|string|max:255',
            'settlement_date'          => 'required|date',
            'notes'                    => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $transaction = CardTransaction::findOrFail($request->card_transaction_id);

            $expectedAmount = $transaction->net_settlement_amount;
            $actualAmount = (float) $request->actual_settlement_amount;
            $bankCharges = (float) $request->bank_charges;
            $processingCharges = (float) $request->processing_charges;

            // Difference = Actual Payout + Charges - Expected Net
            $difference = round($actualAmount + $bankCharges + $processingCharges - $expectedAmount, 2);

            // Create Settlement entry
            $settlement = BankSettlement::create([
                'company_id'                 => session('company_id'),
                'card_transaction_id'        => $transaction->id,
                'bank_statement_reference'   => $request->bank_statement_reference,
                'expected_settlement_amount' => $expectedAmount,
                'actual_settlement_amount'   => $actualAmount,
                'settlement_difference'      => $difference,
                'bank_charges'               => $bankCharges,
                'processing_charges'         => $processingCharges,
                'status'                     => 'completed',
                'settlement_date'            => $request->settlement_date,
                'notes'                      => $request->notes,
                'created_by'                 => auth()->id(),
            ]);

            // Update Transaction
            $transaction->update([
                'settlement_status' => 'completed',
                'settlement_date'   => $request->settlement_date,
            ]);

            // Create Accounting Journal Entry
            $journalEntry = $this->createReconciliationJournal($settlement, $transaction);
            
            $settlement->update([
                'adjustment_entry_id' => $journalEntry->id,
            ]);

            DB::commit();
            return redirect()->route('settlements.index')->with('success', 'Bank Settlement reconciled and journal entries generated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Reconciliation failed: ' . $e->getMessage());
        }
    }

    /**
     * Create reconciliation journal entries.
     */
    private function createReconciliationJournal(BankSettlement $settlement, CardTransaction $transaction)
    {
        $companyId = session('company_id');
        $accountingService = app(\App\Services\AccountingService::class);

        // Fetch dynamic accounts or create standard system ones
        $bankAccount = $accountingService->getSystemAccount($companyId, 'Card / Bank', 'Asset', '1010');
        $cardReceivableAccount = $accountingService->getSystemAccount($companyId, 'Card Receivable', 'Asset', '1200');
        $bankChargesAccount = $accountingService->getSystemAccount($companyId, 'Bank Charges', 'Expense', '5100');
        $processingChargesAccount = $accountingService->getSystemAccount($companyId, 'Processing Charges', 'Expense', '5200');
        $discrepancyAccount = $accountingService->getSystemAccount($companyId, 'Settlement Difference', 'Expense', '5300');

        $journal = JournalEntry::create([
            'company_id'       => $companyId,
            'transaction_date' => $settlement->settlement_date->toDateString(),
            'reference_type'   => BankSettlement::class,
            'reference_id'     => $settlement->id,
            'notes'            => 'Bank Settlement Reconciliation - ' . ($settlement->bank_statement_reference ?? 'REF-' . $settlement->id),
            'created_by'       => auth()->id(),
        ]);

        // 1. Debit Bank (actual money received)
        JournalEntryItem::create([
            'journal_entry_id' => $journal->id,
            'account_id'       => $bankAccount->id,
            'debit_amount'     => $settlement->actual_settlement_amount,
            'credit_amount'    => 0,
        ]);

        // 2. Debit Bank Charges (MDR / other bank charges)
        if ($settlement->bank_charges > 0) {
            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $bankChargesAccount->id,
                'debit_amount'     => $settlement->bank_charges,
                'credit_amount'    => 0,
            ]);
        }

        // 3. Debit Processing Charges
        if ($settlement->processing_charges > 0) {
            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $processingChargesAccount->id,
                'debit_amount'     => $settlement->processing_charges,
                'credit_amount'    => 0,
            ]);
        }

        // 4. Debit/Credit Settlement Difference
        if ($settlement->settlement_difference != 0) {
            $debit = $settlement->settlement_difference < 0 ? abs($settlement->settlement_difference) : 0;
            $credit = $settlement->settlement_difference > 0 ? $settlement->settlement_difference : 0;

            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $discrepancyAccount->id,
                'debit_amount'     => $debit,
                'credit_amount'    => $credit,
            ]);
        }

        // 5. Credit Card Receivable (clear outstanding card balance)
        JournalEntryItem::create([
            'journal_entry_id' => $journal->id,
            'account_id'       => $cardReceivableAccount->id,
            'debit_amount'     => 0,
            'credit_amount'    => $settlement->expected_settlement_amount,
        ]);

        return $journal;
    }
}
