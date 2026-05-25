<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    public function getProfitAndLoss($companyId, $startDate = null, $endDate = null)
    {
        $incomeAccounts = Account::where('company_id', $companyId)
            ->where('account_type', 'Income')
            ->get();
            
        $expenseAccounts = Account::where('company_id', $companyId)
            ->where('account_type', 'Expense')
            ->get();
            
        $totalIncome = 0;
        $totalExpense = 0;
        
        $incomeData = [];
        $expenseData = [];

        foreach ($incomeAccounts as $account) {
            $balance = $this->calculateAccountBalancePeriod($account, $startDate, $endDate);
            if ($balance > 0) {
                $incomeData[] = ['name' => $account->account_name, 'amount' => $balance];
                $totalIncome += $balance;
            }
        }
        
        foreach ($expenseAccounts as $account) {
            $balance = $this->calculateAccountBalancePeriod($account, $startDate, $endDate);
            if ($balance > 0) {
                $expenseData[] = ['name' => $account->account_name, 'amount' => $balance];
                $totalExpense += $balance;
            }
        }
        
        return [
            'income' => $incomeData,
            'expenses' => $expenseData,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit' => $totalIncome - $totalExpense
        ];
    }
    
    public function getBalanceSheet($companyId, $endDate = null)
    {
        $assetAccounts = Account::where('company_id', $companyId)
            ->where('account_type', 'Asset')
            ->get();
            
        $liabilityAccounts = Account::where('company_id', $companyId)
            ->where('account_type', 'Liability')
            ->get();
            
        $equityAccounts = Account::where('company_id', $companyId)
            ->where('account_type', 'Equity')
            ->get();
            
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;
        
        $assetData = [];
        $liabilityData = [];
        $equityData = [];
        
        foreach ($assetAccounts as $account) {
            $balance = $this->calculateAccountBalancePeriod($account, null, $endDate);
            if ($balance > 0) {
                $assetData[] = ['name' => $account->account_name, 'amount' => $balance];
                $totalAssets += $balance;
            }
        }
        
        foreach ($liabilityAccounts as $account) {
            $balance = $this->calculateAccountBalancePeriod($account, null, $endDate);
            if ($balance > 0) {
                $liabilityData[] = ['name' => $account->account_name, 'amount' => $balance];
                $totalLiabilities += $balance;
            }
        }
        
        foreach ($equityAccounts as $account) {
            $balance = $this->calculateAccountBalancePeriod($account, null, $endDate);
            if ($balance != 0) {
                $equityData[] = ['name' => $account->account_name, 'amount' => $balance];
                $totalEquity += $balance;
            }
        }
        
        $pl = $this->getProfitAndLoss($companyId, null, $endDate);
        $totalEquity += $pl['net_profit']; // Add retained earnings
        $equityData[] = ['name' => 'Retained Earnings (Net Profit)', 'amount' => $pl['net_profit']];
        
        return [
            'assets' => $assetData,
            'liabilities' => $liabilityData,
            'equity' => $equityData,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
        ];
    }
    
    private function calculateAccountBalancePeriod(Account $account, $startDate = null, $endDate = null)
    {
        $query = $account->journalItems();
        
        if ($startDate || $endDate) {
            $query->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                if ($startDate) $q->where('transaction_date', '>=', $startDate);
                if ($endDate) $q->where('transaction_date', '<=', $endDate);
            });
        }
        
        $debits = $query->sum('debit_amount');
        $credits = $query->sum('credit_amount');
        
        if (in_array($account->account_type, ['Asset', 'Expense'])) {
            return $account->opening_balance + $debits - $credits;
        } else {
            return $account->opening_balance + $credits - $debits;
        }
    }
}
