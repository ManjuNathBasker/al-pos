<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $status = $request->status ?? 'all';
        $serviceType = $request->service_type ?? 'all';

        $query = Order::with(['customer', 'user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($serviceType !== 'all') {
            $query->where('service_type', $serviceType);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $stats = [
            'total_orders' => $orders->count(),
            'total_sales'  => $orders->sum('total_amount'),
            'total_paid'   => $orders->where('status', 'paid')->sum('total_paid'),
            'total_tax'    => $orders->sum('tax_amount'),
        ];

        return view('reports.sales', compact('orders', 'stats', 'startDate', 'endDate', 'status', 'serviceType'));
    }

    public function export(Request $request)
    {
        $format = $request->format ?? 'pdf';
        
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $status = $request->status ?? 'all';
        $serviceType = $request->service_type ?? 'all';

        $query = Order::with(['customer', 'user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($serviceType !== 'all') {
            $query->where('service_type', $serviceType);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        $company = \App\Models\Company::find(session('company_id'));

        if ($format === 'excel') {
            return Excel::download(new SalesExport($orders), 'sales_report_' . $startDate . '_to_' . $endDate . '.xlsx');
        }

        // PDF Generation
        $pdf = Pdf::loadView('reports.pdf.sales', compact('orders', 'startDate', 'endDate', 'company'));
        return $pdf->download('sales_report_' . $startDate . '_to_' . $endDate . '.pdf');
    }
}
