@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. PAGE HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Purchase Orders</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Track supplier purchase orders, stock arrivals, and procurement payments.</p>
        </div>
        <div>
            <a href="{{ route('purchases.create') }}" 
               class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>New Purchase Order</span>
            </a>
        </div>
    </div>

    {{-- Low Stock Replenishment Alert Banner --}}
    @php
        $lowStockItems = \App\Models\InventoryItem::where('current_stock', '<=', \Illuminate\Support\Facades\DB::raw('minimum_stock'))->where('status', true)->get();
    @endphp
    @if($lowStockItems->count() > 0)
    <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-orange-100 text-[#F5703E] rounded-lg flex items-center justify-center flex-shrink-0 font-bold">
                ⚠️
            </div>
            <div>
                <h4 class="text-xs font-bold text-[#172033]">{{ $lowStockItems->count() }} inventory items are below minimum stock</h4>
                <p class="text-[11px] text-[#64748B]">Create a purchase order to replenish your ingredients and stock.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('purchases.create') }}" class="btn-brand h-9 px-3.5 rounded-lg text-white text-xs font-semibold inline-flex items-center gap-1 transition-colors shadow-sm">
                <span>Replenish Stock</span>
            </a>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         2. TOOLBAR (SEARCH & FILTERS)
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
        <form action="{{ route('purchases.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Search Input --}}
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO number..." 
                       class="w-full h-11 pl-10 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>

            {{-- Status Filter --}}
            <div>
                <select name="status" 
                        class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            {{-- Supplier Filter --}}
            <div>
                <select name="supplier_id" 
                        class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-brand flex-1 h-11 rounded-lg text-white text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                    <span>Filter</span>
                </button>
                @if(request('search') || request('status') || request('supplier_id'))
                    <a href="{{ route('purchases.index') }}" 
                       class="h-11 px-4 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-[#64748B] flex items-center justify-center transition-colors">
                        <span>Clear</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. MAIN PURCHASES TABLE
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">PO Number</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Supplier</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Amount</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Order Status</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Payment Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($purchases as $purchase)
                    <tr class="hover:bg-[#FFF8F5] transition-colors group">
                        {{-- PO Number --}}
                        <td class="py-4 px-4">
                            <a href="{{ route('purchases.show', $purchase) }}" class="text-sm font-bold font-mono text-[#172033] hover:text-[#F5703E] transition-colors block">
                                #{{ $purchase->purchase_number }}
                            </a>
                        </td>

                        {{-- Date --}}
                        <td class="py-4 px-4 text-xs font-medium text-[#172033]">
                            {{ $purchase->purchase_date->format('M d, Y') }}
                        </td>

                        {{-- Supplier --}}
                        <td class="py-4 px-4 text-sm font-semibold text-[#172033]">
                            {{ $purchase->supplier->name }}
                        </td>

                        {{-- Total Amount --}}
                        <td class="py-4 px-4 text-sm font-mono font-bold text-[#172033]">
                            ₹{{ number_format($purchase->total_amount, 2) }}
                        </td>

                        {{-- Order Status --}}
                        <td class="py-4 px-4">
                            @php
                                $statusBadges = [
                                    'draft'     => 'bg-slate-100 text-[#64748B] border-slate-200',
                                    'approved'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'received'  => 'bg-orange-50 text-[#F5703E] border-orange-200',
                                    'completed' => 'bg-emerald-50 text-[#29AB6C] border-emerald-200',
                                    'cancelled' => 'bg-red-50 text-[#FF4848] border-red-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusBadges[$purchase->status] ?? 'bg-slate-100 text-[#64748B] border-slate-200' }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>

                        {{-- Payment Status --}}
                        <td class="py-4 px-4">
                            @php
                                $paymentBadges = [
                                    'unpaid'  => 'bg-red-50 text-[#FF4848] border-red-200',
                                    'partial' => 'bg-amber-50 text-[#FF9932] border-amber-200',
                                    'paid'    => 'bg-emerald-50 text-[#29AB6C] border-emerald-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold font-mono uppercase border {{ $paymentBadges[$purchase->payment_status] ?? 'bg-slate-100 text-[#64748B] border-slate-200' }}">
                                {{ $purchase->payment_status }}
                            </span>
                        </td>

                        {{-- Actions (34x34px) --}}
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('purchases.show', $purchase) }}" title="View Purchase Order"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-100 text-[#64748B] hover:text-[#172033] flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">
                                📦
                            </div>
                            <h3 class="text-sm font-bold text-[#172033]">
                                @if(request('search') || request('status') || request('supplier_id'))
                                    No purchase orders match your filter criteria
                                @else
                                    No purchase orders created yet
                                @endif
                            </h3>
                            <p class="text-xs text-[#64748B] mt-1 max-w-sm mx-auto">
                                Create purchase orders to track bulk inventory orders and supplier payments.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. Pagination Bar --}}
        @if($purchases->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
