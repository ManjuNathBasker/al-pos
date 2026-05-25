<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index()
    {
        $expenses = Expense::with(['category', 'account'])->latest()->paginate(20);
        $categories = ExpenseCategory::all();
        $accounts = Account::whereIn('account_type', ['Asset', 'Liability'])->where('status', true)->get();

        return view('accounting.expenses.index', compact('expenses', 'categories', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        DB::transaction(function () use ($validated) {
            $expense = Expense::create($validated);
            $this->accountingService->recordExpense($expense);
        });

        return back()->with('success', 'Expense recorded successfully.');
    }
}
