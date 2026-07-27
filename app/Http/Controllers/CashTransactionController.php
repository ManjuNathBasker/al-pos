<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\RegisterSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashTransactionController extends Controller
{
    /**
     * Add a general expense from the cash drawer.
     */
    public function addExpense(Request $request)
    {
        return $this->processTransaction($request, 'EXPENSE', 'Expense added successfully.');
    }

    /**
     * Owner withdrawal from the cash drawer.
     */
    public function ownerWithdrawal(Request $request)
    {
        return $this->processTransaction($request, 'OWNER_WITHDRAWAL', 'Owner withdrawal recorded.');
    }

    /**
     * Cash deposit into the cash drawer.
     */
    public function cashDeposit(Request $request)
    {
        return $this->processTransaction($request, 'CASH_DEPOSIT', 'Cash deposit recorded.');
    }

    private function processTransaction(Request $request, $type, $successMessage)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $openSession = RegisterSession::openForUser(Auth::id())->first();

        if (!$openSession) {
            return redirect()->back()->with('error', 'You must have an open register session to perform this action.');
        }

        try {
            DB::beginTransaction();

            CashTransaction::create([
                'register_session_id' => $openSession->id,
                'type' => $type,
                'amount' => $request->amount,
                'payment_method' => 'Cash',
                'description' => $request->description,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error recording transaction: ' . $e->getMessage());
        }
    }
}
