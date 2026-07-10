@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('cards.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">&larr; Back to Card Types</a>
    <h2 class="text-2xl font-bold text-slate-800 mt-2">Edit Card Type</h2>
    <p class="mt-1 text-sm text-slate-500">Update service charge, MDR, and settlement configuration for <strong>{{ $card->bank_name }} {{ $card->card_network }}</strong>.</p>
</div>

<form action="{{ route('cards.update', $card) }}" method="POST" class="space-y-6 max-w-3xl">
    @csrf @method('PUT')

    {{-- Bank & Card Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Card Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Bank Name <span class="text-red-500">*</span></label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $card->bank_name) }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none"
                       placeholder="e.g., HDFC Bank" />
                @error('bank_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Card Network <span class="text-red-500">*</span></label>
                <select name="card_network" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                    <option value="">Select Network</option>
                    @foreach(['Visa', 'Mastercard', 'RuPay', 'Amex', 'Diners Club', 'JCB', 'Other'] as $network)
                        <option value="{{ $network }}" {{ old('card_network', $card->card_network) === $network ? 'selected' : '' }}>{{ $network }}</option>
                    @endforeach
                </select>
                @error('card_network') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Card Type <span class="text-red-500">*</span></label>
                <select name="card_type" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                    <option value="">Select Type</option>
                    @foreach(['Credit', 'Debit', 'EMI', 'Prepaid', 'Gift', 'Corporate', 'Custom'] as $type)
                        <option value="{{ $type }}" {{ old('card_type', $card->card_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('card_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- Charges & Fees --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Charges & Fees</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">
                    Service Charge % <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="service_charge" value="{{ old('service_charge', $card->service_charge) }}" step="0.01" min="0" max="100" required
                           class="w-full px-4 py-2.5 pr-10 bg-emerald-50 border border-emerald-200 rounded-xl text-sm font-bold text-emerald-800 focus:bg-white focus:border-indigo-400 outline-none" />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-500 font-bold text-sm">%</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1 leading-tight">Charged to the customer on card payments. This amount is collected by you and paid to the bank.</p>
                @error('service_charge') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">
                    MDR % <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="mdr" value="{{ old('mdr', $card->mdr) }}" step="0.01" min="0" max="100" required
                           class="w-full px-4 py-2.5 pr-10 bg-amber-50 border border-amber-200 rounded-xl text-sm font-bold text-amber-800 focus:bg-white focus:border-indigo-400 outline-none" />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-amber-500 font-bold text-sm">%</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1 leading-tight">Merchant Discount Rate — the bank's fee deducted from the settlement amount.</p>
                @error('mdr') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">
                    Processing Fee <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="processing_fee" value="{{ old('processing_fee', $card->processing_fee) }}" step="0.01" min="0" required
                           class="w-full px-4 py-2.5 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:bg-white focus:border-indigo-400 outline-none" />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">$</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1 leading-tight">Flat per-transaction processing fee charged by the bank.</p>
                @error('processing_fee') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- Settlement --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Settlement</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Settlement Account <span class="text-red-500">*</span></label>
                <select name="settlement_account_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                    <option value="">Select Account</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('settlement_account_id', $card->settlement_account_id) == $account->id ? 'selected' : '' }}>{{ $account->account_name }}</option>
                    @endforeach
                </select>
                @error('settlement_account_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Settlement Days <span class="text-red-500">*</span></label>
                <input type="number" name="settlement_days" value="{{ old('settlement_days', $card->settlement_days) }}" min="0" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
                @error('settlement_days') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- Status & Notes --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $card->is_active) ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm font-bold text-slate-700">Active</span>
            </label>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Notes</label>
            <textarea name="notes" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-400 outline-none" placeholder="Any additional notes...">{{ old('notes', $card->notes) }}</textarea>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
            Update Card Type
        </button>
        <a href="{{ route('cards.index') }}" class="px-6 py-3 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-200 transition-all">Cancel</a>
    </div>
</form>
@endsection
