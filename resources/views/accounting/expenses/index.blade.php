@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Expenses</h2>
        <p class="mt-1 text-sm text-slate-500">Track and manage company expenses.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('expense-categories.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-600">
            Categories
        </a>
        <button @click="$dispatch('open-modal', 'add-expense')" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Record Expense
        </button>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Paid From</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($expenses as $expense)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $expense->expense_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                            {{ $expense->category->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $expense->account->account_name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $expense->notes }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900 text-right">
                        ${{ number_format($expense->amount, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">No expenses recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
    <div class="p-6 border-t border-slate-100 bg-slate-50/50">
        {{ $expenses->links() }}
    </div>
    @endif
</div>

{{-- Add Modal --}}
<x-modal name="add-expense" focusable>
    <form action="{{ route('expenses.store') }}" method="POST" class="p-6">
        @csrf
        <h2 class="text-lg font-bold text-slate-800 mb-6 border-b pb-4">Record New Expense</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Expense Date *</label>
                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Category *</label>
                <select name="expense_category_id" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Amount *</label>
                <input type="number" step="0.01" name="amount" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Paid From (Credit Account) *</label>
                <select name="account_id" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                    <option value="">Select Account</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all"></textarea>
            </div>
        </div>
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm">Save Expense</button>
        </div>
    </form>
</x-modal>
@endsection
