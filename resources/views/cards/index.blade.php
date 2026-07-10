@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Card Types</h2>
        <p class="mt-1 text-sm text-slate-500">Manage card payment types, service charges, and MDR percentages.</p>
    </div>
    <a href="{{ route('cards.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Add Card Type
    </a>
</div>

{{-- Search --}}
<form action="{{ route('cards.index') }}" method="GET" class="mb-6">
    <div class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by bank name or network..."
               class="flex-1 max-w-md px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none" />
        <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </button>
        @if(request('search'))
        <a href="{{ route('cards.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-all text-sm font-semibold">Clear</a>
        @endif
    </div>
</form>

{{-- Cards Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-3">Bank / Network</th>
                    <th class="px-6 py-3">Card Type</th>
                    <th class="px-6 py-3 text-center">Service Charge %</th>
                    <th class="px-6 py-3 text-center">MDR %</th>
                    <th class="px-6 py-3 text-right">Processing Fee</th>
                    <th class="px-6 py-3 text-center">Settlement Days</th>
                    <th class="px-6 py-3 text-center">Settlement Account</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($cards as $card)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800">{{ $card->bank_name }}</div>
                        <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">{{ $card->card_network }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">{{ $card->card_type }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black {{ $card->service_charge > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-400' }}">
                            {{ number_format($card->service_charge, 2) }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black {{ $card->mdr > 0 ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-slate-400' }}">
                            {{ number_format($card->mdr, 2) }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right font-mono font-bold text-slate-700">${{ number_format($card->processing_fee, 2) }}</td>
                    <td class="px-6 py-4 text-center font-bold text-slate-600">{{ $card->settlement_days }} day(s)</td>
                    <td class="px-6 py-4 text-center text-xs font-semibold text-slate-600">{{ $card->settlementAccount->account_name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($card->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('cards.edit', $card) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                            <form action="{{ route('cards.destroy', $card) }}" method="POST" onsubmit="return confirm('Delete this card type?')">
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
                        No card types found. <a href="{{ route('cards.create') }}" class="text-indigo-600 hover:underline font-bold">Create your first card type</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cards->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $cards->links() }}
    </div>
    @endif
</div>
@endsection
