@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl">

    {{-- Back Link & Header --}}
    <div>
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#64748B] hover:text-[#F5703E] transition-colors mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Products</span>
        </a>
        <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Add Product</h1>
        <p class="text-sm text-[#64748B] mt-0.5">Create a new item in your store menu or inventory catalog.</p>
    </div>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section 1: Basic Information --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 space-y-5">
            <h3 class="text-sm font-semibold text-[#172033] border-b border-[#E5E7EB] pb-3">Basic Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Product Name --}}
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-[#172033]">Product Name <span class="text-[#FF4848]">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                           class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. Margherita Pizza">
                    @error('name')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-[#172033]">Category <span class="text-[#FF4848]">*</span></label>
                    <select id="category_id" name="category_id" required 
                            class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- SKU --}}
                <div>
                    <label for="sku" class="block text-xs font-semibold text-[#172033]">SKU Code</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" 
                           class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. PIZ-MAR-01">
                    @error('sku')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Barcode --}}
                <div class="md:col-span-2">
                    <label for="barcode" class="block text-xs font-semibold text-[#172033]">Barcode / UPC</label>
                    <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}" 
                           class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="Scan or enter barcode number">
                    @error('barcode')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-[#172033]">Description / Ingredients</label>
                    <textarea id="description" name="description" rows="3" 
                              class="mt-1.5 w-full p-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="Add preparation notes or item description...">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Pricing & Taxation --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 space-y-5">
            <h3 class="text-sm font-semibold text-[#172033] border-b border-[#E5E7EB] pb-3">Pricing & Taxation</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Selling Price --}}
                <div>
                    <label for="price" class="block text-xs font-semibold text-[#172033]">Selling Price (@currencySymbol) <span class="text-[#FF4848]">*</span></label>
                    <div class="relative mt-1.5">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[#64748B] font-mono text-sm font-semibold">@currencySymbol</span>
                        <input type="number" step="0.01" name="price" id="price" value="{{ old('price', '0.00') }}" required 
                               class="w-full h-11 pl-8 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    </div>
                    @error('price')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Cost Price --}}
                <div>
                    <label for="cost_price" class="block text-xs font-semibold text-[#172033]">Cost Price (@currencySymbol)</label>
                    <div class="relative mt-1.5">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[#64748B] font-mono text-sm font-semibold">@currencySymbol</span>
                        <input type="number" step="0.01" name="cost_price" id="cost_price" value="{{ old('cost_price') }}" 
                               class="w-full h-11 pl-8 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="0.00">
                    </div>
                    @error('cost_price')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Tax Rate --}}
                <div>
                    <label for="tax_rate" class="block text-xs font-semibold text-[#172033]">Tax Rate (%) <span class="text-[#FF4848]">*</span></label>
                    <div class="relative mt-1.5">
                        <input type="number" name="tax_rate" id="tax_rate" value="{{ old('tax_rate', '0') }}" required 
                               class="w-full h-11 pl-3.5 pr-8 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#64748B] text-xs font-semibold">%</span>
                    </div>
                    @error('tax_rate')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Section 3: Inventory & Media --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 space-y-5">
            <h3 class="text-sm font-semibold text-[#172033] border-b border-[#E5E7EB] pb-3">Inventory & Visibility</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="stock_qty" class="block text-xs font-semibold text-[#172033]">Initial Stock Quantity <span class="text-[#FF4848]">*</span></label>
                    <input type="number" name="stock_qty" id="stock_qty" value="{{ old('stock_qty', '0') }}" required 
                           class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    @error('stock_qty')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="low_stock_threshold" class="block text-xs font-semibold text-[#172033]">Low Stock Alert Threshold <span class="text-[#FF4848]">*</span></label>
                    <input type="number" name="low_stock_threshold" id="low_stock_threshold" value="{{ old('low_stock_threshold', '5') }}" required 
                           class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    @error('low_stock_threshold')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="track_stock" value="1" {{ old('track_stock', true) ? 'checked' : '' }} 
                           class="w-4 h-4 rounded border-[#E5E7EB] text-[#F5703E] focus:ring-[#F5703E]">
                    <div>
                        <span class="text-xs font-semibold text-[#172033] block">Track Inventory Stock</span>
                        <span class="text-[11px] text-[#64748B]">Automatically deduct stock count when orders are placed in POS.</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} 
                           class="w-4 h-4 rounded border-[#E5E7EB] text-[#F5703E] focus:ring-[#F5703E]">
                    <div>
                        <span class="text-xs font-semibold text-[#172033] block">Active & Available in POS</span>
                        <span class="text-[11px] text-[#64748B]">Enable this product for cashiers and waiters.</span>
                    </div>
                </label>
            </div>

            <div class="pt-2">
                <label for="image" class="block text-xs font-semibold text-[#172033]">Product Image</label>
                <p class="text-[11px] text-[#64748B] mt-0.5 mb-1.5">
                    <strong>Recommended size:</strong> 500 × 500 px (Square aspect ratio, max 2MB. Formats: JPG, PNG, WEBP).
                </p>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp,image/gif" onchange="validateAndPreviewImage(this)"
                       class="mt-1.5 block w-full text-xs text-[#64748B] file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-[#F5703E] hover:file:bg-orange-100 border border-[#E5E7EB] rounded-lg p-2 bg-white">
                <p id="image-error" class="mt-1 text-xs text-[#FF4848] hidden"></p>
                
                {{-- Live Image Preview Container --}}
                <div id="image-preview-container" class="mt-3 hidden items-center gap-3 p-2 bg-slate-50 border border-[#E5E7EB] rounded-lg">
                    <img id="image-preview" src="#" alt="Preview" class="w-16 h-16 object-contain rounded-md bg-white border border-[#E5E7EB]">
                    <div>
                        <p id="image-file-info" class="text-xs font-medium text-[#172033]"></p>
                        <span class="text-[10px] text-emerald-600 font-semibold">Valid image file</span>
                    </div>
                </div>
                @error('image')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('products.index') }}" 
               class="h-11 px-5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-sm font-medium text-[#172033] transition-colors flex items-center justify-center">
                Cancel
            </a>
            <button type="submit" 
                    class="h-11 px-6 rounded-lg bg-[#F5703E] hover:bg-[#E05826] text-white text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                <span>Save Product</span>
            </button>
        </div>
    </form>

</div>

<script>
function validateAndPreviewImage(input) {
    const file = input.files[0];
    const errorEl = document.getElementById('image-error');
    const previewContainer = document.getElementById('image-preview-container');
    const previewImg = document.getElementById('image-preview');
    const fileInfo = document.getElementById('image-file-info');

    if (errorEl) { errorEl.classList.add('hidden'); errorEl.innerText = ''; }
    
    if (!file) {
        if (previewContainer) previewContainer.classList.add('hidden');
        return;
    }

    if (file.size > 2097152) {
        if (errorEl) {
            errorEl.innerText = 'File size exceeds 2MB limit (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Please choose a smaller image.';
            errorEl.classList.remove('hidden');
        }
        input.value = '';
        if (previewContainer) previewContainer.classList.add('hidden');
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        if (errorEl) {
            errorEl.innerText = 'Invalid file type. Only JPG, PNG, WEBP, and GIF images are allowed.';
            errorEl.classList.remove('hidden');
        }
        input.value = '';
        if (previewContainer) previewContainer.classList.add('hidden');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        if (previewImg) previewImg.src = e.target.result;
        if (fileInfo) fileInfo.innerText = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
        if (previewContainer) {
            previewContainer.classList.remove('hidden');
            previewContainer.classList.add('flex');
        }
    };
    reader.readAsDataURL(file);
}
</script>
@endsection
