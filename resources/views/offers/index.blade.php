@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Bank Offers</h2>
        <p class="mt-1 text-sm text-slate-500">Manage bank discount offers, cashback, and promotions linked to card payments.</p>
    </div>
    <a href="{{ route('offers.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Add Offer
    </a>
</div>

{{-- Search --}}
<form action="{{ route('offers.index') }}" method="GET" class="mb-6">
    <div class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search offers..."
               class="flex-1 max-w-md px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none" />
        <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </button>
        @if(request('search'))
        <a href="{{ route('offers.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-all text-sm font-semibold">Clear</a>
        @endif
    </div>
</form>

{{-- Offers Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-3">Offer Name</th>
                    <th class="px-6 py-3">Date Range</th>
                    <th class="px-6 py-3 text-right">Discount</th>
                    <th class="px-6 py-3 text-right">Cashback</th>
                    <th class="px-6 py-3 text-right">Min Purchase</th>
                    <th class="px-6 py-3 text-center">Usage</th>
                    <th class="px-6 py-3 text-center">Contribution</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($offers as $offer)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800">{{ $offer->name }}</div>
                        @if($offer->is_emi_offer)
                        <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-bold">EMI</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-slate-600 font-semibold">{{ \Carbon\Carbon::parse($offer->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($offer->end_date)->format('M d, Y') }}</div>
                        @php $now = now(); $isExpired = $now->gt($offer->end_date); @endphp
                        @if($isExpired)
                            <span class="text-[10px] text-red-500 font-bold">EXPIRED</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="font-bold text-slate-800">
                            {{ $offer->discount_type === 'percent' ? number_format($offer->discount_value, 1) . '%' : '$' . number_format($offer->discount_value, 2) }}
                        </span>
                        @if($offer->max_discount > 0)
                        <div class="text-[10px] text-slate-400">Max: ${{ number_format($offer->max_discount, 2) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right font-bold {{ $offer->cashback > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $offer->cashback > 0 ? '$' . number_format($offer->cashback, 2) : '-' }}
                    </td>
                    <td class="px-6 py-4 text-right font-mono font-semibold text-slate-600">${{ number_format($offer->min_purchase, 2) }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="font-bold text-slate-700">{{ $offer->used_count }}</span>
                        <span class="text-slate-400">/</span>
                        <span class="text-slate-500">{{ $offer->usage_limit > 0 ? $offer->usage_limit : '∞' }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="text-xs">
                            <span class="text-amber-600 font-bold">M: {{ number_format($offer->merchant_contribution) }}%</span>
                            <span class="text-slate-300 mx-0.5">|</span>
                            <span class="text-blue-600 font-bold">B: {{ number_format($offer->bank_contribution) }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($offer->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('offers.edit', $offer) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                            <form action="{{ route('offers.destroy', $offer) }}" method="POST" onsubmit="return confirm('Delete this offer?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-slate-500 font-medium">
                        No bank offers found. <a href="{{ route('offers.create') }}" class="text-indigo-600 hover:underline font-bold">Create your first offer</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($offers->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $offers->links() }}
    </div>
    @endif
</div>
@endsection
