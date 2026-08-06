@extends('layouts.app')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Card Types</h2>
        <p class="mt-1 text-sm text-slate-500">Manage card commission types used at POS checkout.</p>
    </div>
    <a href="{{ route('card-types.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Card Type
    </a>
</div>

{{-- Search --}}
<form method="GET" class="mb-6 flex gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search by name..."
           class="flex-1 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none shadow-sm">
    <button type="submit" class="px-5 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">
        Search
    </button>
    @if(request('search'))
        <a href="{{ route('card-types.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-500 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
            Clear
        </a>
    @endif
</form>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</th>
                <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Commission</th>
                <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Handling</th>
                <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Expense Account</th>
                <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                <th class="px-6 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($cardTypes as $type)
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-4 text-sm font-semibold text-slate-800">{{ $type->name }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">
                    @if($type->commission_type === 'percentage')
                        {{ number_format($type->commission_value, 2) }}%
                    @else
                        ₹{{ number_format($type->commission_value, 2) }} (fixed)
                    @endif
                </td>
                <td class="px-6 py-4">
                    @php
                        $handlingLabels = [
                            'ignore'              => ['label' => 'Ignore',             'color' => 'bg-slate-100 text-slate-600'],
                            'auto_write_off'      => ['label' => 'Auto Write-Off',     'color' => 'bg-amber-100 text-amber-700'],
                            'settlement_tracking' => ['label' => 'Settlement Tracking','color' => 'bg-blue-100 text-blue-700'],
                        ];
                        $h = $handlingLabels[$type->commission_handling] ?? ['label' => $type->commission_handling, 'color' => 'bg-slate-100 text-slate-600'];
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold {{ $h['color'] }}">
                        {{ $h['label'] }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">
                    {{ $type->expenseAccount?->account_name ?? '—' }}
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold {{ $type->status ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                        {{ $type->status ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right flex items-center justify-end gap-3">
                    <a href="{{ route('card-types.edit', $type) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Edit</a>
                    <form action="{{ route('card-types.destroy', $type) }}" method="POST"
                          onsubmit="return confirm('Delete this card type?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">
                    No card types found.
                    <a href="{{ route('card-types.create') }}" class="text-indigo-600 font-semibold ml-1">Add the first one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($cardTypes->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $cardTypes->links() }}
    </div>
    @endif
</div>
@endsection
