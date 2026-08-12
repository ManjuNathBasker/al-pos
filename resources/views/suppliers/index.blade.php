@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. PAGE HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Suppliers</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Manage vendor contact profiles, tax numbers, and purchase balances.</p>
        </div>
        <div>
            <button @click="$dispatch('open-modal', 'add-supplier')" 
                    class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Supplier</span>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. TOOLBAR (SEARCH & FILTERS)
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
        <form action="{{ route('suppliers.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex flex-1 items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier, contact, phone, email..." 
                           class="w-full h-11 pl-10 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E] transition-colors">
                </div>

                <button type="submit" class="h-11 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-sm font-medium text-[#172033] flex items-center gap-2 transition-colors">
                    <span>Search</span>
                </button>

                @if(request('search'))
                    <a href="{{ route('suppliers.index') }}" class="h-11 px-3.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-[#64748B] flex items-center gap-1.5 transition-colors">
                        <span>Clear</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. MAIN SUPPLIERS TABLE
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Supplier</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Contact Person</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Phone & Email</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Opening Balance</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-[#FFF8F5] transition-colors group">
                        {{-- Supplier Name --}}
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 text-[#F5703E] flex items-center justify-center font-bold text-sm flex-shrink-0 border border-orange-200">
                                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-[#172033]">{{ $supplier->name }}</div>
                                    <span class="text-xs text-[#64748B]">ID: #SUPP-{{ str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Contact Person --}}
                        <td class="py-4 px-4 text-sm font-medium text-[#172033]">
                            {{ $supplier->contact_person ?: '—' }}
                        </td>

                        {{-- Contact Info --}}
                        <td class="py-4 px-4">
                            <div class="text-xs font-medium text-[#172033]">{{ $supplier->phone ?: '—' }}</div>
                            <div class="text-[11px] text-[#64748B]">{{ $supplier->email ?: '' }}</div>
                        </td>

                        {{-- Balance --}}
                        <td class="py-4 px-4 text-sm font-mono font-bold text-[#172033]">
                            ₹{{ number_format($supplier->opening_balance, 2) }}
                        </td>

                        {{-- Status --}}
                        <td class="py-4 px-4">
                            @if($supplier->status)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">Active</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-[#64748B] border border-slate-200">Inactive</span>
                            @endif
                        </td>

                        {{-- Action Buttons --}}
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                {{-- Edit Modal Trigger --}}
                                <button type="button" @click="$dispatch('open-modal', 'edit-supplier-{{ $supplier->id }}')" title="Edit Supplier"
                                        class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>

                                {{-- Delete Form --}}
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Supplier"
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
                                🚚
                            </div>
                            <h3 class="text-sm font-bold text-[#172033]">
                                @if(request('search'))
                                    No suppliers found matching "{{ request('search') }}"
                                @else
                                    No suppliers registered yet
                                @endif
                            </h3>
                            <p class="text-xs text-[#64748B] mt-1 max-w-sm mx-auto">
                                Register suppliers to streamline ingredients and retail procurement.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. Pagination Bar --}}
        @if($suppliers->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

</div>

{{-- ════════════════════════════════════════════════════════════
     EDIT SUPPLIER MODALS
════════════════════════════════════════════════════════════ --}}
@foreach($suppliers as $supplier)
<x-modal name="edit-supplier-{{ $supplier->id }}" focusable>
    <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="p-6 space-y-5">
        @csrf 
        @method('PUT')
        
        <div class="border-b border-[#E5E7EB] pb-3">
            <h2 class="text-base font-semibold text-[#172033]">Edit Supplier</h2>
            <p class="text-xs text-[#64748B] mt-0.5">Update contact and billing settings for {{ $supplier->name }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Supplier Name <span class="text-[#FF4848]">*</span></label>
                <input type="text" name="name" value="{{ $supplier->name }}" required 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Contact Person</label>
                <input type="text" name="contact_person" value="{{ $supplier->contact_person }}" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Phone Number</label>
                <input type="text" name="phone" value="{{ $supplier->phone }}" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Email Address</label>
                <input type="email" name="email" value="{{ $supplier->email }}" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-[#172033]">Address</label>
                <textarea name="address" rows="2" 
                          class="mt-1 w-full p-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">{{ $supplier->address }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Tax / GST Number</label>
                <input type="text" name="tax_number" value="{{ $supplier->tax_number }}" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Opening Balance (₹)</label>
                <input type="number" name="opening_balance" value="{{ $supplier->opening_balance }}" step="0.01" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-[#172033]">Status</label>
                <select name="status" 
                        class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    <option value="1" {{ $supplier->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$supplier->status ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="pt-4 border-t border-[#E5E7EB] flex justify-end gap-2.5">
            <button type="button" x-on:click="$dispatch('close')" 
                    class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] transition-colors">
                Cancel
            </button>
            <button type="submit" 
                    class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">
                Save Changes
            </button>
        </div>
    </form>
</x-modal>
@endforeach

{{-- ════════════════════════════════════════════════════════════
     ADD NEW SUPPLIER MODAL
════════════════════════════════════════════════════════════ --}}
<x-modal name="add-supplier" focusable>
    <form action="{{ route('suppliers.store') }}" method="POST" class="p-6 space-y-5">
        @csrf
        
        <div class="border-b border-[#E5E7EB] pb-3">
            <h2 class="text-base font-semibold text-[#172033]">Add New Supplier</h2>
            <p class="text-xs text-[#64748B] mt-0.5">Register a new vendor or procurement partner</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Supplier Name <span class="text-[#FF4848]">*</span></label>
                <input type="text" name="name" required 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. Fresh Produce Co.">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Contact Person</label>
                <input type="text" name="contact_person" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. John Doe">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Phone Number</label>
                <input type="text" name="phone" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="+91 98765 43210">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Email Address</label>
                <input type="email" name="email" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="supplier@example.com">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-[#172033]">Address</label>
                <textarea name="address" rows="2" 
                          class="mt-1 w-full p-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="Street, City, Postal Code"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Tax / GST Number</label>
                <input type="text" name="tax_number" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. 29AAAAA0000A1Z5">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Opening Balance (₹)</label>
                <input type="number" name="opening_balance" value="0" step="0.01" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
        </div>

        <div class="pt-4 border-t border-[#E5E7EB] flex justify-end gap-2.5">
            <button type="button" x-on:click="$dispatch('close')" 
                    class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] transition-colors">
                Cancel
            </button>
            <button type="submit" 
                    class="btn-brand h-10 px-5 rounded-lg text-white text-xs font-medium transition-colors shadow-sm">
                Add Supplier
            </button>
        </div>
    </form>
</x-modal>
@endsection
