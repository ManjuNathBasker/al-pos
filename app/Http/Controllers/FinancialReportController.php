<?php

namespace App\Http\Controllers;

use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProfitLossExport;
use App\Exports\BalanceSheetExport;

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

    public function exportProfitLoss(Request $request)
    {
        $format = $request->format ?? 'pdf';
        
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $report = $this->reportService->getProfitAndLoss(
            auth()->user()->company_id,
            $startDate,
            $endDate
        );
        $company = \App\Models\Company::find(session('company_id'));

        if ($format === 'excel') {
            return Excel::download(new ProfitLossExport($report), 'profit_loss_' . $startDate . '_to_' . $endDate . '.xlsx');
        }

        $pdf = Pdf::loadView('reports.pdf.profit_loss', compact('report', 'startDate', 'endDate', 'company'));
        return $pdf->download('profit_loss_' . $startDate . '_to_' . $endDate . '.pdf');
    }

    public function exportBalanceSheet(Request $request)
    {
        $format = $request->format ?? 'pdf';
        $endDate = $request->input('end_date', now()->toDateString());

        $report = $this->reportService->getBalanceSheet(
            auth()->user()->company_id,
            $endDate
        );
        $company = \App\Models\Company::find(session('company_id'));

        if ($format === 'excel') {
            return Excel::download(new BalanceSheetExport($report), 'balance_sheet_' . $endDate . '.xlsx');
        }

        $pdf = Pdf::loadView('reports.pdf.balance_sheet', compact('report', 'endDate', 'company'));
        return $pdf->download('balance_sheet_' . $endDate . '.pdf');
    }
}
