@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Expenses</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Track and manage all company operational expenses.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('expense-categories.index') }}" 
               class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#64748B] flex items-center transition-colors">
                Categories
            </a>
            <button @click="$dispatch('open-modal', 'add-expense')" 
                    class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Record Expense</span>
            </button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
        <form action="{{ route('expenses.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Filter by Category</label>
                <select name="expense_category_id" 
                        class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Filter by Account</label>
                <select name="account_id" 
                        class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm">Filter</button>
                @if(request()->hasAny(['expense_category_id', 'account_id']) && (request('expense_category_id') || request('account_id')))
                    <a href="{{ route('expenses.index') }}" class="h-11 px-3.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-[#64748B] flex items-center transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Expenses Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Category</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Paid From</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Notes</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($expenses as $expense)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-xs font-medium text-[#172033]">{{ $expense->expense_date->format('M d, Y') }}</td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-[#64748B] border border-slate-200">
                                {{ $expense->category->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">{{ $expense->account->account_name ?? 'N/A' }}</td>
                        <td class="py-4 px-4 text-xs text-[#64748B] max-w-xs truncate">{{ $expense->notes }}</td>
                        <td class="py-4 px-4 text-sm font-mono font-bold text-[#FF4848] text-right">₹{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">💸</div>
                            <h3 class="text-sm font-bold text-[#172033]">No expenses recorded yet</h3>
                            <p class="text-xs text-[#64748B] mt-1">Record your first expense to track company spending.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Expense Modal --}}
<x-modal name="add-expense" focusable>
    <form action="{{ route('expenses.store') }}" method="POST" class="p-6 space-y-5">
        @csrf
        <div class="border-b border-[#E5E7EB] pb-3">
            <h2 class="text-base font-semibold text-[#172033]">Record New Expense</h2>
            <p class="text-xs text-[#64748B] mt-0.5">Log an operational expense and debit from an account</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Expense Date <span class="text-[#FF4848]">*</span></label>
                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Category <span class="text-[#FF4848]">*</span></label>
                <select name="expense_category_id" required 
                        class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Amount (₹) <span class="text-[#FF4848]">*</span></label>
                <input type="number" step="0.01" name="amount" required placeholder="0.00"
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Paid From Account <span class="text-[#FF4848]">*</span></label>
                <select name="account_id" required 
                        class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    <option value="">Select Account</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-[#172033]">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional description..."
                          class="mt-1 w-full p-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"></textarea>
            </div>
        </div>
        <div class="pt-4 border-t border-[#E5E7EB] flex justify-end gap-2.5">
            <button type="button" x-on:click="$dispatch('close')" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] transition-colors">Cancel</button>
            <button type="submit" class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">Save Expense</button>
        </div>
    </form>
</x-modal>
@endsection
