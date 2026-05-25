@extends('reports.pdf.layout')

@section('title', 'Sales Report')

@section('content')
<div class="report-info">
    <h2>Sales Report</h2>
    <table>
        <tr>
            <td><strong>Period:</strong> {{ $startDate }} to {{ $endDate }}</td>
            <td class="text-right"><strong>Total Orders:</strong> {{ collect($orders)->count() }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="text-right"><strong>Total Sales:</strong> ${{ number_format(collect($orders)->sum('total_amount'), 2) }}</td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Type</th>
            <th>Status</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
        <tr>
            <td>{{ $order->order_number }}</td>
            <td>{{ $order->created_at->format('Y-m-d') }}</td>
            <td>{{ $order->customer->name ?? 'Walk-in' }}</td>
            <td>{{ ucfirst($order->service_type) }}</td>
            <td>{{ ucfirst($order->status) }}</td>
            <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">No sales records found for this period.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
