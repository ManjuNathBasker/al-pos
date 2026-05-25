@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Profit & Loss Statement</h2>
        <p class="mt-1 text-sm text-slate-500">View your income, expenses, and net profit.</p>
    </div>
    
    <form action="{{ route('reports.profit-loss') }}" method="GET" class="flex gap-2">
        <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-slate-200 text-sm">
        <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-slate-200 text-sm">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Filter</button>
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
