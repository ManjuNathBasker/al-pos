@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. PAGE HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Categories</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Organize and group menu items and products for POS navigation.</p>
        </div>

        <div>
            <a href="{{ route('categories.create') }}" 
               class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Category</span>
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. TOOLBAR (SEARCH & FILTERS)
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
        <form action="{{ route('categories.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex flex-1 items-center gap-3">
                {{-- Search Input (44px H) --}}
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..." 
                           class="w-full h-11 pl-10 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E] transition-colors">
                </div>

                {{-- Submit Search Button --}}
                <button type="submit" class="h-11 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-sm font-medium text-[#172033] flex items-center gap-2 transition-colors">
                    <span>Search</span>
                </button>

                @if(request('search'))
                    <a href="{{ route('categories.index') }}" class="h-11 px-3.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-[#64748B] flex items-center gap-1.5 transition-colors">
                        <span>Clear</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. MAIN CATEGORIES TABLE
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Category</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Appearance</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Sort Order</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Products</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($categories as $category)
                    <tr class="hover:bg-[#FFF8F5] transition-colors group">
                        {{-- Category Name & Slug --}}
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0 border"
                                     style="background-color: {{ $category->color ? $category->color . '20' : '#FFF3EE' }}; color: {{ $category->color ?: '#F5703E' }}; border-color: {{ $category->color ? $category->color . '40' : '#FFD7C5' }};">
                                    {{ $category->icon ?: strtoupper(substr($category->name, 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('categories.edit', $category) }}" class="text-sm font-semibold text-[#172033] hover:text-[#F5703E] transition-colors block">
                                        {{ $category->name }}
                                    </a>
                                    <span class="text-xs text-[#64748B]">/{{ $category->slug }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Appearance --}}
                        <td class="py-4 px-4 text-sm text-[#64748B]">
                            <div class="flex items-center gap-2">
                                @if($category->color)
                                    <span class="w-3.5 h-3.5 rounded-full inline-block border border-slate-300" style="background-color: {{ $category->color }};"></span>
                                    <span class="text-xs font-mono text-[#172033]">{{ $category->color }}</span>
                                @else
                                    <span class="text-xs text-[#94A3B8]">Default</span>
                                @endif
                            </div>
                        </td>

                        {{-- Sort Order --}}
                        <td class="py-4 px-4 text-sm font-mono text-[#172033]">
                            {{ $category->sort_order }}
                        </td>

                        {{-- Products Count --}}
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-[#172033] border border-slate-200">
                                {{ $category->products_count ?? 0 }} {{ Str::plural('product', $category->products_count ?? 0) }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="py-4 px-4">
                            @if($category->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">Active</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-[#64748B] border border-slate-200">Inactive</span>
                            @endif
                        </td>

                        {{-- Action Buttons --}}
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                {{-- Edit Button --}}
                                <a href="{{ route('categories.edit', $category) }}" title="Edit Category"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>

                                {{-- Delete Button Form --}}
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Category"
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
                                🏷
                            </div>
                            <h3 class="text-sm font-bold text-[#172033]">
                                @if(request('search'))
                                    No categories found matching "{{ request('search') }}"
                                @else
                                    No categories created yet
                                @endif
                            </h3>
                            <p class="text-xs text-[#64748B] mt-1 max-w-sm mx-auto">
                                Create product categories to organize items in your POS catalog.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. Pagination Bar --}}
        @if($categories->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
