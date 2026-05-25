@extends('reports.pdf.layout')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="report-info">
    <h2>Profit & Loss Statement</h2>
    <table>
        <tr>
            <td><strong>Period:</strong> {{ $startDate }} to {{ $endDate }}</td>
            <td class="text-right"><strong>Net Profit:</strong> ${{ number_format($report['net_profit'], 2) }}</td>
        </tr>
    </table>
</div>

<h3>Operating Income</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Account</th>
            <th class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['income'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td class="text-right">${{ number_format($item['amount'], 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" class="text-center">No income recorded.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th>Total Income</th>
            <th class="text-right">${{ number_format($report['total_income'], 2) }}</th>
        </tr>
    </tfoot>
</table>

<h3>Operating Expenses</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Account</th>
            <th class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['expenses'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td class="text-right">${{ number_format($item['amount'], 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" class="text-center">No expenses recorded.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th>Total Expenses</th>
            <th class="text-right">${{ number_format($report['total_expense'], 2) }}</th>
        </tr>
    </tfoot>
</table>

<table class="data-table">
    <tr>
        <th style="font-size: 16px;">NET PROFIT</th>
        <th class="text-right" style="font-size: 16px;">${{ number_format($report['net_profit'], 2) }}</th>
    </tr>
</table>
@endsection
