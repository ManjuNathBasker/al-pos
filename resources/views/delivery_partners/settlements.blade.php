@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('delivery-partners.index') }}" 
               class="w-9 h-9 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#64748B] flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">{{ $deliveryPartner->name }} — Settlements</h1>
                <p class="text-sm text-[#64748B] mt-0.5">Track and settle pending delivery orders for this partner.</p>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Order ID</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Customer</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Bill Amount</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Commission</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($orders as $order)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-xs font-bold font-mono text-[#F5703E]">#{{ $order->order_number ?: $order->id }}</td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                        <td class="py-4 px-4 text-xs font-medium text-[#172033]">{{ $order->customer->name ?? 'Walk-in' }}</td>
                        <td class="py-4 px-4 text-right text-xs font-mono font-semibold text-[#172033]">₹{{ number_format($order->total_amount, 2) }}</td>
                        <td class="py-4 px-4 text-right text-xs font-mono font-semibold text-[#FF4848]">₹{{ number_format($order->delivery_commission_amount, 2) }}</td>
                        <td class="py-4 px-4">
                            @if($order->settlement_status === 'settled')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#29AB6C]"></span>Settled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-[#FF9932] border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9932]"></span>Pending
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right">
                            @if($order->settlement_status !== 'settled')
                                <form action="{{ route('delivery-partners.mark-settled', $order->id) }}" method="POST" onsubmit="return confirm('Mark this order as settled?');">
                                    @csrf
                                    <button type="submit" class="h-8 px-3 rounded-lg bg-orange-50 hover:bg-orange-100 text-[#F5703E] text-xs font-semibold border border-orange-200 transition-colors">
                                        Mark Settled
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-[#94A3B8]">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-lg mx-auto mb-2 border border-orange-100">📋</div>
                            <h3 class="text-sm font-bold text-[#172033]">No orders found</h3>
                            <p class="text-xs text-[#64748B] mt-1">No delivery orders have been tracked for this partner yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
