@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Delivery Partners</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Manage delivery partners, commission rates, and settlement accounts.</p>
        </div>
        <a href="{{ route('delivery-partners.create') }}" 
           class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Partner</span>
        </a>
    </div>

    {{-- Partners Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Name</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Commission</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Receivables Account</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($partners as $partner)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-[#172033]">{{ $partner->name }}</td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ number_format($partner->commission_percentage, 2) }}%
                            </span>
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">
                            @if($partner->receivableAccount)
                                {{ $partner->receivableAccount->account_name }}
                            @else
                                <span class="italic text-[#94A3B8]">None</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            @if($partner->status)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#29AB6C]"></span>Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-[#64748B] border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#94A3B8]"></span>Inactive
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('delivery-partners.settlements', $partner) }}" title="Settlements"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-emerald-50 text-[#64748B] hover:text-[#29AB6C] hover:border-emerald-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                </a>
                                <a href="{{ route('delivery-partners.edit', $partner) }}" title="Edit Partner"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('delivery-partners.destroy', $partner) }}" method="POST" onsubmit="return confirm('Delete this partner?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Delete Partner"
                                            class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">🚚</div>
                            <h3 class="text-sm font-bold text-[#172033]">No delivery partners yet</h3>
                            <p class="text-xs text-[#64748B] mt-1">
                                <a href="{{ route('delivery-partners.create') }}" class="text-[#F5703E] hover:underline font-semibold">Add a partner</a> to manage delivery commissions.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
