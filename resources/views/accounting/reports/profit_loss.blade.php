@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Profit & Loss Statement</h2>
        <p class="mt-1 text-sm text-slate-500">View your income, expenses, and net profit.</p>
    </div>
    
    <form action="{{ route('reports.profit-loss') }}" method="GET" class="flex flex-wrap items-center gap-2">
        <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-slate-200 text-sm px-3 py-2">
        <span class="text-slate-400">to</span>
        <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-slate-200 text-sm px-3 py-2">
        <button type="submit" class="p-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 ml-2" title="Filter">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
        
        <div class="flex items-center gap-2 ml-auto">
            <button type="submit" name="format" value="pdf" formaction="{{ route('reports.profit-loss.export') }}" class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-bold text-sm transition-colors border border-red-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                PDF
            </button>
            <button type="submit" name="format" value="excel" formaction="{{ route('reports.profit-loss.export') }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 font-bold text-sm transition-colors border border-emerald-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Excel
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-center">
        <div class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Total Income</div>
        <div class="mt-2 text-3xl font-bold text-slate-800">${{ number_format($report['total_income'], 2) }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-center">
        <div class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Total Expenses</div>
        <div class="mt-2 text-3xl font-bold text-slate-800">${{ number_format($report['total_expense'], 2) }}</div>
    </div>
    <div class="bg-indigo-600 rounded-xl shadow-sm border border-indigo-700 p-6 flex flex-col justify-center">
        <div class="text-sm font-semibold text-indigo-200 uppercase tracking-wide">Net Profit</div>
        <div class="mt-2 text-3xl font-bold text-white">${{ number_format($report['net_profit'], 2) }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Income -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Operating Income</h3>
        </div>
        <div class="p-0">
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['income'] as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $item['name'] }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900 text-right">${{ number_format($item['amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-slate-500 text-sm">No income recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/50 font-bold">
                        <td class="px-6 py-4 text-slate-800">Total Income</td>
                        <td class="px-6 py-4 text-right text-indigo-600">${{ number_format($report['total_income'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Expenses -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Operating Expenses</h3>
        </div>
        <div class="p-0">
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['expenses'] as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $item['name'] }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900 text-right">${{ number_format($item['amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-slate-500 text-sm">No expenses recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/50 font-bold">
                        <td class="px-6 py-4 text-slate-800">Total Expenses</td>
                        <td class="px-6 py-4 text-right text-red-600">${{ number_format($report['total_expense'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
