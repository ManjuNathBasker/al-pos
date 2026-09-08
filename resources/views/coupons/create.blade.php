@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('coupons.index') }}" 
           class="w-9 h-9 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#64748B] flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Add New Coupon</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Create a discount coupon for customers to use at checkout.</p>
        </div>
    </div>

    {{-- Form Card --}}
    <form action="{{ route('coupons.store') }}" method="POST" class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Coupon Code <span class="text-[#FF4848]">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. SAVE20"
                       class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono uppercase text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                @error('code') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Status</label>
                <select name="is_active" class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Discount Type <span class="text-[#FF4848]">*</span></label>
                <select name="type" required class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    <option value="percent">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (@currencySymbol)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Discount Value <span class="text-[#FF4848]">*</span></label>
                <input type="number" name="value" step="0.01" value="{{ old('value') }}" required
                       class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                @error('value') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Expiry Date</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                       class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Usage Limit</label>
                <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" placeholder="Leave empty for unlimited"
                       class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E]">
            </div>
        </div>

        <div class="flex justify-end gap-2.5 pt-4 border-t border-[#E5E7EB]">
            <a href="{{ route('coupons.index') }}" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] flex items-center transition-colors">Cancel</a>
            <button type="submit" class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">Save Coupon</button>
        </div>
    </form>
</div>
@endsection
