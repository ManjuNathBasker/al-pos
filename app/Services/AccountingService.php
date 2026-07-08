<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Get or create a system account for a given company.
     */
    public function getSystemAccount($companyId, $accountName, $accountType, $accountCode = null)
    {
        return Account::firstOrCreate(
            [
                'company_id' => $companyId,
                'account_name' => $accountName,
            ],
            [
                'account_code' => $accountCode,
                'account_type' => $accountType,
                'is_system' => true,
                'status' => true,
            ]
        );
    }

    /**
     * Record a POS Sale.
     * Debit: Cash/Bank (Amount Paid)
     * Credit: Sales (Subtotal - Discount)
     * Credit: Tax Payable (Tax Amount)
     */
    public function recordSale(Order $order)
    {
        DB::transaction(function () use ($order) {
            $companyId = $order->company_id;
            
            $cashAccount = $this->getSystemAccount($companyId, 'Cash', 'Asset', '1000');
            $salesAccount = $this->getSystemAccount($companyId, 'Sales', 'Income', '4000');
            
            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'transaction_date' => $order->created_at->toDateString(),
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'notes' => 'POS Sale - ' . $order->order_number,
                'created_by' => $order->created_by ?? auth()->id(),
            ]);

            // Debit Specific Accounts based on dynamic payments
            foreach ($order->payments as $payment) {
                if ($payment->payment_method === 'wallet') {
                    $walletAccount = $this->getSystemAccount($companyId, 'Customer Wallet', 'Liability', '2200');
                    JournalEntryItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $walletAccount->id,
                        'debit_amount' => $payment->amount,
                        'credit_amount' => 0,
                    ]);
                } else if (is_numeric($payment->payment_method)) {
                    $account = Account::find($payment->payment_method);
                    if ($account) {
                        // Check if this payment corresponds to a Card Transaction
                        $cardTx = \App\Models\CardTransaction::where('order_id', $order->id)
                            ->whereHas('card', function($q) use ($account) {
                                $q->where('settlement_account_id', $account->id);
                            })->first();

                        if ($cardTx) {
                            // Card Receivable account
                            $cardReceivableAccount = $this->getSystemAccount($companyId, 'Card Receivable', 'Asset', '1050');
                            JournalEntryItem::create([
                                'journal_entry_id' => $journal->id,
                                'account_id' => $cardReceivableAccount->id,
                                'debit_amount' => $cardTx->net_settlement_amount,
                                'credit_amount' => 0,
                            ]);

                            // Bank Charges (MDR)
                            $taxableBase = max(0, $cardTx->gross_amount - $cardTx->discount_amount);
                            $mdrAmount = ($taxableBase + $cardTx->service_charge_amount) * ($cardTx->card->mdr / 100);
                            if ($mdrAmount > 0) {
                                $bankChargesAccount = $this->getSystemAccount($companyId, 'Bank Charges', 'Expense', '5100');
                                JournalEntryItem::create([
                                    'journal_entry_id' => $journal->id,
                                    'account_id' => $bankChargesAccount->id,
                                    'debit_amount' => $mdrAmount,
                                    'credit_amount' => 0,
                                ]);
                            }

                            // Processing Charges
                            if ($cardTx->processing_fee_amount > 0) {
                                $processingChargesAccount = $this->getSystemAccount($companyId, 'Processing Charges', 'Expense', '5200');
                                JournalEntryItem::create([
                                    'journal_entry_id' => $journal->id,
                                    'account_id' => $processingChargesAccount->id,
                                    'debit_amount' => $cardTx->processing_fee_amount,
                                    'credit_amount' => 0,
                                ]);
                            }

                            // Offer Expense (Merchant Discount Share)
                            if ($cardTx->merchant_discount_share > 0) {
                                $offerExpenseAccount = $this->getSystemAccount($companyId, 'Offer Expense', 'Expense', '5300');
                                JournalEntryItem::create([
                                    'journal_entry_id' => $journal->id,
                                    'account_id' => $offerExpenseAccount->id,
                                    'debit_amount' => $cardTx->merchant_discount_share,
                                    'credit_amount' => 0,
                                ]);
                            }

                            // Cashback Expense
                            if ($cardTx->cashback_amount > 0) {
                                $cashbackExpenseAccount = $this->getSystemAccount($companyId, 'Cashback Expense', 'Expense', '5400');
                                JournalEntryItem::create([
                                    'journal_entry_id' => $journal->id,
                                    'account_id' => $cashbackExpenseAccount->id,
                                    'debit_amount' => $cardTx->cashback_amount,
                                    'credit_amount' => 0,
                                ]);
                            }

                            // Service Charge Income
                            if ($cardTx->service_charge_amount > 0) {
                                $serviceChargeIncomeAccount = $this->getSystemAccount($companyId, 'Service Charge Income', 'Income', '4100');
                                JournalEntryItem::create([
                                    'journal_entry_id' => $journal->id,
                                    'account_id' => $serviceChargeIncomeAccount->id,
                                    'debit_amount' => 0,
                                    'credit_amount' => $cardTx->service_charge_amount,
                                ]);
                            }
                        } else {
                            // Standard bank payment
                            JournalEntryItem::create([
                                'journal_entry_id' => $journal->id,
                                'account_id' => $account->id,
                                'debit_amount' => $payment->amount,
                                'credit_amount' => 0,
                            ]);
                        }
                    }
                } else {
                    // Legacy support
                    $accountName = 'Cash';
                    $accountCode = '1000';
                    if ($payment->payment_method === 'card') { $accountName = 'Card / Bank'; $accountCode = '1010'; }
                    if ($payment->payment_method === 'upi') { $accountName = 'UPI / Digital'; $accountCode = '1020'; }
                    
                    $acc = $this->getSystemAccount($companyId, $accountName, 'Asset', $accountCode);
                    JournalEntryItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $acc->id,
                        'debit_amount' => $payment->amount,
                        'credit_amount' => 0,
                    ]);
                }
            }

            // If change was returned, it is added to customer wallet, increasing liability (Credit)
            if ($order->change_returned > 0) {
                $walletAccount = $this->getSystemAccount($companyId, 'Customer Wallet', 'Liability', '2200');
                JournalEntryItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $walletAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $order->change_returned,
                ]);
            }
            
            // Credit Sales
            $netSales = $order->subtotal - $order->discount_amount;
            if ($netSales > 0) {
                JournalEntryItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $salesAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $netSales,
                ]);
            }
            
            // Credit Tax
            if ($order->tax_amount > 0) {
                $taxAccount = $this->getSystemAccount($companyId, 'Tax Payable', 'Liability', '2100');
                JournalEntryItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $taxAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $order->tax_amount,
                ]);
            }
        });
    }

    /**
     * Record a Purchase
     * Debit: Purchase/Inventory
     * Credit: Supplier Payable
     */
    public function recordPurchase(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $companyId = $purchase->company_id;
            
            $purchaseAccount = $this->getSystemAccount($companyId, 'Purchases', 'Expense', '5000');
            $payableAccount = $this->getSystemAccount($companyId, 'Supplier Payable', 'Liability', '2000');
            
            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'transaction_date' => $purchase->purchase_date ?? now()->toDateString(),
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
                'notes' => 'Purchase - ' . $purchase->purchase_number,
                'created_by' => $purchase->created_by ?? auth()->id(),
            ]);

            // Debit Purchase
            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $purchaseAccount->id,
                'debit_amount' => $purchase->total_amount,
                'credit_amount' => 0,
            ]);
            
            // Credit Payable
            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $payableAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $purchase->total_amount,
            ]);
        });
    }

    /**
     * Record Supplier Payment
     * Debit: Supplier Payable
     * Credit: Cash/Bank
     */
    public function recordSupplierPayment(PurchasePayment $payment)
    {
        DB::transaction(function () use ($payment) {
            $companyId = $payment->company_id;
            
            $payableAccount = $this->getSystemAccount($companyId, 'Supplier Payable', 'Liability', '2000');
            $cashAccount = $this->getSystemAccount($companyId, 'Cash', 'Asset', '1000');
            
            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'transaction_date' => $payment->payment_date ?? now()->toDateString(),
                'reference_type' => PurchasePayment::class,
                'reference_id' => $payment->id,
                'notes' => 'Supplier Payment for Purchase ' . ($payment->purchase->purchase_number ?? ''),
                'created_by' => $payment->created_by ?? auth()->id(),
            ]);

            // Debit Payable
            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $payableAccount->id,
                'debit_amount' => $payment->paid_amount,
                'credit_amount' => 0,
            ]);
            
            // Credit Cash
            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $cashAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $payment->paid_amount,
            ]);
        });
    }

    /**
     * Record Expense
     * Debit: Specific Expense Account
     * Credit: Cash/Bank
     */
    public function recordExpense(Expense $expense)
    {
        DB::transaction(function () use ($expense) {
            $companyId = $expense->company_id;
            
            $expenseAccount = $this->getSystemAccount($companyId, $expense->category->name ?? 'General Expense', 'Expense');
            
            $creditAccountId = $expense->account_id;
            if (!$creditAccountId) {
                $cashAccount = $this->getSystemAccount($companyId, 'Cash', 'Asset', '1000');
                $creditAccountId = $cashAccount->id;
            }
            
            $journal = JournalEntry::create([
                'company_id' => $companyId,
                'transaction_date' => $expense->expense_date,
                'reference_type' => Expense::class,
                'reference_id' => $expense->id,
                'notes' => 'Expense - ' . ($expense->notes ?? 'Recorded'),
                'created_by' => auth()->id(),
            ]);

            // Debit Expense
            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $expenseAccount->id,
                'debit_amount' => $expense->amount,
                'credit_amount' => 0,
            ]);
            
            // Credit Account (Cash/Bank)
            JournalEntryItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $creditAccountId,
                'debit_amount' => 0,
                'credit_amount' => $expense->amount,
            ]);
        });
    }
}
