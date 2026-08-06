@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">{{ $deliveryPartner->name }} - Settlements</h2>
        <p class="mt-1 text-sm text-slate-500">Manage pending settlements and orders for this delivery partner.</p>
    </div>
    <a href="{{ route('delivery-partners.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Partners
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Order ID</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Customer</th>
                    <th class="px-6 py-4">Bill Amount</th>
                    <th class="px-6 py-4">Commission</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-900">#{{ $order->order_number ?: $order->id }}</td>
                        <td class="px-6 py-4">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4">{{ $order->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-6 py-4 font-semibold">${{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-rose-600 font-semibold">
                            ${{ number_format($order->delivery_commission_amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($order->settlement_status === 'settled')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Settled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($order->settlement_status !== 'settled')
                                <form action="{{ route('delivery-partners.mark-settled', $order->id) }}" method="POST" onsubmit="return confirm('Mark this order as settled by the delivery partner?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-brand-50 text-brand-600 hover:bg-brand-100 rounded-lg text-xs font-semibold transition-colors border border-brand-200">
                                        Mark as Settled
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-50 mb-3">
                                <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-900">No Orders Found</h3>
                            <p class="mt-1 text-sm text-slate-500">No delivery orders have been tracked for this partner yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
