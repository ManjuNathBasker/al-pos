@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. PAGE HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Products</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Manage your menu catalog, item pricing, and inventory stock tracking.</p>
        </div>

        <div>
            <a href="{{ route('products.create') }}" 
               class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Product</span>
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. TOOLBAR (SEARCH & FILTERS)
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
        <form action="{{ route('products.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex flex-1 items-center gap-3">
                {{-- Search Input (44px H) --}}
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products by name, SKU..." 
                           class="w-full h-11 pl-10 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E] transition-colors">
                </div>

                {{-- Submit Search Button --}}
                <button type="submit" class="h-11 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-sm font-medium text-[#172033] flex items-center gap-2 transition-colors">
                    <span>Search</span>
                </button>

                @if(request('search'))
                    <a href="{{ route('products.index') }}" class="h-11 px-3.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-[#64748B] flex items-center gap-1.5 transition-colors">
                        <span>Clear</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. MAIN PRODUCTS TABLE
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Product</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Category</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Price</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Stock</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($products as $product)
                    <tr class="hover:bg-[#FFF8F5] transition-colors group">
                        {{-- Product Name & Image --}}
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                @if($product->image)
                                <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 border border-[#E5E7EB] relative">
                                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $product->image) }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-full h-full items-center justify-center bg-slate-100 text-slate-400">
                                        <svg class="w-5 h-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                </div>
                                @else
                                <div class="w-10 h-10 rounded-lg bg-orange-50 text-[#F5703E] border border-orange-100 flex items-center justify-center flex-shrink-0 font-bold text-xs">
                                    {{ strtoupper(substr($product->name, 0, 2)) }}
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('products.edit', $product) }}" class="text-sm font-semibold text-[#172033] hover:text-[#F5703E] transition-colors block">
                                        {{ $product->name }}
                                    </a>
                                    <span class="text-xs text-[#64748B]">SKU: {{ $product->sku ?: 'N/A' }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td class="py-4 px-4 text-sm font-medium text-[#64748B]">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </td>

                        {{-- Price --}}
                        <td class="py-4 px-4 text-sm font-mono font-bold text-[#172033]">
                            ₹{{ number_format($product->price, 2) }}
                        </td>

                        {{-- Stock Tracking --}}
                        <td class="py-4 px-4 text-sm">
                            @if($product->track_stock)
                                @if($product->stock_qty <= $product->low_stock_threshold)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold font-mono bg-red-50 text-[#FF4848] border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF4848]"></span>
                                        {{ $product->stock_qty }} {{ $product->unit->abbreviation ?? 'pcs' }} (Low)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium font-mono text-[#172033]">
                                        {{ $product->stock_qty }} {{ $product->unit->abbreviation ?? 'pcs' }}
                                    </span>
                                @endif
                            @else
                                <span class="text-xs text-[#94A3B8]">Not tracked</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="py-4 px-4">
                            @if($product->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">Active</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-[#64748B] border border-slate-200">Inactive</span>
                            @endif
                        </td>

                        {{-- Action Buttons --}}
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                {{-- Edit Button --}}
                                <a href="{{ route('products.edit', $product) }}" title="Edit Product"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>

                                {{-- Delete Button Form --}}
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Product"
                                            class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">
                                🍽
                            </div>
                            <h3 class="text-sm font-bold text-[#172033]">
                                @if(request('search'))
                                    No products found matching "{{ request('search') }}"
                                @else
                                    No products created yet
                                @endif
                            </h3>
                            <p class="text-xs text-[#64748B] mt-1 max-w-sm mx-auto">
                                Add products with pricing, category tags, and optional stock tracking.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. Pagination Bar --}}
        @if($products->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
