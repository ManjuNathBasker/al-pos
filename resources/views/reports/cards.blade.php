@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Card & Settlement Reports</h2>
        <p class="mt-1 text-sm text-slate-500">Track and reconcile card transactions, bank offers, MDR, and settlement status.</p>
    </div>
    
    <form action="{{ route('reports.cards') }}" method="GET" class="flex flex-wrap items-center gap-2">
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

        <select name="settlement_status" class="rounded-lg border-slate-200 text-sm px-3 py-2">
            <option value="all">All Settlement Statuses</option>
            <option value="pending" {{ $settlementStatus === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="settled" {{ $settlementStatus === 'settled' ? 'selected' : '' }}>Settled</option>
        </select>
        
        <button type="submit" class="p-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 ml-2" title="Filter">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
    </form>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-8">
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gross Card Sales</div>
        <div class="text-2xl font-black text-slate-900">${{ number_format($stats['gross_sales'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Offer Discounts</div>
        <div class="text-2xl font-black text-red-600">${{ number_format($stats['discounts'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Service Charges</div>
        <div class="text-2xl font-black text-emerald-600">${{ number_format($stats['service_charges'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">MDR & Proc. Fees</div>
        <div class="text-2xl font-black text-slate-600">${{ number_format($stats['mdr'] + $stats['processing_fees'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 bg-amber-50/50 border-amber-100">
        <div class="text-[10px] font-bold text-amber-500 uppercase tracking-widest mb-1">Pending Settlement</div>
        <div class="text-2xl font-black text-amber-600">${{ number_format($stats['pending_settlement'], 2) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 bg-green-50/50 border-green-100">
        <div class="text-[10px] font-bold text-green-500 uppercase tracking-widest mb-1">Settled Net</div>
        <div class="text-2xl font-black text-green-600">${{ number_format($stats['settled'], 2) }}</div>
    </div>
</div>

<div x-data="{ activeTab: 'transactions' }" class="space-y-6">
    <!-- Tabs Header -->
    <div class="flex border-b border-slate-200">
        <button 
            @click="activeTab = 'transactions'" 
            :class="activeTab === 'transactions' ? 'border-brand-500 text-brand-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
            class="py-4 px-6 border-b-2 font-medium text-sm transition-all focus:outline-none"
        >
            Card Transactions
        </button>
        <button 
            @click="activeTab = 'settlements'" 
            :class="activeTab === 'settlements' ? 'border-brand-500 text-brand-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'"
            class="py-4 px-6 border-b-2 font-medium text-sm transition-all focus:outline-none"
        >
            Bank Settlement Runs
        </button>
    </div>

    <!-- Card Transactions Tab -->
    <div x-show="activeTab === 'transactions'" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-700">Transaction Logs</h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $transactions->count() }} transaction(s) found</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Order ID / Date</th>
                        <th class="px-6 py-3">Card Info</th>
                        <th class="px-6 py-3 text-right">Gross Amt</th>
                        <th class="px-6 py-3 text-right">Discount</th>
                        <th class="px-6 py-3 text-right">Service Chg</th>
                        <th class="px-6 py-3 text-right">MDR / Fees</th>
                        <th class="px-6 py-3 text-right">Net Expected</th>
                        <th class="px-6 py-3 text-center">Settlement Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/30">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $tx->order->order_number ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('M d, Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-700">{{ $tx->bank_name }} - {{ $tx->card_network }}</div>
                            <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">{{ $tx->card_type }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-800">${{ number_format($tx->gross_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right text-red-600 font-semibold">
                            @if($tx->discount_amount > 0)
                                -${{ number_format($tx->discount_amount, 2) }}
                                <div class="text-[9px] text-slate-400 font-normal">({{ $tx->bankOffer->name ?? 'Offer' }})</div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-emerald-600 font-semibold">
                            @if($tx->service_charge_amount > 0)
                                +${{ number_format($tx->service_charge_amount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-slate-600">
                            @php
                                $taxableBase = max(0, $tx->gross_amount - $tx->discount_amount);
                                $mdr = ($taxableBase + $tx->service_charge_amount) * (($tx->card->mdr ?? 0) / 100);
                            @endphp
                            <div>${{ number_format($mdr + $tx->processing_fee_amount, 2) }}</div>
                            <div class="text-[10px] text-slate-400">MDR: {{ $tx->card->mdr ?? 0 }}% | Fee: ${{ number_format($tx->processing_fee_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">${{ number_format($tx->net_settlement_amount, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                @if($tx->settlement_status === 'settled') bg-green-100 text-green-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ ucfirst($tx->settlement_status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500 font-medium">No card transactions recorded for the selected range.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Settlements Reconciliation Runs Tab -->
    <div x-show="activeTab === 'settlements'" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-700">Reconciliation Logs</h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $settlements->count() }} run(s) found</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Settlement Date</th>
                        <th class="px-6 py-3">Card / Account</th>
                        <th class="px-6 py-3 text-right">Transactions</th>
                        <th class="px-6 py-3 text-right">Expected Net</th>
                        <th class="px-6 py-3 text-right">Actual Paid</th>
                        <th class="px-6 py-3 text-right">Reconciliation Diff</th>
                        <th class="px-6 py-3 text-right">Bank Charge Diff</th>
                        <th class="px-6 py-3 text-center">Reference / Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($settlements as $settlement)
                    <tr class="hover:bg-slate-50/30">
                        <td class="px-6 py-4 font-bold text-slate-800">
                            {{ \Carbon\Carbon::parse($settlement->settlement_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-700">{{ $settlement->card->bank_name ?? 'N/A' }} ({{ $settlement->card->card_network ?? 'N/A' }})</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $settlement->card->settlementAccount->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-700">
                            {{ $settlement->cardTransactions->count() }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-800">${{ number_format($settlement->expected_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-green-600">${{ number_format($settlement->actual_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right font-bold 
                            @if($settlement->settlement_difference_amount != 0) text-red-600 @else text-slate-800 @endif">
                            ${{ number_format($settlement->settlement_difference_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-600">${{ number_format($settlement->bank_charges_difference_amount, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="text-xs font-semibold text-slate-700">{{ $settlement->bank_reference_number ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 max-w-[200px] truncate" title="{{ $settlement->notes }}">{{ $settlement->notes ?? 'No notes' }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500 font-medium">No bank settlements recorded for the selected range.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
