@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header + Filter --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Card Settlements & Reconciliation</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Audit swipe transactions, MDR deduction splits, and bank reconciliation diffs.</p>
        </div>
        <form action="{{ route('reports.cards') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="date" name="start_date" value="{{ $startDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <span class="text-[#94A3B8] text-xs">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <select name="status" class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                <option value="all">All Settlements</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="settled" {{ request('status') == 'settled' ? 'selected' : '' }}>Settled</option>
            </select>
            <select name="card_id" class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                <option value="all">All Cards</option>
                @foreach($cards as $card)
                    <option value="{{ $card->id }}" {{ request('card_id') == $card->id ? 'selected' : '' }}>
                        {{ $card->bank_name }} - {{ $card->card_network }} ({{ $card->card_type }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="h-10 px-4 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Filter</button>
        </form>
    </div>

    {{-- KPI Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#94A3B8] mb-1">Gross Card Sales</p>
            <p class="text-lg font-bold font-mono text-[#172033]">@currency($stats['gross_sales'])</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#94A3B8] mb-1">Offer Discounts</p>
            <p class="text-lg font-bold font-mono text-[#FF4848]">@currency($stats['discounts'])</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#94A3B8] mb-1">Service Charges</p>
            <p class="text-lg font-bold font-mono text-[#29AB6C]">@currency($stats['service_charges'])</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#94A3B8] mb-1">MDR & Proc. Fees</p>
            <p class="text-lg font-bold font-mono text-[#64748B]">@currency($stats['mdr'] + $stats['processing_fees'])</p>
        </div>
        <div class="bg-amber-50/80 rounded-xl border border-amber-200 shadow-sm p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#FF9932] mb-1">Pending Settlement</p>
            <p class="text-lg font-bold font-mono text-[#FF9932]">@currency($stats['pending_settlement'])</p>
        </div>
        <div class="bg-emerald-50/80 rounded-xl border border-emerald-200 shadow-sm p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#29AB6C] mb-1">Settled Net</p>
            <p class="text-lg font-bold font-mono text-[#29AB6C]">@currency($stats['settled'])</p>
        </div>
    </div>

    {{-- Tab Interface --}}
    <div x-data="{ activeTab: 'transactions' }" class="space-y-4">
        <div class="flex border-b border-[#E5E7EB]">
            <button @click="activeTab = 'transactions'" 
                    :class="activeTab === 'transactions' ? 'border-[#F5703E] text-[#F5703E] font-semibold' : 'border-transparent text-[#64748B] hover:text-[#172033]'"
                    class="py-3 px-5 border-b-2 text-sm transition-all focus:outline-none">
                Card Transactions
            </button>
            <button @click="activeTab = 'settlements'" 
                    :class="activeTab === 'settlements' ? 'border-[#F5703E] text-[#F5703E] font-semibold' : 'border-transparent text-[#64748B] hover:text-[#172033]'"
                    class="py-3 px-5 border-b-2 text-sm transition-all focus:outline-none">
                Reconciliations & Settlements
            </button>
        </div>

        {{-- Transactions List Tab --}}
        <div x-show="activeTab === 'transactions'" class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date & Time</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Order</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Card Details</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Gross</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Discount</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Svc Chg</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">MDR / Fees</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Net Expected</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($transactions as $tx)
                        <tr class="hover:bg-[#FFF8F5] transition-colors">
                            <td class="py-4 px-4 text-xs font-medium text-[#172033]">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                            <td class="py-4 px-4">
                                <a href="{{ route('orders.show', $tx->order_id) }}" class="text-xs font-bold font-mono text-[#F5703E] hover:underline">
                                    {{ $tx->order->order_number ?? '#' . $tx->order_id }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-xs font-semibold text-[#172033]">{{ $tx->bank_name }} - {{ $tx->card_network }}</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-[#94A3B8] mt-0.5">{{ $tx->card_type }}</div>
                            </td>
                            <td class="py-4 px-4 text-right text-xs font-mono font-semibold text-[#172033]">@currency($tx->gross_amount)</td>
                            <td class="py-4 px-4 text-right text-xs font-mono text-[#FF4848] font-medium">
                                @if($tx->discount_amount > 0)
                                    -@currency($tx->discount_amount)
                                    <div class="text-[9px] text-[#94A3B8]">({{ $tx->bankOffer->name ?? 'Offer' }})</div>
                                @else
                                    <span class="text-[#94A3B8]">—</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right text-xs font-mono text-[#29AB6C] font-medium">
                                @if($tx->service_charge_amount > 0)
                                    +@currency($tx->service_charge_amount)
                                @else
                                    <span class="text-[#94A3B8]">—</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right text-xs font-mono text-[#64748B]">
                                @php
                                    $taxableBase = max(0, $tx->gross_amount - $tx->discount_amount);
                                    $mdr = ($taxableBase + $tx->service_charge_amount) * (($tx->card->mdr ?? 0) / 100);
                                @endphp
                                <div>@currency($mdr + $tx->processing_fee_amount)</div>
                                <div class="text-[10px] text-[#94A3B8]">MDR: {{ $tx->card->mdr ?? 0 }}% | Fee: @currency($tx->processing_fee_amount)</div>
                            </td>
                            <td class="py-4 px-4 text-right text-xs font-bold font-mono text-[#172033]">@currency($tx->net_settlement_amount)</td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border
                                    @if($tx->settlement_status === 'settled') bg-emerald-50 text-[#29AB6C] border-emerald-200
                                    @else bg-amber-50 text-[#FF9932] border-amber-200 @endif">
                                    {{ ucfirst($tx->settlement_status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-xs text-[#94A3B8]">No card transactions found for the selected range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Settlement Reconciliation Tab --}}
        <div x-show="activeTab === 'settlements'" class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-slate-50/75 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-[#172033]">Reconciliation Logs</h3>
                <span class="text-xs text-[#64748B] font-semibold">{{ $settlements->count() }} run(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Settlement Date</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Card / Account</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Transactions</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Expected Net</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Actual Paid</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Recon. Diff</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Bank Chg Diff</th>
                            <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Reference / Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($settlements as $settlement)
                        <tr class="hover:bg-[#FFF8F5] transition-colors">
                            <td class="py-4 px-4 text-xs font-semibold text-[#172033]">{{ \Carbon\Carbon::parse($settlement->settlement_date)->format('M d, Y') }}</td>
                            <td class="py-4 px-4">
                                <div class="text-xs font-semibold text-[#172033]">{{ $settlement->card->bank_name ?? 'N/A' }} ({{ $settlement->card->card_network ?? 'N/A' }})</div>
                                <div class="text-[10px] font-mono text-[#94A3B8] mt-0.5">{{ $settlement->card->settlementAccount->name ?? 'N/A' }}</div>
                            </td>
                            <td class="py-4 px-4 text-right text-xs font-medium text-[#172033]">{{ $settlement->cardTransactions->count() }}</td>
                            <td class="py-4 px-4 text-right text-xs font-mono font-semibold text-[#172033]">@currency($settlement->expected_amount)</td>
                            <td class="py-4 px-4 text-right text-xs font-mono font-semibold text-[#29AB6C]">@currency($settlement->actual_amount)</td>
                            <td class="py-4 px-4 text-right text-xs font-bold font-mono {{ $settlement->settlement_difference_amount != 0 ? 'text-[#FF4848]' : 'text-[#64748B]' }}">@currency($settlement->settlement_difference_amount)</td>
                            <td class="py-4 px-4 text-right text-xs font-mono text-[#64748B]">@currency($settlement->bank_charges_difference_amount)</td>
                            <td class="py-4 px-4">
                                <div class="text-xs font-semibold text-[#172033]">{{ $settlement->bank_reference_number ?? '—' }}</div>
                                <div class="text-[10px] text-[#94A3B8] max-w-[200px] truncate" title="{{ $settlement->notes }}">{{ $settlement->notes ?? 'No notes' }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-xs text-[#94A3B8]">No settlement reconciliations found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
