<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryExport;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status ?? 'all';

        $query = InventoryItem::query();

        if ($status === 'low_stock') {
            $query->whereColumn('current_stock', '<=', 'minimum_stock');
        } elseif ($status === 'out_of_stock') {
            $query->where('current_stock', '<=', 0);
        } elseif ($status === 'in_stock') {
            $query->whereColumn('current_stock', '>', 'minimum_stock');
        }

        $inventory = $query->orderBy('name')->get();

        $stats = [
            'total_items'    => $inventory->count(),
            'total_value'    => $inventory->sum(function ($item) {
                return $item->current_stock * $item->cost_price;
            }),
            'low_stock'      => $inventory->where('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0)->count(),
            'out_of_stock'   => $inventory->where('current_stock', '<=', 0)->count(),
        ];

        return view('reports.inventory', compact('inventory', 'stats', 'status'));
    }

    public function export(Request $request)
    {
        $format = $request->format ?? 'pdf';
        
        $status = $request->status ?? 'all';

        $query = InventoryItem::query();

        if ($status === 'low_stock') {
            $query->whereColumn('current_stock', '<=', 'minimum_stock');
        } elseif ($status === 'out_of_stock') {
            $query->where('current_stock', '<=', 0);
        } elseif ($status === 'in_stock') {
            $query->whereColumn('current_stock', '>', 'minimum_stock');
        }

        $inventory = $query->orderBy('name')->get();
        $company = \App\Models\Company::find(session('company_id'));

        if ($format === 'excel') {
            return Excel::download(new InventoryExport($inventory), 'inventory_report_' . now()->format('Y_m_d') . '.xlsx');
        }

        // PDF Generation
        $pdf = Pdf::loadView('reports.pdf.inventory', compact('inventory', 'company'));
        return $pdf->download('inventory_report_' . now()->format('Y_m_d') . '.pdf');
    }
}
