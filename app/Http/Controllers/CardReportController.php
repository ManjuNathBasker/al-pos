<?php

namespace App\Http\Controllers;

use App\Models\CardTransaction;
use App\Models\BankSettlement;
use App\Models\Card;
use Illuminate\Http\Request;

class CardReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $settlementStatus = $request->settlement_status ?? 'all';
        $cardId = $request->card_id ?? 'all';

        // 1. Card Transactions Query
        $txQuery = CardTransaction::with(['card', 'order', 'customer', 'branch'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($settlementStatus !== 'all') {
            $txQuery->where('settlement_status', $settlementStatus);
        }

        if ($cardId !== 'all') {
            $txQuery->where('card_id', $cardId);
        }

        $transactions = $txQuery->orderBy('created_at', 'desc')->get();

        // 2. Summary stats for card transactions
        $stats = [
            'gross_sales' => $transactions->sum('gross_amount'),
            'discounts' => $transactions->sum('discount_amount'),
            'cashback' => $transactions->sum('cashback_amount'),
            'service_charges' => $transactions->sum('service_charge_amount'),
            'mdr' => 0.00,
            'processing_fees' => $transactions->sum('processing_fee_amount'),
            'net_settlement' => $transactions->sum('net_settlement_amount'),
            'pending_settlement' => $transactions->where('settlement_status', 'pending')->sum('net_settlement_amount'),
            'settled' => $transactions->where('settlement_status', 'settled')->sum('net_settlement_amount'),
        ];
        
        // Recalculate MDR precisely from card transactions data
        $stats['mdr'] = $transactions->sum(function($tx) {
            $taxableBase = max(0, $tx->gross_amount - $tx->discount_amount);
            return ($taxableBase + $tx->service_charge_amount) * (($tx->card->mdr ?? 0) / 100);
        });

        // 3. Bank Settlements list
        $settlements = BankSettlement::with(['card', 'cardTransactions'])
            ->whereBetween('settlement_date', [$startDate, $endDate])
            ->orderBy('settlement_date', 'desc')
            ->get();

        $cards = Card::get();

        return view('reports.cards', compact('transactions', 'settlements', 'stats', 'cards', 'startDate', 'endDate', 'settlementStatus', 'cardId'));
    }
}
