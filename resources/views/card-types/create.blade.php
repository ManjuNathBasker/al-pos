@extends('layouts.app')

@section('content')
<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('card-types.index') }}" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Add Card Type</h2>
        <p class="mt-1 text-sm text-slate-500">Create a new card commission type for POS checkout.</p>
    </div>
</div>

<form action="{{ route('card-types.store') }}" method="POST" class="max-w-2xl space-y-6">
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 space-y-5">

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Card Type Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       placeholder="e.g. Standard, Premium, Corporate"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none transition-all @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Commission Type + Value --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="commission_type" class="block text-sm font-semibold text-slate-700 mb-1.5">Commission Type <span class="text-red-500">*</span></label>
                    <select name="commission_type" id="commission_type"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-white focus:border-indigo-400 outline-none">
                        <option value="percentage" {{ old('commission_type','percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('commission_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                    </select>
                    @error('commission_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="commission_value" class="block text-sm font-semibold text-slate-700 mb-1.5">Commission Value <span class="text-red-500">*</span></label>
                    <input type="number" name="commission_value" id="commission_value" value="{{ old('commission_value', 0) }}"
                           step="0.0001" min="0" max="100"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none font-mono @error('commission_value') border-red-400 @enderror">
                    @error('commission_value')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Commission Handling --}}
            <div>
                <label for="commission_handling" class="block text-sm font-semibold text-slate-700 mb-1.5">Commission Handling <span class="text-red-500">*</span></label>
                <select name="commission_handling" id="commission_handling"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-white focus:border-indigo-400 outline-none">
                    <option value="ignore" {{ old('commission_handling','ignore') === 'ignore' ? 'selected' : '' }}>Ignore (track only)</option>
                    <option value="auto_write_off" {{ old('commission_handling') === 'auto_write_off' ? 'selected' : '' }}>Auto Write-Off (journal entry on sale)</option>
                    <option value="settlement_tracking" {{ old('commission_handling') === 'settlement_tracking' ? 'selected' : '' }}>Settlement Tracking (future phase)</option>
                </select>
                @error('commission_handling')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-xs text-slate-400">Auto Write-Off automatically creates a journal entry deducting the commission on each card sale.</p>
            </div>

            {{-- Expense Account --}}
            <div>
                <label for="expense_account_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Commission Expense Account</label>
                <select name="expense_account_id" id="expense_account_id"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-white focus:border-indigo-400 outline-none">
                    <option value="">— Auto (system default) —</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('expense_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>
                @error('expense_account_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-xs text-slate-400">Leave blank to use the system "Card Processing Charges" account.</p>
            </div>

            {{-- Status --}}
            <div class="flex items-center gap-3">
                <input type="hidden" name="status" value="0">
                <input type="checkbox" name="status" id="status" value="1" checked
                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                <label for="status" class="text-sm font-semibold text-slate-700">Active (visible in POS)</label>
            </div>
        </div>

        <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('card-types.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                Create Card Type
            </button>
        </div>
    </div>
</form>
@endsection
