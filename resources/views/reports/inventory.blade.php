@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Inventory Reports</h2>
        <p class="mt-1 text-sm text-slate-500">Track current stock levels and valuations.</p>
    </div>
    
    <form action="{{ route('reports.inventory') }}" method="GET" class="flex flex-wrap items-center gap-2">
        <select name="status" class="rounded-lg border-slate-200 text-sm px-3 py-2">
            <option value="all">All Items</option>
            <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
            <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
            <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
        </select>
        
        <button type="submit" class="p-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 ml-2" title="Filter">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
        
        <div class="flex items-center gap-2 ml-auto">
            <button type="submit" name="format" value="pdf" formaction="{{ route('reports.inventory.export') }}" class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-bold text-sm transition-colors border border-red-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                PDF
            </button>
            <button type="submit" name="format" value="excel" formaction="{{ route('reports.inventory.export') }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 font-bold text-sm transition-colors border border-emerald-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Excel
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Items</div>
        <div class="text-3xl font-black text-slate-900">{{ $stats['total_items'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Inventory Value</div>
        <div class="text-3xl font-black text-indigo-600">${{ number_format($stats['total_value'], 2) }}</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Low Stock Items</div>
        <div class="text-3xl font-black text-amber-600">{{ $stats['low_stock'] }}</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Out of Stock Items</div>
        <div class="text-3xl font-black text-red-600">{{ $stats['out_of_stock'] }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-4">Item Name</th>
                    <th class="px-6 py-4">Item Code</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Quantity</th>
                    <th class="px-6 py-4 text-right">Unit Cost</th>
                    <th class="px-6 py-4 text-right">Total Value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($inventory as $item)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-6 py-4 text-sm font-bold text-slate-800">{{ $item->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $item->code }}</td>
                    <td class="px-6 py-4 text-center text-sm">
                        @if($item->current_stock <= 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                        @elseif($item->current_stock <= $item->minimum_stock)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Low Stock</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">In Stock</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm text-slate-600">{{ $item->current_stock }} {{ $item->unit_type }}</td>
                    <td class="px-6 py-4 text-right text-sm text-slate-600">${{ number_format($item->cost_price, 2) }}</td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-slate-800">${{ number_format($item->current_stock * $item->cost_price, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">No inventory data found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
