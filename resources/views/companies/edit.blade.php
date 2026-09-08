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
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Edit Company</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Update company profile, operational status, currency, and settings for {{ $company->name }}.</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('companies.update', $company) }}" method="POST" class="space-y-6"
          x-data="{
              presets: {
                  'INR': { name: 'Indian Rupee', code: 'INR', symbol: '₹', decimals: 2, position: 'before' },
                  'USD': { name: 'US Dollar', code: 'USD', symbol: '$', decimals: 2, position: 'before' },
                  'EUR': { name: 'Euro', code: 'EUR', symbol: '€', decimals: 2, position: 'after' },
                  'GBP': { name: 'British Pound', code: 'GBP', symbol: '£', decimals: 2, position: 'before' },
                  'AED': { name: 'UAE Dirham', code: 'AED', symbol: 'د.إ', decimals: 2, position: 'after' },
                  'SAR': { name: 'Saudi Riyal', code: 'SAR', symbol: '﷼', decimals: 2, position: 'after' },
                  'QAR': { name: 'Qatari Riyal', code: 'QAR', symbol: 'ر.ق', decimals: 2, position: 'after' },
                  'KWD': { name: 'Kuwaiti Dinar', code: 'KWD', symbol: 'د.ك', decimals: 3, position: 'after' },
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
              },
              formatSample(num) {
                  const d = Math.max(0, parseInt(this.decimalPlaces) || 0);
                  const formatted = num.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
                  return this.symbolPosition === 'after'
                      ? formatted + ' ' + this.currencySymbol
                      : this.currencySymbol + formatted;
              }
          }">
        @csrf
        @method('PUT')

        {{-- Card 1: Company Profile & Status --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-[#172033]">Company Profile</h3>
                    <p class="text-xs text-[#64748B] mt-0.5">Basic identity, business classification, and operational status.</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $company->is_active ? 'bg-emerald-50 text-[#29AB6C] border border-emerald-200' : 'bg-rose-50 text-rose-600 border border-rose-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $company->is_active ? 'bg-emerald-500' : 'bg-rose-500' }} mr-1.5"></span>
                    {{ $company->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-xs font-semibold text-[#172033] mb-1.5">Company Name <span class="text-[#FF4848]">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. Acme Supermarket">
                        @error('name') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#172033] mb-1.5">Business Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}"
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="contact@company.com">
                        @error('email') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-[#172033] mb-1.5">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}"
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="+1 234 567 8900">
                        @error('phone') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>

                    {{-- Business Type --}}
                    <div class="md:col-span-2">
                        <label for="business_type" class="block text-xs font-semibold text-[#172033] mb-1.5">Business Type <span class="text-[#FF4848]">*</span></label>
                        <select name="business_type" id="business_type" required
                                class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                            <option value="retail" {{ old('business_type', $company->business_type) == 'retail' ? 'selected' : '' }}>Retail Store</option>
                            <option value="restaurant" {{ old('business_type', $company->business_type) == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                            <option value="cafe" {{ old('business_type', $company->business_type) == 'cafe' ? 'selected' : '' }}>Cafe</option>
                            <option value="bakery" {{ old('business_type', $company->business_type) == 'bakery' ? 'selected' : '' }}>Bakery</option>
                            <option value="food_court" {{ old('business_type', $company->business_type) == 'food_court' ? 'selected' : '' }}>Food Court</option>
                            <option value="supermarket" {{ old('business_type', $company->business_type) == 'supermarket' ? 'selected' : '' }}>Supermarket</option>
                            <option value="bookstall" {{ old('business_type', $company->business_type) == 'bookstall' ? 'selected' : '' }}>Bookstall</option>
                            <option value="boutique" {{ old('business_type', $company->business_type) == 'boutique' ? 'selected' : '' }}>Boutique</option>
                            <option value="pharmacy" {{ old('business_type', $company->business_type) == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                        </select>
                        @error('business_type') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>

                    {{-- Address --}}
                    <div class="md:col-span-2">
                        <label for="address" class="block text-xs font-semibold text-[#172033] mb-1.5">Address</label>
                        <textarea id="address" name="address" rows="3" placeholder="Full business address..."
                                  class="w-full px-3.5 py-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">{{ old('address', $company->address) }}</textarea>
                        @error('address') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>

                    {{-- Company Status --}}
                    <div class="md:col-span-2 pt-3 border-t border-[#E5E7EB]">
                        <label class="block text-xs font-semibold text-[#172033] mb-2">Company Status <span class="text-[#FF4848]">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="relative flex items-center p-3.5 rounded-xl border cursor-pointer transition-all {{ old('is_active', $company->is_active ? '1' : '0') == '1' ? 'bg-emerald-50/60 border-emerald-300 ring-1 ring-emerald-400/30' : 'bg-white border-[#E5E7EB] hover:border-slate-300' }}">
                                <input type="radio" name="is_active" value="1" {{ old('is_active', $company->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="text-[#29AB6C] focus:ring-[#29AB6C] h-4 w-4 border-slate-300">
                                <div class="ml-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="text-xs font-bold text-[#172033]">Active</span>
                                    </div>
                                    <p class="text-[11px] text-[#64748B] mt-0.5">Company is enabled and fully accessible for POS sales, inventory, and operations.</p>
                                </div>
                            </label>

                            <label class="relative flex items-center p-3.5 rounded-xl border cursor-pointer transition-all {{ old('is_active', $company->is_active ? '1' : '0') == '0' ? 'bg-rose-50/60 border-rose-300 ring-1 ring-rose-400/30' : 'bg-white border-[#E5E7EB] hover:border-slate-300' }}">
                                <input type="radio" name="is_active" value="0" {{ old('is_active', $company->is_active ? '1' : '0') == '0' ? 'checked' : '' }} class="text-rose-600 focus:ring-rose-500 h-4 w-4 border-slate-300">
                                <div class="ml-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        <span class="text-xs font-bold text-[#172033]">Inactive</span>
                                    </div>
                                    <p class="text-[11px] text-[#64748B] mt-0.5">Deactivates the company. Users will be prevented from switching into this company.</p>
                                </div>
                            </label>
                        </div>
                        @error('is_active') <p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Currency Configuration --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-[#172033]">Base Currency Configuration</h3>
                    <p class="text-xs text-[#64748B] mt-0.5">Configure base currency, symbols, and formatting for all POS transactions.</p>
                </div>
                {{-- Quick Presets Dropdown --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-[#94A3B8]">Preset:</span>
                    <select @change="applyPreset($event.target.value)" class="text-xs rounded-lg border-[#E5E7EB] text-[#172033] py-1.5 px-2.5 bg-white font-medium focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                        <option value="">Select preset...</option>
                        <option value="INR">INR — Indian Rupee (₹)</option>
                        <option value="USD">USD — US Dollar ($)</option>
                        <option value="AED">AED — UAE Dirham (د.إ)</option>
                        <option value="EUR">EUR — Euro (€)</option>
                        <option value="GBP">GBP — British Pound (£)</option>
                        <option value="SAR">SAR — Saudi Riyal (﷼)</option>
                        <option value="QAR">QAR — Qatari Riyal (ر.ق)</option>
                        <option value="KWD">KWD — Kuwaiti Dinar (د.ك)</option>
                        <option value="CAD">CAD — Canadian Dollar (CA$)</option>
                        <option value="AUD">AUD — Australian Dollar (A$)</option>
                        <option value="SGD">SGD — Singapore Dollar (S$)</option>
                        <option value="JPY">JPY — Japanese Yen (¥)</option>
                    </select>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Currency Name --}}
                    <div>
                        <label for="currency_name" class="block text-xs font-semibold text-[#172033] mb-1.5">Currency Name <span class="text-[#FF4848]">*</span></label>
                        <input type="text" name="currency_name" id="currency_name" x-model="currencyName" required
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"
                               placeholder="e.g. Indian Rupee">
                        @error('currency_name')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                    </div>

                    {{-- Currency Code --}}
                    <div>
                        <label for="currency_code" class="block text-xs font-semibold text-[#172033] mb-1.5">Currency Code (ISO) <span class="text-[#FF4848]">*</span></label>
                        <input type="text" name="currency_code" id="currency_code" x-model="currencyCode" required maxlength="10"
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] font-mono uppercase placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"
                               placeholder="e.g. INR">
                        @error('currency_code')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                    </div>

                    {{-- Currency Symbol --}}
                    <div>
                        <label for="currency_symbol" class="block text-xs font-semibold text-[#172033] mb-1.5">Currency Symbol <span class="text-[#FF4848]">*</span></label>
                        <input type="text" name="currency_symbol" id="currency_symbol" x-model="currencySymbol" required maxlength="10"
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] font-mono placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"
                               placeholder="e.g. ₹ or $">
                        @error('currency_symbol')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                    </div>

                    {{-- Decimal Places --}}
                    <div>
                        <label for="currency_decimal_places" class="block text-xs font-semibold text-[#172033] mb-1.5">Decimal Places <span class="text-[#FF4848]">*</span></label>
                        <input type="number" name="currency_decimal_places" id="currency_decimal_places" x-model.number="decimalPlaces" min="0" max="4" required
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] font-mono focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                        @error('currency_decimal_places')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                    </div>

                    {{-- Symbol Position --}}
                    <div class="md:col-span-2">
                        <label for="currency_symbol_position" class="block text-xs font-semibold text-[#172033] mb-1.5">Symbol Position <span class="text-[#FF4848]">*</span></label>
                        <select name="currency_symbol_position" id="currency_symbol_position" x-model="symbolPosition" required
                                class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                            <option value="before">Before Amount (e.g. <span x-text="currencySymbol + '1,250.00'"></span>)</option>
                            <option value="after">After Amount (e.g. <span x-text="'1,250.00 ' + currencySymbol"></span>)</option>
                        </select>
                        @error('currency_symbol_position')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Live Interactive Formatting Preview Box --}}
                <div class="bg-gradient-to-r from-slate-50 to-orange-50/40 rounded-xl border border-[#E5E7EB] p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[11px] font-bold text-[#172033] uppercase tracking-wider">Live Currency Formatting Preview</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
                        <div class="bg-white p-3 rounded-lg border border-[#E5E7EB] shadow-xs">
                            <p class="text-[10px] font-semibold text-[#94A3B8] uppercase">Product Price</p>
                            <p class="text-base font-extrabold font-mono text-[#172033] mt-0.5" x-text="formatSample(1250)"></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-[#E5E7EB] shadow-xs">
                            <p class="text-[10px] font-semibold text-[#94A3B8] uppercase">Order Grand Total</p>
                            <p class="text-base font-extrabold font-mono text-[#F5703E] mt-0.5" x-text="formatSample(4890.50)"></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-[#E5E7EB] shadow-xs">
                            <p class="text-[10px] font-semibold text-[#94A3B8] uppercase">Discount Applied</p>
                            <p class="text-base font-extrabold font-mono text-[#29AB6C] mt-0.5" x-text="'-' + formatSample(250)"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: General Settings --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/50">
                <h3 class="text-sm font-semibold text-[#172033]">General Settings</h3>
                <p class="text-xs text-[#64748B] mt-0.5">Configure company-wide payment, tax, and card commission rules.</p>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Order Sales Tax --}}
                    <div>
                        <label for="tax_percentage" class="block text-xs font-semibold text-[#172033] mb-1">
                            POS Sales Tax Rate (%)
                        </label>
                        <p class="text-xs text-[#64748B] mb-2">
                            Default tax percentage applied to POS orders during checkout.
                        </p>
                        <div class="flex items-center gap-2">
                            <input type="number" name="tax_percentage" id="tax_percentage"
                                   value="{{ old('tax_percentage', $taxPercentage ?? 8.0) }}"
                                   step="0.01" min="0" max="100"
                                   class="w-32 h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E] @error('tax_percentage') border-[#FF4848] @enderror">
                            <span class="text-sm font-semibold text-[#64748B]">%</span>
                        </div>
                        @error('tax_percentage')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                    </div>

                    {{-- Card Commission Tax --}}
                    <div>
                        <label for="card_commission_tax" class="block text-xs font-semibold text-[#172033] mb-1">
                            Card Commission Tax (%)
                        </label>
                        <p class="text-xs text-[#64748B] mb-2">
                            Tax percentage applied on bank commission deduction during POS card settlements.
                        </p>
                        <div class="flex items-center gap-2">
                            <input type="number" name="card_commission_tax" id="card_commission_tax"
                                   value="{{ old('card_commission_tax', $cardCommissionTax ?? 0) }}"
                                   step="0.01" min="0" max="100"
                                   class="w-32 h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E] @error('card_commission_tax') border-[#FF4848] @enderror">
                            <span class="text-sm font-semibold text-[#64748B]">%</span>
                        </div>
                        @error('card_commission_tax')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Bottom Action Bar --}}
            <div class="bg-slate-50/75 px-6 py-4 flex items-center justify-end gap-3 border-t border-[#E5E7EB]">
                <a href="{{ route('companies.index') }}" 
                   class="h-11 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#64748B] text-sm font-medium transition-colors inline-flex items-center">
                    Cancel
                </a>
                <button type="submit" 
                        class="btn-brand h-11 px-5 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save All Changes</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
