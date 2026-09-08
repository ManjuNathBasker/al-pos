@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Card Types</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Manage payment card types and their commission rates at POS checkout.</p>
        </div>
        <a href="{{ route('card-types.create') }}" 
           class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Card Type</span>
        </a>
    </div>

    {{-- Search Filter --}}
    <form method="GET" class="flex items-center gap-2.5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..."
               class="flex-1 h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
        <button type="submit" class="h-10 px-4 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Search</button>
        @if(request('search'))
            <a href="{{ route('card-types.index') }}" class="h-10 px-3.5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#64748B] flex items-center transition-colors">Clear</a>
        @endif
    </form>

    {{-- Card Types Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Name</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Commission</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Handling</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Expense Account</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($cardTypes as $type)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-[#172033]">{{ $type->name }}</td>
                        <td class="py-4 px-4 text-xs font-mono font-medium text-[#172033]">
                            @if($type->commission_type === 'percentage')
                                {{ number_format($type->commission_value, 2) }}%
                            @else
                                @currency($type->commission_value) (fixed)
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            @php
                                $handlingLabels = [
                                    'ignore'              => ['label' => 'Ignore',             'cls' => 'bg-slate-100 text-[#64748B] border-slate-200'],
                                    'auto_write_off'      => ['label' => 'Auto Write-Off',     'cls' => 'bg-amber-50 text-[#FF9932] border-amber-200'],
                                    'settlement_tracking' => ['label' => 'Settlement Tracking','cls' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                ];
                                $h = $handlingLabels[$type->commission_handling] ?? ['label' => $type->commission_handling, 'cls' => 'bg-slate-100 text-[#64748B] border-slate-200'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $h['cls'] }}">{{ $h['label'] }}</span>
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">{{ $type->expenseAccount?->account_name ?? '—' }}</td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                                {{ $type->status ? 'bg-emerald-50 text-[#29AB6C] border-emerald-200' : 'bg-slate-100 text-[#64748B] border-slate-200' }}">
                                {{ $type->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('card-types.edit', $type) }}" title="Edit"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('card-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Delete this card type?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Delete"
                                            class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-lg mx-auto mb-2 border border-orange-100">💳</div>
                            <p class="text-sm font-medium text-[#172033]">No card types found</p>
                            <p class="text-xs text-[#64748B] mt-0.5">
                                <a href="{{ route('card-types.create') }}" class="text-[#F5703E] hover:underline font-semibold">Add the first one →</a>
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cardTypes->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">{{ $cardTypes->links() }}</div>
        @endif
    </div>
</div>
@endsection
