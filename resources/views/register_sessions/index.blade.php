@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Register Sessions</h1>
            <p class="text-sm text-[#64748B] mt-0.5">View shift reports, cash drawer logs, and discrepancy records.</p>
        </div>
    </div>

    {{-- Sessions Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date / Time</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Cashier</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Opening</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Expected</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Actual</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Difference</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4">
                            <div class="text-xs font-semibold text-[#172033]">{{ $session->opened_at->format('M d, Y') }}</div>
                            <div class="text-[11px] text-[#94A3B8] mt-0.5">
                                {{ $session->opened_at->format('h:i A') }} — {{ $session->closed_at ? $session->closed_at->format('h:i A') : 'Ongoing' }}
                            </div>
                        </td>
                        <td class="py-4 px-4 text-xs font-medium text-[#172033]">{{ $session->user->name ?? 'Unknown' }}</td>
                        <td class="py-4 px-4">
                            @if($session->status === 'open')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">Open</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-[#64748B] border border-slate-200">Closed</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs font-mono font-medium text-[#172033] text-right">₹{{ number_format($session->opening_amount, 2) }}</td>
                        <td class="py-4 px-4 text-xs font-mono text-[#64748B] text-right">
                            {{ $session->closing_amount_expected !== null ? '₹' . number_format($session->closing_amount_expected, 2) : '—' }}
                        </td>
                        <td class="py-4 px-4 text-xs font-mono text-[#64748B] text-right">
                            {{ $session->closing_amount_actual !== null ? '₹' . number_format($session->closing_amount_actual, 2) : '—' }}
                        </td>
                        <td class="py-4 px-4 text-xs font-bold font-mono text-right">
                            @if($session->difference === null)
                                <span class="text-[#94A3B8]">—</span>
                            @elseif($session->difference == 0)
                                <span class="text-[#64748B]">₹0.00</span>
                            @elseif($session->difference < 0)
                                <span class="text-[#FF4848]">-₹{{ number_format(abs($session->difference), 2) }}</span>
                            @else
                                <span class="text-[#29AB6C]">+₹{{ number_format($session->difference, 2) }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B] max-w-xs truncate" title="{{ $session->notes }}">{{ $session->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-lg mx-auto mb-2 border border-orange-100">🗂</div>
                            <p class="text-sm font-medium text-[#172033]">No register sessions found</p>
                            <p class="text-xs text-[#64748B] mt-0.5">Sessions will appear here after closing a POS shift.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">{{ $sessions->links() }}</div>
        @endif
    </div>
</div>
@endsection
