@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Profit & Loss Statement</h1>
            <p class="text-sm text-[#64748B] mt-0.5">View your income, expenses, and net profit for a period.</p>
        </div>
        <form action="{{ route('reports.profit-loss') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="date" name="start_date" value="{{ $startDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <span class="text-[#94A3B8] text-xs">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" 
                   class="h-10 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
            <button type="submit" class="h-10 px-4 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Apply</button>
            <button type="submit" name="format" value="pdf" formaction="{{ route('reports.profit-loss.export') }}" 
                    class="h-10 px-3.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold border border-red-200 transition-colors">PDF</button>
            <button type="submit" name="format" value="excel" formaction="{{ route('reports.profit-loss.export') }}" 
                    class="h-10 px-3.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-xs font-bold border border-emerald-200 transition-colors">Excel</button>
        </form>
    </div>

    {{-- KPI Summary Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Income</p>
            <p class="text-2xl font-bold font-mono text-[#29AB6C] mt-1.5">@currency($report['total_income'])</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Expenses</p>
            <p class="text-2xl font-bold font-mono text-[#FF4848] mt-1.5">@currency($report['total_expense'])</p>
        </div>
        <div class="bg-[#172033] rounded-xl shadow-sm p-5">
            <p class="text-xs font-semibold text-white/60 uppercase tracking-wider">Net Profit</p>
            <p class="text-2xl font-bold font-mono text-white mt-1.5">@currency($report['net_profit'])</p>
        </div>
    </div>

    {{-- Income & Expense Details --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Operating Income --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-emerald-50/50">
                <h3 class="text-sm font-bold text-[#29AB6C]">Operating Income</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($report['income'] as $item)
                    <tr class="hover:bg-[#FFF8F5]">
                        <td class="px-5 py-3.5 text-xs font-medium text-[#172033]">{{ $item['name'] }}</td>
                        <td class="px-5 py-3.5 text-right text-xs font-mono font-semibold text-[#29AB6C]">@currency($item['amount'])</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-4 text-center text-xs text-[#94A3B8]">No income recorded.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-50/50 border-t border-[#E5E7EB]">
                        <td class="px-5 py-4 text-xs font-bold text-[#29AB6C]">Total Income</td>
                        <td class="px-5 py-4 text-right text-sm font-bold font-mono text-[#29AB6C]">@currency($report['total_income'])</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Operating Expenses --}}
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-red-50/50">
                <h3 class="text-sm font-bold text-[#FF4848]">Operating Expenses</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($report['expenses'] as $item)
                    <tr class="hover:bg-[#FFF8F5]">
                        <td class="px-5 py-3.5 text-xs font-medium text-[#172033]">{{ $item['name'] }}</td>
                        <td class="px-5 py-3.5 text-right text-xs font-mono font-semibold text-[#FF4848]">@currency($item['amount'])</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-4 text-center text-xs text-[#94A3B8]">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-red-50/50 border-t border-[#E5E7EB]">
                        <td class="px-5 py-4 text-xs font-bold text-[#FF4848]">Total Expenses</td>
                        <td class="px-5 py-4 text-right text-sm font-bold font-mono text-[#FF4848]">@currency($report['total_expense'])</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
