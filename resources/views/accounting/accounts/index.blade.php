@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Chart of Accounts</h2>
        <p class="mt-1 text-sm text-slate-500">Manage your financial accounts and ledgers.</p>
    </div>
    <button @click="$dispatch('open-modal', 'add-account')" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Account
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Account Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Balance</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($accounts as $account)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $account->account_code ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900">{{ $account->account_name }}</div>
                        @if($account->parent)
                        <div class="text-xs text-slate-500">Sub-account of: {{ $account->parent->account_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $account->account_type }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900 text-right">
                        ${{ number_format($account->current_balance, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        @if($account->status)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Inactive</span>
                        @endif
                        @if($account->is_system)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-1">System</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        @if(!$account->is_system)
                        <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">No accounts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($accounts->hasPages())
    <div class="p-6 border-t border-slate-100 bg-slate-50/50">
        {{ $accounts->links() }}
    </div>
    @endif
</div>

{{-- Add Modal --}}
<x-modal name="add-account" focusable>
    <form action="{{ route('accounts.store') }}" method="POST" class="p-6">
        @csrf
        <h2 class="text-lg font-bold text-slate-800 mb-6 border-b pb-4">Add New Account</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Account Name *</label>
                <input type="text" name="account_name" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Account Code</label>
                <input type="text" name="account_code" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Account Type *</label>
                <select name="account_type" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                    <option value="Asset">Asset</option>
                    <option value="Liability">Liability</option>
                    <option value="Equity">Equity</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Parent Account</label>
                <select name="parent_account_id" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                    <option value="">None</option>
                    @foreach($parentAccounts as $pAccount)
                        <option value="{{ $pAccount->id }}">{{ $pAccount->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" value="0.00" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
        </div>
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm">Add Account</button>
        </div>
    </form>
</x-modal>
@endsection
