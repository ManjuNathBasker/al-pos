{{-- Shared form partial for Bank Offer create/edit --}}
@php $o = $offer; @endphp

{{-- Basic Info --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Offer Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-3">
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Offer Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $o->name ?? '') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" placeholder="e.g., HDFC 10% Cashback" />
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Start Date <span class="text-red-500">*</span></label>
            <input type="date" name="start_date" value="{{ old('start_date', isset($o) ? $o->start_date : now()->format('Y-m-d')) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
            @error('start_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">End Date <span class="text-red-500">*</span></label>
            <input type="date" name="end_date" value="{{ old('end_date', isset($o) ? $o->end_date : now()->addMonth()->format('Y-m-d')) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
            @error('end_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Usage Limit <span class="text-red-500">*</span></label>
            <input type="number" name="usage_limit" value="{{ old('usage_limit', $o->usage_limit ?? 0) }}" min="0" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
            <p class="text-[10px] text-slate-400 mt-1">0 = Unlimited</p>
        </div>
    </div>
</div>

{{-- Discount Config --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Discount & Cashback</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Discount Type <span class="text-red-500">*</span></label>
            <select name="discount_type" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                <option value="percent" {{ old('discount_type', $o->discount_type ?? 'percent') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                <option value="flat" {{ old('discount_type', $o->discount_type ?? '') === 'flat' ? 'selected' : '' }}>Flat Amount ($)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Discount Value <span class="text-red-500">*</span></label>
            <input type="number" name="discount_value" value="{{ old('discount_value', $o->discount_value ?? 0) }}" step="0.01" min="0" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Max Discount Cap <span class="text-red-500">*</span></label>
            <input type="number" name="max_discount" value="{{ old('max_discount', $o->max_discount ?? 0) }}" step="0.01" min="0" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
            <p class="text-[10px] text-slate-400 mt-1">0 = No cap</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Cashback <span class="text-red-500">*</span></label>
            <input type="number" name="cashback" value="{{ old('cashback', $o->cashback ?? 0) }}" step="0.01" min="0" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Min Purchase <span class="text-red-500">*</span></label>
            <input type="number" name="min_purchase" value="{{ old('min_purchase', $o->min_purchase ?? 0) }}" step="0.01" min="0" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Merchant Contribution % <span class="text-red-500">*</span></label>
            <input type="number" name="merchant_contribution" value="{{ old('merchant_contribution', $o->merchant_contribution ?? 100) }}" step="0.01" min="0" max="100" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Bank Contribution % <span class="text-red-500">*</span></label>
            <input type="number" name="bank_contribution" value="{{ old('bank_contribution', $o->bank_contribution ?? 0) }}" step="0.01" min="0" max="100" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
        </div>
    </div>
</div>

{{-- Targeting --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Targeting (Optional)</h3>
    @php
        $selectedCards = old('cards', isset($o) ? $o->cards->pluck('id')->toArray() : []);
        $selectedProducts = old('products', isset($o) ? $o->products->pluck('id')->toArray() : []);
        $selectedCategories = old('categories', isset($o) ? $o->categories->pluck('id')->toArray() : []);
        $selectedBranches = old('branches', isset($o) ? $o->branches->pluck('id')->toArray() : []);
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Cards</label>
            <select name="cards[]" multiple size="4" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                @foreach($cards as $card)
                    <option value="{{ $card->id }}" {{ in_array($card->id, $selectedCards) ? 'selected' : '' }}>{{ $card->bank_name }} - {{ $card->card_network }} ({{ $card->card_type }})</option>
                @endforeach
            </select>
            <p class="text-[10px] text-slate-400 mt-1">Hold Ctrl to multi-select. Empty = all cards.</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Products</label>
            <select name="products[]" multiple size="4" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ in_array($product->id, $selectedProducts) ? 'selected' : '' }}>{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Categories</label>
            <select name="categories[]" multiple size="4" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ in_array($cat->id, $selectedCategories) ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Branches</label>
            <select name="branches[]" multiple size="4" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ in_array($branch->id, $selectedBranches) ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Flags --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <div class="flex flex-wrap gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $o->is_active ?? true) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
            <span class="text-sm font-bold text-slate-700">Active</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_emi_offer" value="1" {{ old('is_emi_offer', $o->is_emi_offer ?? false) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-purple-600 focus:ring-purple-500" />
            <span class="text-sm font-bold text-slate-700">EMI Offer</span>
        </label>
    </div>
</div>
