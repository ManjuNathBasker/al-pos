@extends('layouts.app')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Card Commission Report</h2>
        <p class="mt-1 text-sm text-slate-500">Commission deductions on card payments at POS.</p>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @php
        $summaryItems = [
            ['label' => 'Total Orders',      'value' => number_format($totals->total_orders ?? 0),           'color' => 'indigo'],
            ['label' => 'Total Billed',       'value' => '₹' . number_format($totals->total_billed ?? 0, 2),      'color' => 'slate'],
            ['label' => 'Commission',         'value' => '₹' . number_format($totals->total_commission ?? 0, 2),  'color' => 'amber'],
            ['label' => 'Commission Tax',     'value' => '₹' . number_format($totals->total_commission_tax ?? 0, 2), 'color' => 'orange'],
            ['label' => 'Total Deduction',   'value' => '₹' . number_format($totals->total_deduction ?? 0, 2),    'color' => 'red'],
            ['label' => 'Net Received',       'value' => '₹' . number_format($totals->total_net_received ?? 0, 2), 'color' => 'emerald'],
        ];
    @endphp
    @foreach($summaryItems as $item)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
        <p class="text-[10px] font-black uppercase tracking-widest text-{{ $item['color'] }}-500 mb-1">{{ $item['label'] }}</p>
        <p class="text-lg font-bold text-slate-800 font-mono">{{ $item['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:border-indigo-400 outline-none">
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:border-indigo-400 outline-none">
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Card Type</label>
            <select name="card_type_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:border-indigo-400 outline-none">
                <option value="">All Types</option>
                @foreach($cardTypes as $ct)
                    <option value="{{ $ct->id }}" {{ request('card_type_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Handling</label>
            <select name="handling" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:border-indigo-400 outline-none">
                <option value="">All Handling</option>
                <option value="ignore" {{ request('handling') === 'ignore' ? 'selected' : '' }}>Ignore</option>
                <option value="auto_write_off" {{ request('handling') === 'auto_write_off' ? 'selected' : '' }}>Auto Write-Off</option>
                <option value="settlement_tracking" {{ request('handling') === 'settlement_tracking' ? 'selected' : '' }}>Settlement Tracking</option>
            </select>
        </div>
    </div>
    <div class="mt-4 flex gap-3">
        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all">Apply Filters</button>
        <a href="{{ route('reports.card-commission') }}" class="px-5 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Clear</a>
    </div>
</form>

{{-- Transactions Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Order #</th>
                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Card Type</th>
                <th class="px-4 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Bill Amount</th>
                <th class="px-4 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Commission</th>
                <th class="px-4 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Tax</th>
                <th class="px-4 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Deduction</th>
                <th class="px-4 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Net Received</th>
                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Handling</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($orders as $order)
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-3 text-sm font-bold text-indigo-600">{{ $order->order_number }}</td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                <td class="px-4 py-3 text-sm text-slate-700">{{ $order->customer?->name ?? 'Walk-in' }}</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $order->cardType?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm font-mono text-right text-slate-800">₹{{ number_format($order->total_amount, 2) }}</td>
                <td class="px-4 py-3 text-sm font-mono text-right text-amber-600">₹{{ number_format($order->card_commission_amount, 4) }}</td>
                <td class="px-4 py-3 text-sm font-mono text-right text-orange-600">₹{{ number_format($order->card_commission_tax_amount, 4) }}</td>
                <td class="px-4 py-3 text-sm font-mono text-right text-red-600">₹{{ number_format($order->card_commission_total_deduction, 4) }}</td>
                <td class="px-4 py-3 text-sm font-mono text-right text-emerald-700 font-bold">₹{{ number_format($order->card_net_received, 4) }}</td>
                <td class="px-4 py-3">
                    @php
                        $handlingColors = [
                            'ignore'              => 'bg-slate-100 text-slate-600',
                            'auto_write_off'      => 'bg-amber-100 text-amber-700',
                            'settlement_tracking' => 'bg-blue-100 text-blue-700',
                        ];
                        $h = $order->cardType?->commission_handling ?? 'ignore';
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $handlingColors[$h] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ ucfirst(str_replace('_', ' ', $h)) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-6 py-12 text-center text-sm text-slate-400">
                    No card commission transactions found for the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($orders->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
