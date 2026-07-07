@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Purchase Reports</h2>
        <p class="mt-1 text-sm text-slate-500">Analyze your spending and supplier performance.</p>
    </div>
    <form action="{{ route('reports.purchases') }}" method="GET" class="flex flex-wrap items-center gap-2">
        <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-slate-200 text-sm px-3 py-2">
        <span class="text-slate-400">to</span>
        <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-slate-200 text-sm px-3 py-2">
        
        <select name="status" class="rounded-lg border-slate-200 text-sm px-3 py-2">
            <option value="all">All Statuses</option>
            <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
        
        <button type="submit" class="p-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 ml-2" title="Filter">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
        
        <div class="flex items-center gap-2 ml-auto">
            <button type="submit" name="format" value="pdf" formaction="{{ route('reports.purchases.export') }}" class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-bold text-sm transition-colors border border-red-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                PDF
            </button>
            <button type="submit" name="format" value="excel" formaction="{{ route('reports.purchases.export') }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 font-bold text-sm transition-colors border border-emerald-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Excel
            </button>
        </div>
    </form>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total POs</div>
        <div class="text-3xl font-black text-slate-900">{{ $stats['total_purchases'] }}</div>
        <div class="mt-2 text-xs text-slate-500">In selected period</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Amount</div>
        <div class="text-3xl font-black text-indigo-600">${{ number_format($stats['total_amount'], 2) }}</div>
        <div class="mt-2 text-xs text-slate-500">Inventory cost</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Paid</div>
        <div class="text-3xl font-black text-green-600">${{ number_format($stats['total_paid'], 2) }}</div>
        <div class="mt-2 text-xs text-slate-500">Cash outflow</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Due</div>
        <div class="text-3xl font-black text-red-600">${{ number_format($stats['total_due'], 2) }}</div>
        <div class="mt-2 text-xs text-slate-500">Outstanding liabilities</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Top Suppliers --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-sm">Top Suppliers by Volume</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                        <th class="px-6 py-3">Supplier</th>
                        <th class="px-6 py-3 text-center">Orders</th>
                        <th class="px-6 py-3 text-right">Total Purchase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($topSuppliers as $supplier)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-900 text-sm">{{ $supplier->name }}</td>
                        <td class="px-6 py-4 text-center text-sm text-slate-600">{{ $supplier->purchases_count }}</td>
                        <td class="px-6 py-4 text-right font-black text-indigo-600 text-sm">${{ number_format($supplier->purchases_sum_total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Monthly Spending Trend --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-center">
        <h3 class="font-bold text-slate-800 text-sm mb-6">Spending Trend (Last 6 Months)</h3>
        <div class="space-y-4">
            @foreach($monthlyData as $data)
            <div>
                <div class="flex justify-between text-xs font-bold text-slate-500 mb-1">
                    <span>{{ date('F Y', strtotime($data->month . '-01')) }}</span>
                    <span>${{ number_format($data->total, 2) }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    @php 
                        $max = $monthlyData->max('total') ?: 1;
                        $percent = ($data->total / $max) * 100;
                    @endphp
                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
