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
    <form action="{{ route('companies.store') }}" method="POST"
          x-data="{
              presets: {
                  'INR': { name: 'Indian Rupee', code: 'INR', symbol: '₹', decimals: 2, position: 'before' },
                  'USD': { name: 'US Dollar', code: 'USD', symbol: '$', decimals: 2, position: 'before' },
                  'EUR': { name: 'Euro', code: 'EUR', symbol: '€', decimals: 2, position: 'after' },
                  'GBP': { name: 'British Pound', code: 'GBP', symbol: '£', decimals: 2, position: 'before' },
                  'AED': { name: 'UAE Dirham', code: 'AED', symbol: 'د.إ', decimals: 2, position: 'after' },
                  'SAR': { name: 'Saudi Riyal', code: 'SAR', symbol: '﷼', decimals: 2, position: 'after' },
                  'QAR': { name: 'Qatari Riyal', code: 'QAR', symbol: 'ر.ق', decimals: 2, position: 'after' },
                  'CAD': { name: 'Canadian Dollar', code: 'CAD', symbol: 'CA$', decimals: 2, position: 'before' },
                  'AUD': { name: 'Australian Dollar', code: 'AUD', symbol: 'A$', decimals: 2, position: 'before' },
                  'SGD': { name: 'Singapore Dollar', code: 'SGD', symbol: 'S$', decimals: 2, position: 'before' },
                  'JPY': { name: 'Japanese Yen', code: 'JPY', symbol: '¥', decimals: 0, position: 'before' }
              },
              currencyName: '{{ old('currency_name', $currencyConfig['name'] ?? 'Indian Rupee') }}',
              currencyCode: '{{ old('currency_code', $currencyConfig['code'] ?? 'INR') }}',
              currencySymbol: '{{ old('currency_symbol', $currencyConfig['symbol'] ?? '₹') }}',
              decimalPlaces: {{ old('currency_decimal_places', $currencyConfig['decimal_places'] ?? 2) }},
              symbolPosition: '{{ old('currency_symbol_position', $currencyConfig['symbol_position'] ?? 'before') }}',
              applyPreset(code) {
                  if (this.presets[code]) {
                      const p = this.presets[code];
                      this.currencyName = p.name;
                      this.currencyCode = p.code;
                      this.currencySymbol = p.symbol;
                      this.decimalPlaces = p.decimals;
                      this.symbolPosition = p.position;
                  }
              }
          }">
        @csrf
        <div class="space-y-6">
            {{-- Company Information Card --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/50">
                    <h3 class="text-sm font-semibold text-[#172033]">Company Profile</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Name --}}
                        <div class="md:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-[#172033] mb-1.5">Company Name <span class="text-[#FF4848]">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. Acme Supermarket">
                            @error('name') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-xs font-semibold text-[#172033] mb-1.5">Business Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]" placeholder="contact@company.com">
                            @error('email') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-[#172033] mb-1.5">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                   class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]" placeholder="+1 234 567 8900">
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

                    {{-- Company Status --}}
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-semibold text-[#172033] mb-2">Initial Status</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="relative flex items-center p-3 rounded-lg border cursor-pointer {{ old('is_active', '1') == '1' ? 'bg-emerald-50/50 border-emerald-300' : 'bg-white border-[#E5E7EB]' }}">
                                <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="text-[#F5703E] focus:ring-[#F5703E] h-4 w-4 border-slate-300">
                                <div class="ml-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="text-xs font-bold text-[#172033]">Active</span>
                                    </div>
                                    <p class="text-[11px] text-[#64748B] mt-0.5">Ready for immediate operation.</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-3 rounded-lg border cursor-pointer {{ old('is_active') === '0' ? 'bg-rose-50/50 border-rose-300' : 'bg-white border-[#E5E7EB]' }}">
                                <input type="radio" name="is_active" value="0" {{ old('is_active') === '0' ? 'checked' : '' }} class="text-[#F5703E] focus:ring-[#F5703E] h-4 w-4 border-slate-300">
                                <div class="ml-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        <span class="text-xs font-bold text-[#172033]">Inactive</span>
                                    </div>
                                    <p class="text-[11px] text-[#64748B] mt-0.5">Created in disabled state.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Currency Settings Card --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-[#172033]">Base Currency Configuration</h3>
                        <p class="text-xs text-[#64748B] mt-0.5">Select the primary currency used across products, checkout, and invoices.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-400">Preset:</span>
                        <select @change="applyPreset($event.target.value)" class="text-xs rounded-lg border-slate-200 text-slate-700 py-1.5 px-2.5 bg-white font-medium focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="INR">INR — Indian Rupee (₹)</option>
                            <option value="USD">USD — US Dollar ($)</option>
                            <option value="AED">AED — UAE Dirham (د.إ)</option>
                            <option value="EUR">EUR — Euro (€)</option>
                            <option value="GBP">GBP — British Pound (£)</option>
                            <option value="SAR">SAR — Saudi Riyal (﷼)</option>
                            <option value="QAR">QAR — Qatari Riyal (ر.ق)</option>
                            <option value="CAD">CAD — Canadian Dollar (CA$)</option>
                            <option value="AUD">AUD — Australian Dollar (A$)</option>
                            <option value="SGD">SGD — Singapore Dollar (S$)</option>
                            <option value="JPY">JPY — Japanese Yen (¥)</option>
                        </select>
                    </div>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Name --}}
                        <div>
                            <label for="currency_name" class="block text-xs font-semibold text-[#172033] mb-1.5">Currency Name</label>
                            <input type="text" name="currency_name" id="currency_name" x-model="currencyName" required
                                   class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                        </div>

                        {{-- Code --}}
                        <div>
                            <label for="currency_code" class="block text-xs font-semibold text-[#172033] mb-1.5">Currency Code</label>
                            <input type="text" name="currency_code" id="currency_code" x-model="currencyCode" required maxlength="10"
                                   class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono uppercase text-[#172033] focus:outline-none focus:border-[#F5703E]">
                        </div>

                        {{-- Symbol --}}
                        <div>
                            <label for="currency_symbol" class="block text-xs font-semibold text-[#172033] mb-1.5">Currency Symbol</label>
                            <input type="text" name="currency_symbol" id="currency_symbol" x-model="currencySymbol" required maxlength="10"
                                   class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E]">
                        </div>

                        {{-- Decimals --}}
                        <div>
                            <label for="currency_decimal_places" class="block text-xs font-semibold text-[#172033] mb-1.5">Decimal Places</label>
                            <input type="number" name="currency_decimal_places" id="currency_decimal_places" x-model.number="decimalPlaces" min="0" max="4" required
                                   class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E]">
                        </div>

                        {{-- Position --}}
                        <div class="md:col-span-2">
                            <label for="currency_symbol_position" class="block text-xs font-semibold text-[#172033] mb-1.5">Symbol Position</label>
                            <select name="currency_symbol_position" id="currency_symbol_position" x-model="symbolPosition" required
                                    class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                                <option value="before">Before Amount (e.g. $1,250.00)</option>
                                <option value="after">After Amount (e.g. 1,250.00 €)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-slate-50/75 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-[#E5E7EB]">
                    <a href="{{ route('companies.index') }}" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] flex items-center transition-colors">Cancel</a>
                    <button type="submit" class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">Save Company</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
