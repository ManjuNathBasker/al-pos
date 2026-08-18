@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Chart of Accounts</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Manage your financial accounts, ledgers, and chart hierarchy.</p>
        </div>
        <button @click="$dispatch('open-modal', 'add-account')" 
                class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>New Account</span>
        </button>
    </div>

    {{-- Main Accounts Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Code</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Account Name</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Type</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Balance</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-xs font-mono font-semibold text-[#64748B]">{{ $account->account_code ?? '—' }}</td>
                        <td class="py-4 px-4">
                            <div class="text-sm font-semibold text-[#172033]">{{ $account->account_name }}</div>
                            @if($account->parent)
                            <div class="text-xs text-[#64748B]">Sub-account of: {{ $account->parent->account_name }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs font-medium text-[#64748B]">{{ $account->account_type }}</td>
                        <td class="py-4 px-4 text-sm font-mono font-bold text-[#172033] text-right">@currency($account->current_balance)</td>
                        <td class="py-4 px-4">
                            <div class="flex flex-wrap gap-1">
                                @if($account->status)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-[#29AB6C] border border-emerald-200">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-[#64748B] border border-slate-200">Inactive</span>
                                @endif
                                @if($account->is_system)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-600 border border-blue-200">System</span>
                                @endif
                                @if($account->show_in_pos)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-600 border border-purple-200">POS</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button type="button" @click="$dispatch('open-modal', 'edit-account-{{ $account->id }}')" title="Edit Account"
                                        class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                @if(!$account->is_system)
                                <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this account?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Delete Account"
                                            class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                @endif

                                {{-- Edit Modal --}}
                                <x-modal name="edit-account-{{ $account->id }}" focusable>
                                    <form action="{{ route('accounts.update', $account) }}" method="POST" class="p-6 text-left space-y-5">
                                        @csrf @method('PUT')
                                        <div class="border-b border-[#E5E7EB] pb-3">
                                            <h2 class="text-base font-semibold text-[#172033]">Edit Account</h2>
                                            @if($account->is_system)<p class="text-xs text-amber-600 mt-0.5">⚠ System account — some fields are read-only</p>@endif
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-[#172033]">Account Name <span class="text-[#FF4848]">*</span></label>
                                                <input type="text" name="account_name" value="{{ $account->account_name }}" required {{ $account->is_system ? 'readonly' : '' }}
                                                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E] {{ $account->is_system ? 'bg-slate-50 text-[#64748B]' : '' }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-[#172033]">Account Code</label>
                                                <input type="text" name="account_code" value="{{ $account->account_code }}" {{ $account->is_system ? 'readonly' : '' }}
                                                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-[#172033]">Account Type <span class="text-[#FF4848]">*</span></label>
                                                @if($account->is_system)
                                                    <input type="text" value="{{ $account->account_type }}" readonly class="mt-1 w-full h-11 px-3.5 bg-slate-50 border border-[#E5E7EB] rounded-lg text-sm text-[#64748B]">
                                                @else
                                                    <select name="account_type" required class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                                                        <option value="Asset" {{ $account->account_type == 'Asset' ? 'selected' : '' }}>Asset</option>
                                                        <option value="Liability" {{ $account->account_type == 'Liability' ? 'selected' : '' }}>Liability</option>
                                                        <option value="Equity" {{ $account->account_type == 'Equity' ? 'selected' : '' }}>Equity</option>
                                                        <option value="Income" {{ $account->account_type == 'Income' ? 'selected' : '' }}>Income</option>
                                                        <option value="Expense" {{ $account->account_type == 'Expense' ? 'selected' : '' }}>Expense</option>
                                                    </select>
                                                @endif
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-[#172033]">Parent Account</label>
                                                @if($account->is_system)
                                                    <input type="text" value="{{ $account->parent ? $account->parent->account_name : 'None' }}" readonly class="mt-1 w-full h-11 px-3.5 bg-slate-50 border border-[#E5E7EB] rounded-lg text-sm text-[#64748B]">
                                                @else
                                                    <select name="parent_account_id" class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                                                        <option value="">None</option>
                                                        @foreach($parentAccounts as $pAccount)
                                                            @if($pAccount->id != $account->id)
                                                                <option value="{{ $pAccount->id }}" {{ $account->parent_account_id == $pAccount->id ? 'selected' : '' }}>{{ $pAccount->account_name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                                                <input type="checkbox" name="show_in_pos" id="edit_show_pos_{{ $account->id }}" value="1" {{ $account->show_in_pos ? 'checked' : '' }}
                                                       class="w-4 h-4 rounded border-[#E5E7EB] text-[#F5703E] focus:ring-[#F5703E]">
                                                <label for="edit_show_pos_{{ $account->id }}" class="text-xs font-medium text-[#172033]">Show as Payment Method in POS</label>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-[#E5E7EB] flex justify-end gap-2.5">
                                            <button type="button" x-on:click="$dispatch('close')" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] transition-colors">Cancel</button>
                                            <button type="submit" class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">Save Changes</button>
                                        </div>
                                    </form>
                                </x-modal>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">📒</div>
                            <h3 class="text-sm font-bold text-[#172033]">No accounts found</h3>
                            <p class="text-xs text-[#64748B] mt-1">Add your first account to start managing your chart of accounts.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">{{ $accounts->links() }}</div>
        @endif
    </div>
</div>

{{-- Add New Account Modal --}}
<x-modal name="add-account" focusable>
    <form action="{{ route('accounts.store') }}" method="POST" class="p-6 space-y-5">
        @csrf
        <div class="border-b border-[#E5E7EB] pb-3">
            <h2 class="text-base font-semibold text-[#172033]">New Account</h2>
            <p class="text-xs text-[#64748B] mt-0.5">Add a new ledger account to the chart of accounts</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Account Name <span class="text-[#FF4848]">*</span></label>
                <input type="text" name="account_name" required placeholder="e.g. Cash in Hand"
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Account Code</label>
                <input type="text" name="account_code" placeholder="e.g. 1001"
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Account Type <span class="text-[#FF4848]">*</span></label>
                <select name="account_type" required class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    <option value="Asset">Asset</option>
                    <option value="Liability">Liability</option>
                    <option value="Equity">Equity</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Parent Account</label>
                <select name="parent_account_id" class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    <option value="">None (Top Level)</option>
                    @foreach($parentAccounts as $pAccount)
                        <option value="{{ $pAccount->id }}">{{ $pAccount->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-[#172033]">Opening Balance (@currencySymbol)</label>
                <input type="number" step="0.01" name="opening_balance" value="0.00"
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div class="sm:col-span-2 flex items-center gap-2">
                <input type="checkbox" name="show_in_pos" id="show_in_pos" value="1" class="w-4 h-4 rounded border-[#E5E7EB] text-[#F5703E] focus:ring-[#F5703E]">
                <label for="show_in_pos" class="text-xs font-medium text-[#172033]">Show as Payment Method in POS</label>
            </div>
        </div>
        <div class="pt-4 border-t border-[#E5E7EB] flex justify-end gap-2.5">
            <button type="button" x-on:click="$dispatch('close')" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] transition-colors">Cancel</button>
            <button type="submit" class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">Add Account</button>
        </div>
    </form>
</x-modal>
@endsection
