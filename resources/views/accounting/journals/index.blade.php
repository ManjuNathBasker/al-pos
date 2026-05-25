@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Journal Entries</h2>
        <p class="mt-1 text-sm text-slate-500">View and create manual journal entries.</p>
    </div>
    <button @click="$dispatch('open-modal', 'add-journal')" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Journal Entry
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Journal #</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Notes / Reference</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($entries as $entry)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $entry->transaction_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $entry->journal_number }}</td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-900">{{ $entry->notes }}</div>
                        <div class="text-xs text-slate-500 mt-1">
                            @foreach($entry->items as $item)
                                <span class="{{ $item->debit_amount > 0 ? 'text-indigo-600' : 'text-slate-500' }}">
                                    {{ $item->account->account_name }}: 
                                    @if($item->debit_amount > 0) DR ${{ number_format($item->debit_amount, 2) }} @endif
                                    @if($item->credit_amount > 0) CR ${{ number_format($item->credit_amount, 2) }} @endif
                                </span><br>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">
                        ${{ number_format($entry->items->sum('debit_amount'), 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">No journal entries found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($entries->hasPages())
    <div class="p-6 border-t border-slate-100 bg-slate-50/50">
        {{ $entries->links() }}
    </div>
    @endif
</div>

{{-- Add Modal --}}
<x-modal name="add-journal" focusable maxWidth="4xl">
    <form action="{{ route('journal-entries.store') }}" method="POST" class="p-6" x-data="{ items: [{id: 1}, {id: 2}] }">
        @csrf
        <h2 class="text-lg font-bold text-slate-800 mb-6 border-b pb-4">Create Journal Entry</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Transaction Date *</label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes / Description *</label>
                <textarea name="notes" required rows="2" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all"></textarea>
            </div>
        </div>

        <h3 class="text-sm font-bold text-slate-800 mb-4">Journal Items</h3>
        <template x-for="(item, index) in items" :key="item.id">
            <div class="flex gap-4 mb-4 items-center">
                <div class="flex-1">
                    <select x-bind:name="'items['+index+'][account_id]'" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-3 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                        <option value="">Select Account</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ $acc->account_type }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-32">
                    <input type="number" step="0.01" x-bind:name="'items['+index+'][debit_amount]'" placeholder="Debit" value="0.00" required class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div class="w-32">
                    <input type="number" step="0.01" x-bind:name="'items['+index+'][credit_amount]'" placeholder="Credit" value="0.00" required class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div>
                    <button type="button" @click="items.splice(index, 1)" class="p-3 text-red-500 hover:bg-red-50 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        </template>
        
        <button type="button" @click="items.push({id: Date.now()})" class="text-indigo-600 text-sm font-medium hover:text-indigo-800">+ Add Line</button>

        <div class="mt-8 flex justify-end gap-3 border-t pt-4">
            <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm">Save Entry</button>
        </div>
    </form>
</x-modal>
@endsection
