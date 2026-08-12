@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Coupons</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Create and manage discount coupons for your customers.</p>
        </div>
        <a href="{{ route('coupons.create') }}" 
           class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Coupon</span>
        </a>
    </div>

    {{-- Coupons Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Code</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Type</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Value</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Expiry</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Usage</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @foreach($coupons as $coupon)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4">
                            <span class="text-sm font-bold font-mono text-[#F5703E] tracking-wide">{{ $coupon->code }}</span>
                        </td>
                        <td class="py-4 px-4 text-xs font-medium text-[#64748B]">{{ ucfirst($coupon->type) }}</td>
                        <td class="py-4 px-4 text-sm font-bold font-mono text-[#172033]">
                            {{ $coupon->type === 'percent' ? $coupon->value . '%' : '₹' . number_format($coupon->value, 2) }}
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">
                            {{ $coupon->expiry_date ? $coupon->expiry_date->format('M d, Y') : 'No Expiry' }}
                        </td>
                        <td class="py-4 px-4 text-xs font-medium text-[#172033]">
                            {{ $coupon->used_count }} / <span class="text-[#94A3B8]">{{ $coupon->usage_limit ?? '∞' }}</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                                {{ $coupon->is_active ? 'bg-emerald-50 text-[#29AB6C] border-emerald-200' : 'bg-slate-100 text-[#64748B] border-slate-200' }}">
                                {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('coupons.edit', $coupon) }}" title="Edit Coupon"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('coupons.destroy', $coupon) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this coupon?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Delete Coupon"
                                            class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">{{ $coupons->links() }}</div>
        @endif
    </div>
</div>
@endsection
