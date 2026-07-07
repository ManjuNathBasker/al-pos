@extends('reports.pdf.layout')

@section('title', 'Purchases Report')

@section('content')
<div class="report-info">
    <h2>Purchases Report</h2>
    <table>
        <tr>
            <td><strong>Period:</strong> {{ $startDate }} to {{ $endDate }}</td>
            <td class="text-right"><strong>Total Purchases:</strong> {{ collect($purchases)->count() }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="text-right"><strong>Total Amount:</strong> ${{ number_format(collect($purchases)->sum('total_amount'), 2) }}</td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Ref No</th>
            <th>Date</th>
            <th>Supplier</th>
            <th>Status</th>
            <th>Payment</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($purchases as $purchase)
        <tr>
            <td>{{ $purchase->reference_no }}</td>
            <td>{{ $purchase->purchase_date }}</td>
            <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
            <td>{{ ucfirst($purchase->status) }}</td>
            <td>{{ ucfirst($purchase->payment_status) }}</td>
            <td class="text-right">${{ number_format($purchase->total_amount, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">No purchase records found for this period.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
