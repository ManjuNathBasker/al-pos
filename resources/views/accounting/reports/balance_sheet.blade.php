@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Balance Sheet</h1>
            <p class="text-sm text-[#64748B] mt-0.5">View your financial position — assets, liabilities, and equity.</p>
        </div>
        <form action="{{ route('reports.balance-sheet') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="date" name="end_date" value="{{ $endDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <button type="submit" class="h-10 px-4 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Apply</button>
            <button type="submit" name="format" value="pdf" formaction="{{ route('reports.balance-sheet.export') }}" 
                    class="h-10 px-3.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold border border-red-200 transition-colors">PDF</button>
            <button type="submit" name="format" value="excel" formaction="{{ route('reports.balance-sheet.export') }}" 
                    class="h-10 px-3.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-xs font-bold border border-emerald-200 transition-colors">Excel</button>
        </form>
    </div>

    {{-- Two-column Balance Sheet --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Assets --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-blue-50/50">
                <h3 class="text-sm font-bold text-blue-700">Assets</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($report['assets'] as $item)
                    <tr class="hover:bg-[#FFF8F5]">
                        <td class="px-5 py-3.5 text-xs font-medium text-[#172033]">{{ $item['name'] }}</td>
                        <td class="px-5 py-3.5 text-right text-xs font-mono font-semibold text-[#172033]">@currency($item['amount'])</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-4 text-center text-xs text-[#94A3B8]">No assets recorded.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-blue-50/50 border-t border-[#E5E7EB]">
                        <td class="px-5 py-4 text-xs font-bold text-blue-700">Total Assets</td>
                        <td class="px-5 py-4 text-right text-sm font-bold font-mono text-blue-700">@currency($report['total_assets'])</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Liabilities + Equity --}}
        <div class="space-y-5">

            {{-- Liabilities --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-red-50/50">
                    <h3 class="text-sm font-bold text-[#FF4848]">Liabilities</h3>
                </div>
                <table class="w-full text-left border-collapse">
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($report['liabilities'] as $item)
                        <tr class="hover:bg-[#FFF8F5]">
                            <td class="px-5 py-3.5 text-xs font-medium text-[#172033]">{{ $item['name'] }}</td>
                            <td class="px-5 py-3.5 text-right text-xs font-mono font-semibold text-[#172033]">@currency($item['amount'])</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-5 py-4 text-center text-xs text-[#94A3B8]">No liabilities recorded.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-red-50/50 border-t border-[#E5E7EB]">
                            <td class="px-5 py-4 text-xs font-bold text-[#FF4848]">Total Liabilities</td>
                            <td class="px-5 py-4 text-right text-sm font-bold font-mono text-[#FF4848]">@currency($report['total_liabilities'])</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Equity --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-orange-50/50">
                    <h3 class="text-sm font-bold text-[#F5703E]">Equity</h3>
                </div>
                <table class="w-full text-left border-collapse">
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($report['equity'] as $item)
                        <tr class="hover:bg-[#FFF8F5]">
                            <td class="px-5 py-3.5 text-xs font-medium text-[#172033]">{{ $item['name'] }}</td>
                            <td class="px-5 py-3.5 text-right text-xs font-mono font-semibold text-[#172033]">@currency($item['amount'])</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-5 py-4 text-center text-xs text-[#94A3B8]">No equity recorded.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-orange-50/50 border-t border-[#E5E7EB]">
                            <td class="px-5 py-4 text-xs font-bold text-[#F5703E]">Total Equity</td>
                            <td class="px-5 py-4 text-right text-sm font-bold font-mono text-[#F5703E]">@currency($report['total_equity'])</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Summary Banner --}}
            <div class="bg-[#172033] rounded-xl p-5 flex justify-between items-center text-white">
                <span class="text-sm font-semibold">Total Liabilities & Equity</span>
                <span class="text-lg font-bold font-mono">@currency($report['total_liabilities'] + $report['total_equity'])</span>
            </div>
        </div>
    </div>
</div>
@endsection
