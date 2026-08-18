@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Card Commission Report</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Commission deductions applied on card payments at POS.</p>
        </div>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
            $summaryItems = [
                ['label' => 'Total Orders',    'value' => number_format($totals->total_orders ?? 0),              'color' => 'text-[#172033]'],
                ['label' => 'Total Billed',    'value' => format_currency($totals->total_billed ?? 0),           'color' => 'text-[#172033]'],
                ['label' => 'Commission',      'value' => format_currency($totals->total_commission ?? 0),       'color' => 'text-[#FF9932]'],
                ['label' => 'Comm. Tax',       'value' => format_currency($totals->total_commission_tax ?? 0),   'color' => 'text-[#F5703E]'],
                ['label' => 'Total Deduction', 'value' => format_currency($totals->total_deduction ?? 0),        'color' => 'text-[#FF4848]'],
                ['label' => 'Net Received',    'value' => format_currency($totals->total_net_received ?? 0),     'color' => 'text-[#29AB6C]'],
            ];
        @endphp
        @foreach($summaryItems as $item)
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#94A3B8] mb-1">{{ $item['label'] }}</p>
            <p class="text-base font-bold font-mono {{ $item['color'] }}">{{ $item['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Card Type</label>
                <select name="card_type_id" class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    <option value="">All Types</option>
                    @foreach($cardTypes as $ct)
                        <option value="{{ $ct->id }}" {{ request('card_type_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Handling</label>
                <select name="handling" class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    <option value="">All Handling</option>
                    <option value="ignore" {{ request('handling') === 'ignore' ? 'selected' : '' }}>Ignore</option>
                    <option value="auto_write_off" {{ request('handling') === 'auto_write_off' ? 'selected' : '' }}>Auto Write-Off</option>
                    <option value="settlement_tracking" {{ request('handling') === 'settlement_tracking' ? 'selected' : '' }}>Settlement Tracking</option>
                </select>
            </div>
            <div class="col-span-2 md:col-span-4 flex gap-2.5">
                <button type="submit" class="h-10 px-5 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Apply Filters</button>
                <a href="{{ route('reports.card-commission') }}" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#64748B] flex items-center transition-colors">Clear</a>
            </div>
        </form>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Order #</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Customer</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Card Type</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Bill Amount</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Commission</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Tax</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Deduction</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Net Received</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Handling</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($orders as $order)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-3.5 px-4 text-xs font-bold font-mono text-[#F5703E]">{{ $order->order_number }}</td>
                        <td class="py-3.5 px-4 text-xs text-[#64748B]">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                        <td class="py-3.5 px-4 text-xs font-medium text-[#172033]">{{ $order->customer?->name ?? 'Walk-in' }}</td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-[#172033]">{{ $order->cardType?->name ?? '—' }}</td>
                        <td class="py-3.5 px-4 text-right text-xs font-mono font-medium text-[#172033]">@currency($order->total_amount, $order)</td>
                        <td class="py-3.5 px-4 text-right text-xs font-mono text-[#FF9932]">@currency($order->card_commission_amount, $order)</td>
                        <td class="py-3.5 px-4 text-right text-xs font-mono text-[#F5703E]">@currency($order->card_commission_tax_amount, $order)</td>
                        <td class="py-3.5 px-4 text-right text-xs font-mono text-[#FF4848]">@currency($order->card_commission_total_deduction, $order)</td>
                        <td class="py-3.5 px-4 text-right text-xs font-mono font-bold text-[#29AB6C]">@currency($order->card_net_received, $order)</td>
                        <td class="py-3.5 px-4">
                            @php
                                $handlingCls = [
                                    'ignore'              => 'bg-slate-100 text-[#64748B] border-slate-200',
                                    'auto_write_off'      => 'bg-amber-50 text-[#FF9932] border-amber-200',
                                    'settlement_tracking' => 'bg-blue-50 text-blue-700 border-blue-200',
                                ];
                                $h = $order->cardType?->commission_handling ?? 'ignore';
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $handlingCls[$h] ?? 'bg-slate-100 text-[#64748B] border-slate-200' }}">
                                {{ ucfirst(str_replace('_', ' ', $h)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-xs text-[#94A3B8]">No card commission transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
