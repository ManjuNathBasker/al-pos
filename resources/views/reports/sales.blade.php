@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Sales Reports</h2>
        <p class="mt-1 text-sm text-slate-500">Analyze your sales performance across orders.</p>
    </div>
    
    <form action="{{ route('reports.sales') }}" method="GET" class="flex flex-wrap items-center gap-2">
        <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-slate-200 text-sm px-3 py-2">
        <span class="text-slate-400">to</span>
        <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-slate-200 text-sm px-3 py-2">
        
        <select name="status" class="rounded-lg border-slate-200 text-sm px-3 py-2">
            <option value="all">All Statuses</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        
        <select name="service_type" class="rounded-lg border-slate-200 text-sm px-3 py-2">
            <option value="all">All Services</option>
            <option value="dine-in" {{ request('service_type') == 'dine-in' ? 'selected' : '' }}>Dine-in</option>
            <option value="takeaway" {{ request('service_type') == 'takeaway' ? 'selected' : '' }}>Takeaway</option>
            <option value="delivery" {{ request('service_type') == 'delivery' ? 'selected' : '' }}>Delivery</option>
        </select>
        
        <button type="submit" class="p-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 ml-2" title="Filter">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
        
        <div class="flex items-center gap-2 ml-auto">
            <button type="submit" name="format" value="pdf" formaction="{{ route('reports.sales.export') }}" class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-bold text-sm transition-colors border border-red-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                PDF
            </button>
            <button type="submit" name="format" value="excel" formaction="{{ route('reports.sales.export') }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 font-bold text-sm transition-colors border border-emerald-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Excel
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Orders</div>
        <div class="text-3xl font-black text-slate-900">{{ $stats['total_orders'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Sales</div>
        <div class="text-3xl font-black text-indigo-600">${{ number_format($stats['total_sales'], 2) }}</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Paid</div>
        <div class="text-3xl font-black text-green-600">${{ number_format($stats['total_paid'], 2) }}</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Tax Collected</div>
        <div class="text-3xl font-black text-slate-600">${{ number_format($stats['total_tax'], 2) }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-4">Order #</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Customer</th>
                    <th class="px-6 py-4">Service Type</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-6 py-4 text-sm font-bold text-slate-800">{{ $order->order_number }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $order->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $order->customer->name ?? 'Walk-in' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ ucfirst($order->service_type) }}</td>
                    <td class="px-6 py-4 text-center text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($order->status === 'paid') bg-green-100 text-green-800
                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-amber-100 text-amber-800 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-slate-800">${{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">No sales data found for the selected filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
