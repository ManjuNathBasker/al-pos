@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Journal Entries</h1>
            <p class="text-sm text-[#64748B] mt-0.5">View and create manual double-entry journal entries.</p>
        </div>
        <button @click="$dispatch('open-modal', 'add-journal')" 
                class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>New Journal Entry</span>
        </button>
    </div>

    {{-- Entries Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Journal #</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Notes / Lines</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($entries as $entry)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-xs font-medium text-[#172033]">{{ $entry->transaction_date->format('M d, Y') }}</td>
                        <td class="py-4 px-4">
                            <span class="text-sm font-bold font-mono text-[#172033]">{{ $entry->journal_number }}</span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="text-xs font-semibold text-[#172033]">{{ $entry->notes }}</div>
                            <div class="text-[11px] text-[#64748B] mt-1 space-y-0.5">
                                @foreach($entry->items as $item)
                                <div class="flex gap-2">
                                    <span class="font-medium">{{ $item->account->account_name }}:</span>
                                    @if($item->debit_amount > 0)
                                        <span class="text-blue-600 font-mono">DR ₹{{ number_format($item->debit_amount, 2) }}</span>
                                    @endif
                                    @if($item->credit_amount > 0)
                                        <span class="text-[#29AB6C] font-mono">CR ₹{{ number_format($item->credit_amount, 2) }}</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm font-mono font-bold text-[#172033] text-right">₹{{ number_format($entry->items->sum('debit_amount'), 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">📋</div>
                            <h3 class="text-sm font-bold text-[#172033]">No journal entries found</h3>
                            <p class="text-xs text-[#64748B] mt-1">Create manual double-entry records for accounting adjustments.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">{{ $entries->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Journal Entry Modal --}}
<x-modal name="add-journal" focusable maxWidth="4xl">
    <form action="{{ route('journal-entries.store') }}" method="POST" class="p-6" x-data="{ items: [{id: 1}, {id: 2}] }">
        @csrf
        <div class="border-b border-[#E5E7EB] pb-3 mb-5">
            <h2 class="text-base font-semibold text-[#172033]">Create Journal Entry</h2>
            <p class="text-xs text-[#64748B] mt-0.5">Add a manual double-entry accounting record</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Transaction Date <span class="text-[#FF4848]">*</span></label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-[#172033]">Notes / Description <span class="text-[#FF4848]">*</span></label>
                <textarea name="notes" required rows="2" placeholder="e.g. Monthly depreciation adjustment"
                          class="mt-1 w-full p-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"></textarea>
            </div>
        </div>

        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-xs font-bold text-[#172033] uppercase tracking-wider">Journal Lines</h3>
            <button type="button" @click="items.push({id: Date.now()})" 
                    class="h-8 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-xs font-semibold text-[#F5703E] flex items-center gap-1 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Line
            </button>
        </div>

        {{-- Journal Items --}}
        <div class="space-y-2 mb-5">
            <template x-for="(item, index) in items" :key="item.id">
                <div class="flex gap-2 items-center">
                    <div class="flex-1">
                        <select x-bind:name="'items['+index+'][account_id]'" required 
                                class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-xs text-[#172033] focus:outline-none focus:border-[#F5703E]">
                            <option value="">Select Account</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ $acc->account_type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-32">
                        <input type="number" step="0.01" x-bind:name="'items['+index+'][debit_amount]'" placeholder="Debit" value="0.00" required
                               class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-xs font-mono text-blue-600 placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E]">
                    </div>
                    <div class="w-32">
                        <input type="number" step="0.01" x-bind:name="'items['+index+'][credit_amount]'" placeholder="Credit" value="0.00" required
                               class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-xs font-mono text-[#29AB6C] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E]">
                    </div>
                    <button type="button" @click="items.splice(index, 1)"
                            class="w-9 h-9 rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#94A3B8] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </template>
        </div>

        <div class="pt-4 border-t border-[#E5E7EB] flex justify-end gap-2.5">
            <button type="button" x-on:click="$dispatch('close')" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] transition-colors">Cancel</button>
            <button type="submit" class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">Save Entry</button>
        </div>
    </form>
</x-modal>
@endsection
