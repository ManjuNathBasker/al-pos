<?php

namespace App\Http\Controllers;

use App\Services\FinancialReportService;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    protected $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function profitAndLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $report = $this->reportService->getProfitAndLoss(
            auth()->user()->company_id,
            $startDate,
            $endDate
        );

        return view('accounting.reports.profit_loss', compact('report', 'startDate', 'endDate'));
    }

    public function balanceSheet(Request $request)
    {
        $endDate = $request->input('end_date', now()->toDateString());

        $report = $this->reportService->getBalanceSheet(
            auth()->user()->company_id,
            $endDate
        );

        return view('accounting.reports.balance_sheet', compact('report', 'endDate'));
    }
}
