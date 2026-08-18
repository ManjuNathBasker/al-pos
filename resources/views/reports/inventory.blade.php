@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header + Filter --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Inventory Report</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Track current stock levels, valuations, and low-stock alerts.</p>
        </div>
        <form action="{{ route('reports.inventory') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <select name="status" class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                <option value="all">All Items</option>
                <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
            </select>
            <button type="submit" class="h-10 px-4 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Filter</button>
            <button type="submit" name="format" value="pdf" formaction="{{ route('reports.inventory.export') }}" 
                    class="h-10 px-3.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold border border-red-200 flex items-center gap-1.5 transition-colors">PDF</button>
            <button type="submit" name="format" value="excel" formaction="{{ route('reports.inventory.export') }}" 
                    class="h-10 px-3.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-xs font-bold border border-emerald-200 flex items-center gap-1.5 transition-colors">Excel</button>
        </form>
    </div>

    {{-- KPI Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Items</p>
            <p class="text-2xl font-bold text-[#172033] mt-1.5">{{ $stats['total_items'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Inventory Value</p>
            <p class="text-2xl font-bold font-mono text-[#F5703E] mt-1.5">@currency($stats['total_value'])</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Low Stock</p>
            <p class="text-2xl font-bold text-[#FF9932] mt-1.5">{{ $stats['low_stock'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Out of Stock</p>
            <p class="text-2xl font-bold text-[#FF4848] mt-1.5">{{ $stats['out_of_stock'] }}</p>
        </div>
    </div>

    {{-- Inventory Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Item Name</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Code</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Stock Status</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Quantity</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Unit Cost</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Total Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($inventory as $item)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-[#172033]">{{ $item->name }}</td>
                        <td class="py-4 px-4 text-xs font-mono text-[#64748B]">{{ $item->code }}</td>
                        <td class="py-4 px-4">
                            @if($item->current_stock <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-[#FF4848] border border-red-200">Out of Stock</span>
                            @elseif($item->current_stock <= $item->minimum_stock)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-[#FF9932] border border-amber-200">Low Stock</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">In Stock</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right text-xs font-mono font-medium text-[#172033]">{{ $item->current_stock }} {{ $item->unit_type }}</td>
                        <td class="py-4 px-4 text-right text-xs font-mono text-[#64748B]">@currency($item->cost_price)</td>
                        <td class="py-4 px-4 text-right text-sm font-mono font-bold text-[#172033]">@currency($item->current_stock * $item->cost_price)</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-xs text-[#94A3B8]">No inventory data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
