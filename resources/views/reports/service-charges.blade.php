@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Service Charge Report</h2>
        <p class="mt-1 text-sm text-slate-500">Track service charges collected on card payments, bank deductions, and net earnings.</p>
    </div>
    
    <div class="flex flex-wrap items-center gap-2">
        <form action="{{ route('reports.service-charges') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-slate-200 text-sm px-3 py-2">
            <span class="text-slate-400">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-slate-200 text-sm px-3 py-2">
            
            <select name="card_id" class="rounded-lg border-slate-200 text-sm px-3 py-2">
                <option value="all">All Cards</option>
                @foreach($cards as $card)
                    <option value="{{ $card->id }}" {{ $cardId == $card->id ? 'selected' : '' }}>
                        {{ $card->bank_name }} - {{ $card->card_network }} ({{ $card->card_type }})
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="p-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900" title="Filter">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </button>
        </form>
        <a href="{{ route('reports.service-charges.export', ['start_date' => $startDate, 'end_date' => $endDate, 'card_id' => $cardId]) }}" 
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white font-bold text-sm rounded-lg hover:bg-emerald-700 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Export CSV
        </a>
    </div>
</div>

{{-- Summary Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-4 mb-8">
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Transactions</div>
        <div class="text-2xl font-black text-slate-900">{{ $stats['tx_count'] }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gross Card Sales</div>
        <div class="text-2xl font-black text-slate-900">${{ number_format($stats['total_gross'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-emerald-100 bg-emerald-50/50">
        <div class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Service Charges Collected</div>
        <div class="text-2xl font-black text-emerald-600">${{ number_format($stats['total_service_charge'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-amber-100 bg-amber-50/50">
        <div class="text-[10px] font-bold text-amber-500 uppercase tracking-widest mb-1">Bank Deductions (MDR+Fees)</div>
        <div class="text-2xl font-black text-amber-600">${{ number_format($stats['total_bank_deductions'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 {{ $stats['net_from_service_charge'] >= 0 ? 'bg-blue-50/50 border-blue-100' : 'bg-red-50/50 border-red-100' }}">
        <div class="text-[10px] font-bold {{ $stats['net_from_service_charge'] >= 0 ? 'text-blue-500' : 'text-red-500' }} uppercase tracking-widest mb-1">Net from Service Charges</div>
        <div class="text-2xl font-black {{ $stats['net_from_service_charge'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">${{ number_format($stats['net_from_service_charge'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Avg Charge Rate</div>
        <div class="text-2xl font-black text-slate-700">{{ number_format($stats['avg_charge_rate'], 2) }}%</div>
    </div>
</div>

<div class="space-y-8">
    {{-- Bank-wise Breakdown --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-200">
            <h3 class="font-bold text-slate-700">Bank-wise Service Charge Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Bank / Card Type</th>
                        <th class="px-6 py-3 text-center">Transactions</th>
                        <th class="px-6 py-3 text-center">Charge Rate</th>
                        <th class="px-6 py-3 text-right">Gross Sales</th>
                        <th class="px-6 py-3 text-right">Service Charge</th>
                        <th class="px-6 py-3 text-right">MDR</th>
                        <th class="px-6 py-3 text-right">Proc. Fees</th>
                        <th class="px-6 py-3 text-right">Bank Deductions</th>
                        <th class="px-6 py-3 text-right">Net Earning</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($bankBreakdown as $row)
                    <tr class="hover:bg-slate-50/30">
                        <td class="px-6 py-4 font-bold text-slate-800">{{ $row['label'] }}</td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-600">{{ $row['tx_count'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">{{ number_format($row['charge_rate'], 2) }}%</span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-700">${{ number_format($row['gross'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-600">${{ number_format($row['service_charge'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-amber-600">${{ number_format($row['mdr'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-600">${{ number_format($row['processing_fees'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-amber-700">${{ number_format($row['bank_deductions'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-black {{ $row['net'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">${{ number_format($row['net'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-500 font-medium">No card transactions in this date range.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($bankBreakdown->count() > 0)
                <tfoot class="bg-slate-50">
                    <tr class="font-black text-sm border-t-2 border-slate-200">
                        <td class="px-6 py-3 text-slate-800">TOTAL</td>
                        <td class="px-6 py-3 text-center text-slate-700">{{ $stats['tx_count'] }}</td>
                        <td class="px-6 py-3 text-center text-slate-700">{{ number_format($stats['avg_charge_rate'], 2) }}%</td>
                        <td class="px-6 py-3 text-right text-slate-800">${{ number_format($stats['total_gross'], 2) }}</td>
                        <td class="px-6 py-3 text-right text-emerald-600">${{ number_format($stats['total_service_charge'], 2) }}</td>
                        <td class="px-6 py-3 text-right text-amber-600">${{ number_format($stats['total_mdr'], 2) }}</td>
                        <td class="px-6 py-3 text-right text-slate-600">${{ number_format($stats['total_processing_fees'], 2) }}</td>
                        <td class="px-6 py-3 text-right text-amber-700">${{ number_format($stats['total_bank_deductions'], 2) }}</td>
                        <td class="px-6 py-3 text-right {{ $stats['net_from_service_charge'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">${{ number_format($stats['net_from_service_charge'], 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Detailed Transaction Log --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-700">Transaction Detail</h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $transactions->count() }} transaction(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Date / Order</th>
                        <th class="px-6 py-3">Bank / Card</th>
                        <th class="px-6 py-3 text-right">Gross Amt</th>
                        <th class="px-6 py-3 text-right">Discount</th>
                        <th class="px-6 py-3 text-right">Taxable Base</th>
                        <th class="px-6 py-3 text-center">Svc %</th>
                        <th class="px-6 py-3 text-right">Svc Charge</th>
                        <th class="px-6 py-3 text-right">MDR + Fees</th>
                        <th class="px-6 py-3 text-right">Net</th>
                        <th class="px-6 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($transactions as $tx)
                    @php
                        $taxableBase = max(0, $tx->gross_amount - $tx->discount_amount);
                        $mdr = ($taxableBase + $tx->service_charge_amount) * (($tx->card->mdr ?? 0) / 100);
                        $bankTotal = $mdr + $tx->processing_fee_amount;
                        $net = $tx->service_charge_amount - $bankTotal;
                    @endphp
                    <tr class="hover:bg-slate-50/30">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $tx->order->order_number ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('M d, Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-700">{{ $tx->bank_name }}</div>
                            <div class="text-xs text-slate-500">{{ $tx->card->card_network ?? '' }} · {{ $tx->card->card_type ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-800">${{ number_format($tx->gross_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right text-red-600 font-semibold">
                            {{ $tx->discount_amount > 0 ? '-$' . number_format($tx->discount_amount, 2) : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-700">${{ number_format($taxableBase, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs font-black text-emerald-700">{{ number_format($tx->card->service_charge ?? 0, 2) }}%</span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-600">
                            {{ $tx->service_charge_amount > 0 ? '+$' . number_format($tx->service_charge_amount, 2) : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-amber-600">${{ number_format($bankTotal, 2) }}</td>
                        <td class="px-6 py-4 text-right font-bold {{ $net >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            ${{ number_format($net, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                @if($tx->settlement_status === 'completed') bg-green-100 text-green-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ ucfirst($tx->settlement_status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-slate-500 font-medium">No card transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
