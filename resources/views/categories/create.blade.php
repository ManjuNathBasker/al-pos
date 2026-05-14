@extends('layouts.app')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('categories.index') }}" class="text-slate-400 hover:text-slate-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Add Category</h2>
            <p class="mt-1 text-sm text-slate-500">Create a new product category.</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden max-w-3xl">
    <form action="{{ route('categories.store') }}" method="POST" class="p-6 sm:p-8 space-y-8">
        @csrf

        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-2">
            
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700">Category Name <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <input type="text" name="name" id="name" required value="{{ old('name') }}" 
                           class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border">
                </div>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                <div class="mt-1">
                    <textarea id="description" name="description" rows="3" 
                              class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border">{{ old('description') }}</textarea>
                </div>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="icon" class="block text-sm font-medium text-slate-700">Icon (Text/Emoji)</label>
                <div class="mt-1">
                    <input type="text" name="icon" id="icon" value="{{ old('icon') }}" placeholder="e.g., ☕ or fa-coffee"
                           class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border">
                </div>
                @error('icon')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="color" class="block text-sm font-medium text-slate-700">Color (Hex Code)</label>
                <div class="mt-1 flex gap-3 items-center">
                    <input type="color" name="color_picker" id="color_picker" value="{{ old('color', '#3b82f6') }}" 
                           class="h-9 w-14 p-0 border-0 rounded cursor-pointer"
                           onchange="document.getElementById('color').value = this.value">
                    <input type="text" name="color" id="color" value="{{ old('color', '#3b82f6') }}" placeholder="#ffffff"
                           class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border"
                           onchange="document.getElementById('color_picker').value = this.value">
                </div>
                @error('color')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-slate-700">Sort Order</label>
                <div class="mt-1">
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" 
                           class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border">
                </div>
                <p class="mt-1 text-xs text-slate-500">Lower numbers appear first.</p>
                @error('sort_order')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2 mt-4">
                <div class="flex items-start">
                    <div class="flex h-5 items-center">
                        <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-slate-700">Active Status</label>
                        <p class="text-slate-500">Only active categories will be shown in the POS.</p>
                    </div>
                </div>
            </div>

        </div>

        <div class="pt-5 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('categories.index') }}" class="rounded-md border border-slate-300 bg-white py-2 px-4 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Cancel
            </a>
            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Save Category
            </button>
        </div>
    </form>
</div>
@endsection
