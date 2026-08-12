@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header + Filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Purchase Report</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Analyze procurement spending and supplier performance.</p>
        </div>
        <form action="{{ route('reports.purchases') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="date" name="start_date" value="{{ $startDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <span class="text-[#94A3B8] text-xs">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <select name="status" class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                <option value="all">All Statuses</option>
                <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
            <button type="submit" class="h-10 px-4 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Filter</button>
            <button type="submit" name="format" value="pdf" formaction="{{ route('reports.purchases.export') }}" 
                    class="h-10 px-3.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold border border-red-200 transition-colors">PDF</button>
            <button type="submit" name="format" value="excel" formaction="{{ route('reports.purchases.export') }}" 
                    class="h-10 px-3.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-xs font-bold border border-emerald-200 transition-colors">Excel</button>
        </form>
    </div>

    {{-- KPI Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total POs</p>
            <p class="text-2xl font-bold text-[#172033] mt-1.5">{{ $stats['total_purchases'] }}</p>
            <p class="text-xs text-[#64748B] mt-1">In selected period</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Amount</p>
            <p class="text-2xl font-bold font-mono text-[#F5703E] mt-1.5">₹{{ number_format($stats['total_amount'], 2) }}</p>
            <p class="text-xs text-[#64748B] mt-1">Inventory cost</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Paid</p>
            <p class="text-2xl font-bold font-mono text-[#29AB6C] mt-1.5">₹{{ number_format($stats['total_paid'], 2) }}</p>
            <p class="text-xs text-[#64748B] mt-1">Cash outflow</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Due</p>
            <p class="text-2xl font-bold font-mono text-[#FF4848] mt-1.5">₹{{ number_format($stats['total_due'], 2) }}</p>
            <p class="text-xs text-[#64748B] mt-1">Outstanding liabilities</p>
        </div>
    </div>

    {{-- Two Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top Suppliers by Volume --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-slate-50/75">
                <h3 class="text-sm font-semibold text-[#172033]">Top Suppliers by Volume</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E5E7EB]">
                            <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Supplier</th>
                            <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-center">Orders</th>
                            <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Total Purchase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @foreach($topSuppliers as $supplier)
                        <tr class="hover:bg-[#FFF8F5] transition-colors">
                            <td class="py-3.5 px-4 text-sm font-semibold text-[#172033]">{{ $supplier->name }}</td>
                            <td class="py-3.5 px-4 text-center text-xs font-medium text-[#64748B]">{{ $supplier->purchases_count }}</td>
                            <td class="py-3.5 px-4 text-right text-sm font-bold font-mono text-[#F5703E]">₹{{ number_format($supplier->purchases_sum_total_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Monthly Spending Trend --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6">
            <h3 class="text-sm font-semibold text-[#172033] mb-5">Spending Trend (Last 6 Months)</h3>
            <div class="space-y-4">
                @foreach($monthlyData as $data)
                <div>
                    <div class="flex justify-between text-xs font-semibold text-[#64748B] mb-1.5">
                        <span>{{ date('F Y', strtotime($data->month . '-01')) }}</span>
                        <span class="font-mono text-[#172033]">₹{{ number_format($data->total, 2) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        @php 
                            $max = $monthlyData->max('total') ?: 1;
                            $percent = ($data->total / $max) * 100;
                        @endphp
                        <div class="h-2 rounded-full transition-all" style="width: {{ $percent }}%; background-color: #F5703E;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
