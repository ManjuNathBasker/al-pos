@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl">

    {{-- Back Link & Header --}}
    <div>
        <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#64748B] hover:text-[#F5703E] transition-colors mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Categories</span>
        </a>
        <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Add Category</h1>
        <p class="text-sm text-[#64748B] mt-0.5">Create a category to group related food or retail items.</p>
    </div>

    <form action="{{ route('categories.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Section: Category Configuration --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 space-y-5">
            <h3 class="text-sm font-semibold text-[#172033] border-b border-[#E5E7EB] pb-3">Category Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Name --}}
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-[#172033]">Category Name <span class="text-[#FF4848]">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                           class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. Hot Beverages, Desserts">
                    @error('name')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-[#172033]">Description</label>
                    <textarea id="description" name="description" rows="3" 
                              class="mt-1.5 w-full p-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="Optional details about this category...">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Icon (Text / Emoji) --}}
                <div>
                    <label for="icon" class="block text-xs font-semibold text-[#172033]">Icon (Emoji / Text)</label>
                    <input type="text" name="icon" id="icon" value="{{ old('icon') }}" placeholder="e.g. 🍕, ☕, 🍰"
                           class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    @error('icon')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Color --}}
                <div>
                    <label for="color" class="block text-xs font-semibold text-[#172033]">Color Accent</label>
                    <div class="mt-1.5 flex items-center gap-3">
                        <input type="color" name="color_picker" id="color_picker" value="{{ old('color', '#F5703E') }}" 
                               class="h-11 w-14 p-1 border border-[#E5E7EB] rounded-lg cursor-pointer bg-white"
                               onchange="document.getElementById('color').value = this.value">
                        <input type="text" name="color" id="color" value="{{ old('color', '#F5703E') }}" placeholder="#F5703E"
                               class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"
                               onchange="document.getElementById('color_picker').value = this.value">
                    </div>
                    @error('color')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>

                {{-- Sort Order --}}
                <div>
                    <label for="sort_order" class="block text-xs font-semibold text-[#172033]">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" 
                           class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    <p class="mt-1 text-[11px] text-[#64748B]">Lower numbers appear first on POS buttons.</p>
                    @error('sort_order')<p class="mt-1 text-xs text-[#FF4848]">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} 
                           class="w-4 h-4 rounded border-[#E5E7EB] text-[#F5703E] focus:ring-[#F5703E]">
                    <div>
                        <span class="text-xs font-semibold text-[#172033] block">Active Status</span>
                        <span class="text-[11px] text-[#64748B]">Show this category and its products in the POS catalog.</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('categories.index') }}" 
               class="h-11 px-5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-sm font-medium text-[#172033] transition-colors flex items-center justify-center">
                Cancel
            </a>
            <button type="submit" 
                    class="h-11 px-6 rounded-lg bg-[#F5703E] hover:bg-[#E05826] text-white text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                <span>Save Category</span>
            </button>
        </div>
    </form>

</div>
@endsection
