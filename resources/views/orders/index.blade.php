@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. PAGE HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Orders</h1>
            <p class="text-sm text-[#64748B] mt-0.5">View and manage all POS dining receipts and transactions.</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. TOOLBAR (SEARCH & FILTERS)
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
        <form action="{{ route('orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Search --}}
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Order ID or notes..." 
                       class="w-full h-11 pl-10 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>

            {{-- Status --}}
            <div>
                <select name="status" id="status" 
                        class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            {{-- Date --}}
            <div>
                <input type="date" name="date" id="date" value="{{ request('date') }}" 
                       class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <button type="submit" 
                        class="btn-brand flex-1 h-11 rounded-lg text-white text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                    <span>Filter</span>
                </button>
                @if(request('search') || request('status') || request('date'))
                    <a href="{{ route('orders.index') }}" 
                       class="h-11 px-4 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-[#64748B] flex items-center justify-center transition-colors">
                        <span>Clear</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. MAIN ORDERS TABLE
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Order #</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date & Time</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Amount</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($orders as $order)
                    <tr class="hover:bg-[#FFF8F5] transition-colors group">
                        {{-- Order Number --}}
                        <td class="py-4 px-4">
                            <a href="{{ route('orders.show', $order) }}" class="text-sm font-bold font-mono text-[#172033] hover:text-[#F5703E] transition-colors block">
                                {{ $order->order_number }}
                            </a>
                            <span class="text-xs text-[#64748B]">
                                {{ $order->customer ? $order->customer->name : 'Walk-in Customer' }}
                            </span>
                        </td>

                        {{-- Date & Time --}}
                        <td class="py-4 px-4">
                            <div class="text-xs font-medium text-[#172033]">{{ $order->created_at->format('M d, Y') }}</div>
                            <div class="text-[11px] text-[#94A3B8]">{{ $order->created_at->format('h:i A') }}</div>
                        </td>

                        {{-- Total Amount --}}
                        <td class="py-4 px-4 text-sm font-mono font-bold text-[#172033]">
                            @currency($order->total_amount, $order)
                        </td>

                        {{-- Status --}}
                        <td class="py-4 px-4">
                            @if($order->status == 'paid')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">Paid</span>
                            @elseif($order->status == 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-[#FF9932] border border-amber-200">Pending</span>
                            @elseif($order->status == 'cancelled' || $order->status == 'refunded')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-[#FF4848] border border-red-200">{{ ucfirst($order->status) }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-[#64748B] border border-slate-200">{{ ucfirst($order->status) }}</span>
                            @endif
                        </td>

                        {{-- Action Buttons (34x34px) --}}
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('orders.show', $order) }}" title="View Order Receipt"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-100 text-[#64748B] hover:text-[#172033] flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">
                                🧾
                            </div>
                            <h3 class="text-sm font-bold text-[#172033]">
                                @if(request('search') || request('status') || request('date'))
                                    No orders found matching your filter criteria
                                @else
                                    No orders recorded yet
                                @endif
                            </h3>
                            <p class="text-xs text-[#64748B] mt-1 max-w-sm mx-auto">
                                Completed sales from the POS terminal and tables will appear here.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. Pagination Bar --}}
        @if($orders->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
