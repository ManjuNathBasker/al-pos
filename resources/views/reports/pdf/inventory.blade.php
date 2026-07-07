@extends('reports.pdf.layout')

@section('title', 'Inventory Report')

@section('content')
<div class="report-info">
    <h2>Inventory Report</h2>
    <table>
        <tr>
            <td><strong>Date:</strong> {{ now()->format('Y-m-d') }}</td>
            <td class="text-right"><strong>Total Items:</strong> {{ collect($inventory)->count() }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="text-right"><strong>Total Value:</strong> ${{ number_format(collect($inventory)->sum(fn($i) => $i->current_stock * $i->cost_price), 2) }}</td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Item Code</th>
            <th class="text-right">Quantity</th>
            <th class="text-right">Unit Cost</th>
            <th class="text-right">Total Value</th>
        </tr>
    </thead>
    <tbody>
        @forelse($inventory as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td>{{ $item->code }}</td>
            <td class="text-right">{{ $item->current_stock }} {{ $item->unit_type }}</td>
            <td class="text-right">${{ number_format($item->cost_price, 2) }}</td>
            <td class="text-right">${{ number_format($item->current_stock * $item->cost_price, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">No inventory records found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
