@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Purchase Orders</h2>
        <p class="mt-1 text-sm text-slate-500">Track and manage your inventory procurement.</p>
    </div>
    <a href="{{ route('purchases.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Purchase
    </a>
</div>

{{-- Low Stock Suggestions --}}
@php
    $lowStockItems = \App\Models\InventoryItem::where('current_stock', '<=', \Illuminate\Support\Facades\DB::raw('minimum_stock'))->where('status', true)->get();
@endphp
@if($lowStockItems->count() > 0)
<div class="mb-8 p-4 bg-red-50 border border-red-100 rounded-xl flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="p-2 bg-red-100 text-red-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div>
            <h4 class="text-sm font-bold text-red-900">{{ $lowStockItems->count() }} items are low on stock!</h4>
            <p class="text-xs text-red-700">Consider creating a purchase order to replenish inventory.</p>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('purchases.create') }}" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition-colors">Replenish Now</a>
    </div>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
        <form action="{{ route('purchases.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by PO number..." class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>
            <div class="w-48">
                <select name="status" class="w-full rounded-lg border-slate-200 text-sm">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="w-48">
                <select name="supplier_id" class="w-full rounded-lg border-slate-200 text-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50">Filter</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">PO Number</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($purchases as $purchase)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('purchases.show', $purchase) }}" class="font-bold text-indigo-600 hover:text-indigo-700">#{{ $purchase->purchase_number }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $purchase->purchase_date->format('M j, Y') }}</td>
                    <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $purchase->supplier->name }}</td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900">${{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="px-6 py-4">
                        @php
                            $statusClasses = [
                                'draft'     => 'bg-slate-100 text-slate-700',
                                'approved'  => 'bg-blue-100 text-blue-700',
                                'received'  => 'bg-orange-100 text-orange-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$purchase->status] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($purchase->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $paymentClasses = [
                                'unpaid'  => 'bg-red-50 text-red-600 border-red-100',
                                'partial' => 'bg-amber-50 text-amber-600 border-amber-100',
                                'paid'    => 'bg-green-50 text-green-600 border-green-100',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-tight {{ $paymentClasses[$purchase->payment_status] ?? 'bg-slate-50 text-slate-600' }}">
                            {{ $purchase->payment_status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('purchases.show', $purchase) }}" class="p-2 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors inline-block">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">No purchase orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
    <div class="p-6 border-t border-slate-100 bg-slate-50/50">
        {{ $purchases->links() }}
    </div>
    @endif
</div>
@endsection
