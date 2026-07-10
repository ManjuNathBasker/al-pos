<?php

namespace App\Http\Controllers;

use App\Models\CardTransaction;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ServiceChargeReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $cardId = $request->card_id ?? 'all';

        $txQuery = CardTransaction::with(['card', 'order', 'branch'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($cardId !== 'all') {
            $txQuery->where('card_id', $cardId);
        }

        $transactions = $txQuery->orderBy('created_at', 'desc')->get();

        // Summary Stats
        $totalServiceCharge = $transactions->sum('service_charge_amount');
        $totalGross = $transactions->sum('gross_amount');
        $totalMDR = $transactions->sum(function ($tx) {
            $taxableBase = max(0, $tx->gross_amount - $tx->discount_amount);
            return ($taxableBase + $tx->service_charge_amount) * (($tx->card->mdr ?? 0) / 100);
        });
        $totalProcessingFees = $transactions->sum('processing_fee_amount');
        $totalBankDeductions = $totalMDR + $totalProcessingFees;
        $netFromServiceCharge = $totalServiceCharge - $totalBankDeductions;
        $txCount = $transactions->count();
        $avgChargeRate = $totalGross > 0 ? ($totalServiceCharge / $totalGross) * 100 : 0;

        $stats = [
            'total_service_charge' => $totalServiceCharge,
            'total_gross'          => $totalGross,
            'total_mdr'            => $totalMDR,
            'total_processing_fees'=> $totalProcessingFees,
            'total_bank_deductions'=> $totalBankDeductions,
            'net_from_service_charge' => $netFromServiceCharge,
            'tx_count'             => $txCount,
            'avg_charge_rate'      => $avgChargeRate,
        ];

        // Bank-wise breakdown
        $bankBreakdown = $transactions->groupBy(function ($tx) {
            return $tx->bank_name . ' — ' . ($tx->card->card_network ?? '') . ' (' . ($tx->card->card_type ?? '') . ')';
        })->map(function ($group, $key) {
            $sc = $group->sum('service_charge_amount');
            $gross = $group->sum('gross_amount');
            $mdr = $group->sum(function ($tx) {
                $base = max(0, $tx->gross_amount - $tx->discount_amount);
                return ($base + $tx->service_charge_amount) * (($tx->card->mdr ?? 0) / 100);
            });
            $fees = $group->sum('processing_fee_amount');
            return [
                'label'           => $key,
                'card_id'         => $group->first()->card_id,
                'tx_count'        => $group->count(),
                'gross'           => $gross,
                'service_charge'  => $sc,
                'charge_rate'     => $group->first()->card->service_charge ?? 0,
                'mdr'             => $mdr,
                'processing_fees' => $fees,
                'bank_deductions' => $mdr + $fees,
                'net'             => $sc - $mdr - $fees,
            ];
        })->sortByDesc('service_charge')->values();

        $cards = Card::get();

        return view('reports.service-charges', compact(
            'transactions', 'stats', 'bankBreakdown', 'cards',
            'startDate', 'endDate', 'cardId'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $cardId = $request->card_id ?? 'all';

        $txQuery = CardTransaction::with(['card', 'order', 'branch'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($cardId !== 'all') {
            $txQuery->where('card_id', $cardId);
        }

        $transactions = $txQuery->orderBy('created_at', 'desc')->get();

        $csvData = "Date,Order #,Bank,Network,Type,Gross Amount,Discount,Taxable Base,Service Charge %,Service Charge Amt,MDR %,MDR Amt,Processing Fee,Total Bank Deductions,Net from Service Charge,Settlement Status\n";

        foreach ($transactions as $tx) {
            $taxableBase = max(0, $tx->gross_amount - $tx->discount_amount);
            $mdr = ($taxableBase + $tx->service_charge_amount) * (($tx->card->mdr ?? 0) / 100);
            $bankDeductions = $mdr + $tx->processing_fee_amount;
            $net = $tx->service_charge_amount - $bankDeductions;

            $csvData .= implode(',', [
                $tx->created_at->format('Y-m-d H:i'),
                $tx->order->order_number ?? $tx->order_id,
                '"' . $tx->bank_name . '"',
                $tx->card->card_network ?? '',
                $tx->card->card_type ?? '',
                number_format($tx->gross_amount, 2),
                number_format($tx->discount_amount, 2),
                number_format($taxableBase, 2),
                number_format($tx->card->service_charge ?? 0, 2),
                number_format($tx->service_charge_amount, 2),
                number_format($tx->card->mdr ?? 0, 2),
                number_format($mdr, 2),
                number_format($tx->processing_fee_amount, 2),
                number_format($bankDeductions, 2),
                number_format($net, 2),
                ucfirst($tx->settlement_status),
            ]) . "\n";
        }

        $filename = 'service-charge-report-' . $startDate . '-to-' . $endDate . '.csv';

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
