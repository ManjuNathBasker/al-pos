@extends('reports.pdf.layout')

@section('title', 'Balance Sheet')

@section('content')
<div class="report-info">
    <h2>Balance Sheet</h2>
    <table>
        <tr>
            <td><strong>As of:</strong> {{ $endDate }}</td>
        </tr>
    </table>
</div>

<h3>Assets</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Account</th>
            <th class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['assets'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td class="text-right">@currency($item['amount'])</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" class="text-center">No assets recorded.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th>Total Assets</th>
            <th class="text-right">@currency($report['total_assets'])</th>
        </tr>
    </tfoot>
</table>

<h3>Liabilities</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Account</th>
            <th class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['liabilities'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td class="text-right">@currency($item['amount'])</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" class="text-center">No liabilities recorded.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th>Total Liabilities</th>
            <th class="text-right">@currency($report['total_liabilities'])</th>
        </tr>
    </tfoot>
</table>

<h3>Equity</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Account</th>
            <th class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['equity'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td class="text-right">@currency($item['amount'])</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" class="text-center">No equity recorded.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th>Total Equity</th>
            <th class="text-right">@currency($report['total_equity'])</th>
        </tr>
    </tfoot>
</table>

<table class="data-table">
    <tr>
        <th style="font-size: 16px;">TOTAL LIABILITIES & EQUITY</th>
        <th class="text-right" style="font-size: 16px;">@currency($report['total_liabilities'] + $report['total_equity'])</th>
    </tr>
</table>
@endsection
