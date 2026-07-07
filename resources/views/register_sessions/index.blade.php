@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Register Sessions</h1>
            <p class="text-sm text-slate-500">View shift reports and cash drawer discrepancies.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="py-4 px-6 font-semibold text-sm text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="py-4 px-6 font-semibold text-sm text-slate-500 uppercase tracking-wider">Cashier</th>
                        <th class="py-4 px-6 font-semibold text-sm text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="py-4 px-6 font-semibold text-sm text-slate-500 uppercase tracking-wider text-right">Opening</th>
                        <th class="py-4 px-6 font-semibold text-sm text-slate-500 uppercase tracking-wider text-right">Expected</th>
                        <th class="py-4 px-6 font-semibold text-sm text-slate-500 uppercase tracking-wider text-right">Actual</th>
                        <th class="py-4 px-6 font-semibold text-sm text-slate-500 uppercase tracking-wider text-right">Diff</th>
                        <th class="py-4 px-6 font-semibold text-sm text-slate-500 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 px-6">
                            <div class="text-sm font-semibold text-slate-800">{{ $session->opened_at->format('M d, Y') }}</div>
                            <div class="text-xs text-slate-500">{{ $session->opened_at->format('h:i A') }} - {{ $session->closed_at ? $session->closed_at->format('h:i A') : 'Ongoing' }}</div>
                        </td>
                        <td class="py-4 px-6 text-sm font-medium text-slate-700">
                            {{ $session->user->name ?? 'Unknown' }}
                        </td>
                        <td class="py-4 px-6">
                            @if($session->status === 'open')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Open
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    Closed
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-sm font-medium text-slate-700 text-right">
                            ${{ number_format($session->opening_amount, 2) }}
                        </td>
                        <td class="py-4 px-6 text-sm font-medium text-slate-700 text-right">
                            {{ $session->closing_amount_expected !== null ? '$' . number_format($session->closing_amount_expected, 2) : '-' }}
                        </td>
                        <td class="py-4 px-6 text-sm font-medium text-slate-700 text-right">
                            {{ $session->closing_amount_actual !== null ? '$' . number_format($session->closing_amount_actual, 2) : '-' }}
                        </td>
                        <td class="py-4 px-6 text-sm font-bold text-right">
                            @if($session->difference === null)
                                -
                            @elseif($session->difference == 0)
                                <span class="text-slate-500">$0.00</span>
                            @elseif($session->difference < 0)
                                <span class="text-red-500">-${{ number_format(abs($session->difference), 2) }}</span>
                            @else
                                <span class="text-emerald-500">+${{ number_format($session->difference, 2) }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-sm text-slate-600 max-w-xs truncate" title="{{ $session->notes }}">
                            {{ $session->notes ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">No register sessions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($sessions->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
