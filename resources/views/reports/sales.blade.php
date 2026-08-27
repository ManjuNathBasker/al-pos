@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header + Filter Bar --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Sales Report</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Analyze your order and revenue performance across date ranges.</p>
        </div>

        <form action="{{ route('reports.sales') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="date" name="start_date" value="{{ $startDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <span class="text-[#94A3B8] text-xs font-medium">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <select name="status" class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                <option value="all">All Statuses</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <select name="service_type" class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                <option value="all">All Services</option>
                <option value="dine-in" {{ request('service_type') == 'dine-in' ? 'selected' : '' }}>Dine-in</option>
                <option value="takeaway" {{ request('service_type') == 'takeaway' ? 'selected' : '' }}>Takeaway</option>
                <option value="delivery" {{ request('service_type') == 'delivery' ? 'selected' : '' }}>Delivery</option>
            </select>
            <button type="submit" class="h-10 px-4 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Filter</button>
            <div class="flex items-center gap-2">
                <button type="submit" name="format" value="pdf" formaction="{{ route('reports.sales.export') }}" 
                        class="h-10 px-3.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold border border-red-200 flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    PDF
                </button>
                <button type="submit" name="format" value="excel" formaction="{{ route('reports.sales.export') }}" 
                        class="h-10 px-3.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-xs font-bold border border-emerald-200 flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Excel
                </button>
            </div>
        </form>
    </div>

    {{-- KPI Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Orders</p>
            <p class="text-2xl font-bold text-[#172033] mt-1.5">{{ $stats['total_orders'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Sales</p>
            <p class="text-2xl font-bold font-mono text-[#F5703E] mt-1.5">@currency($stats['total_sales'])</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Paid</p>
            <p class="text-2xl font-bold font-mono text-[#29AB6C] mt-1.5">@currency($stats['total_paid'])</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Tax Collected</p>
            <p class="text-2xl font-bold font-mono text-[#172033] mt-1.5">@currency($stats['total_tax'])</p>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Order #</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date & Time</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Customer</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Service</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($orders as $order)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-sm font-bold font-mono text-[#172033]">{{ $order->order_number }}</td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-4 px-4 text-xs font-medium text-[#172033]">{{ $order->customer->name ?? 'Walk-in' }}</td>
                        @php
                            $stKey = $order->service_type ?: ($order->table_id ? 'dine_in' : 'retail');
                            $stLabel = match ($stKey) {
                                'dine_in' => 'Dine-In',
                                'takeaway', 'pickup' => 'Takeaway',
                                'delivery' => 'Delivery',
                                default => 'Counter',
                            };
                        @endphp
                        <td class="py-4 px-4 text-xs font-semibold text-[#172033]">{{ $stLabel }}</td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                                @if($order->status === 'paid') bg-emerald-50 text-[#29AB6C] border-emerald-200
                                @elseif($order->status === 'cancelled') bg-red-50 text-[#FF4848] border-red-200
                                @else bg-amber-50 text-[#FF9932] border-amber-200 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right text-sm font-mono font-bold text-[#172033]">@currency($order->total_amount, $order)</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-xs text-[#94A3B8]">No sales data found for the selected filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
