@extends('layouts.app')

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('companies.index') }}" 
           class="w-9 h-9 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#64748B] flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Add Company</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Create a new company to manage products and orders.</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('companies.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-[#172033] mb-1.5">Company Name <span class="text-[#FF4848]">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                        @error('name') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#172033] mb-1.5">Business Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                        @error('email') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-[#172033] mb-1.5">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                        @error('phone') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>

                    {{-- Business Type --}}
                    <div class="md:col-span-2">
                        <label for="business_type" class="block text-xs font-semibold text-[#172033] mb-1.5">Business Type <span class="text-[#FF4848]">*</span></label>
                        <select name="business_type" id="business_type" required
                                class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                            <option value="retail" {{ old('business_type') == 'retail' ? 'selected' : '' }}>Retail Store</option>
                            <option value="restaurant" {{ old('business_type') == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                            <option value="cafe" {{ old('business_type') == 'cafe' ? 'selected' : '' }}>Cafe</option>
                            <option value="bakery" {{ old('business_type') == 'bakery' ? 'selected' : '' }}>Bakery</option>
                            <option value="food_court" {{ old('business_type') == 'food_court' ? 'selected' : '' }}>Food Court</option>
                            <option value="supermarket" {{ old('business_type') == 'supermarket' ? 'selected' : '' }}>Supermarket</option>
                            <option value="bookstall" {{ old('business_type') == 'bookstall' ? 'selected' : '' }}>Bookstall</option>
                            <option value="boutique" {{ old('business_type') == 'boutique' ? 'selected' : '' }}>Boutique</option>
                            <option value="pharmacy" {{ old('business_type') == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                        </select>
                        @error('business_type') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                        <p class="mt-1.5 text-xs text-[#94A3B8]">Business type will automatically enable relevant modules (e.g., Table Management for Restaurants).</p>
                    </div>
                </div>

                {{-- Address --}}
                <div>
                    <label for="address" class="block text-xs font-semibold text-[#172033] mb-1.5">Address</label>
                    <textarea id="address" name="address" rows="3" placeholder="Full business address..."
                              class="w-full px-3.5 py-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E]">{{ old('address') }}</textarea>
                    @error('address') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-slate-50/75 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-[#E5E7EB]">
                <a href="{{ route('companies.index') }}" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] flex items-center transition-colors">Cancel</a>
                <button type="submit" class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">Save Company</button>
            </div>
        </div>
    </form>
</div>
@endsection
