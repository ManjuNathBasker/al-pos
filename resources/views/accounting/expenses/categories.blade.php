@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('expenses.index') }}" 
               class="w-9 h-9 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#64748B] flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Expense Categories</h1>
                <p class="text-sm text-[#64748B] mt-0.5">Organize expenses into named categories for better reporting.</p>
            </div>
        </div>
        <button @click="$dispatch('open-modal', 'add-category')" 
                class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>New Category</span>
        </button>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Name</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Description</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($categories as $cat)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-[#172033]">{{ $cat->name }}</td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">{{ $cat->description ?: '—' }}</td>
                        <td class="py-4 px-4 text-right">
                            <form action="{{ route('expense-categories.destroy', $cat) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this category?');">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete Category"
                                        class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors ml-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-12 text-center">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-lg mx-auto mb-2 border border-orange-100">🏷</div>
                            <p class="text-sm font-medium text-[#172033]">No categories yet</p>
                            <p class="text-xs text-[#64748B] mt-0.5">Add categories to classify your expenses.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Category Modal --}}
<x-modal name="add-category" focusable>
    <form action="{{ route('expense-categories.store') }}" method="POST" class="p-6 space-y-5">
        @csrf
        <div class="border-b border-[#E5E7EB] pb-3">
            <h2 class="text-base font-semibold text-[#172033]">New Expense Category</h2>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Name <span class="text-[#FF4848]">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Utilities"
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Description</label>
                <textarea name="description" rows="2" placeholder="Optional description..."
                          class="mt-1 w-full p-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"></textarea>
            </div>
        </div>
        <div class="pt-4 border-t border-[#E5E7EB] flex justify-end gap-2.5">
            <button type="button" x-on:click="$dispatch('close')" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] transition-colors">Cancel</button>
            <button type="submit" class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">Save Category</button>
        </div>
    </form>
</x-modal>
@endsection
