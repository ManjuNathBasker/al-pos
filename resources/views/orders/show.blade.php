@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('orders.index') }}" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Order {{ $order->order_number }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $order->created_at->format('F j, Y, g:i a') }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <!-- Print / Download Bill Button -->
        <button onclick="window.print()" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 hide-on-print">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Bill
        </button>

        <!-- Send via WhatsApp Button -->
        @php
            $waMessage = "Receipt for Order {$order->order_number}%0A";
            $waMessage .= "Date: " . $order->created_at->format('Y-m-d H:i') . "%0A";
            $waMessage .= "-------------------%0A";
            foreach($order->items as $item) {
                $waMessage .= "{$item->quantity}x {$item->product_name} - $" . number_format($item->subtotal, 2) . "%0A";
            }
            $waMessage .= "-------------------%0A";
            $waMessage .= "Total: $" . number_format($order->total_amount, 2);
        @endphp
        <a href="https://wa.me/?text={{ $waMessage }}" target="_blank" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 hide-on-print">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
            </svg>
            WhatsApp
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Col: Items & Receipt -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- Printable Receipt Area -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden print-area">
            <div class="p-6 sm:p-8">
                <!-- Receipt Header -->
                <div class="flex justify-between items-start mb-8 pb-8 border-b border-slate-200 border-dashed">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">{{ config('app.name', 'POS Store') }}</h1>
                        <p class="text-slate-500 mt-1">Receipt for Order #{{ $order->order_number }}</p>
                    </div>
                    <div class="text-right">
                        @if($order->status == 'paid')
                        <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700 border border-green-200">Paid</span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-700 border border-slate-200">{{ ucfirst($order->status) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Items Table -->
                <table class="w-full text-left mb-8">
                    <thead>
                        <tr class="text-slate-500 text-sm border-b border-slate-200">
                            <th class="pb-3 font-medium">Item</th>
                            <th class="pb-3 font-medium text-center">Qty</th>
                            <th class="pb-3 font-medium text-right">Price</th>
                            <th class="pb-3 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 divide-y divide-slate-100">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="py-4">
                                <div class="font-medium text-slate-900">{{ $item->product_name }}</div>
                            </td>
                            <td class="py-4 text-center">{{ $item->quantity }}</td>
                            <td class="py-4 text-right">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-4 text-right font-medium">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Financial Summary -->
                <div class="w-full sm:w-1/2 ml-auto space-y-3 text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-medium text-slate-900">${{ number_format($order->items->sum('subtotal'), 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Discount</span>
                        <span>-${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pb-3 border-b border-slate-200">
                        <span>Tax</span>
                        <span class="font-medium text-slate-900">${{ number_format($order->tax_amount ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-slate-900 pt-1">
                        <span>Total</span>
                        <span>${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-12 text-center text-sm text-slate-500 pt-8 border-t border-slate-200 border-dashed">
                    <p>Thank you for your purchase!</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Col: Meta Info -->
    <div class="space-y-6 hide-on-print">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Order Information</h3>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <span class="block text-slate-500">Order Date</span>
                    <span class="font-medium text-slate-900">{{ $order->created_at->format('M j, Y H:i:s') }}</span>
                </div>
                <div>
                    <span class="block text-slate-500">Cashier / Staff</span>
                    <span class="font-medium text-slate-900">{{ $order->user->name ?? 'System' }}</span>
                </div>
                <div>
                    <span class="block text-slate-500">Status</span>
                    <span class="font-medium text-slate-900">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
        </div>

        @if($order->customer)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Customer Information</h3>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <span class="block text-slate-500">Customer Name</span>
                    <span class="font-medium text-slate-900">{{ $order->customer->name }}</span>
                </div>
                <div>
                    <span class="block text-slate-500">Phone</span>
                    <span class="font-medium text-slate-900">{{ $order->customer->phone }}</span>
                </div>
                <div>
                    <span class="block text-slate-500">Wallet Balance</span>
                    <span class="font-medium text-slate-900">${{ number_format($order->customer->wallet_balance, 2) }}</span>
                </div>
            </div>
        </div>
        @endif

        @if($order->note)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Order Notes</h3>
            </div>
            <div class="p-6 text-sm text-slate-700 whitespace-pre-wrap">{{ $order->note }}</div>
        </div>
        @endif
    </div>
</div>

<style>
    @media print {
        body { visibility: hidden; background-color: white; }
        .print-area { visibility: visible; position: absolute; left: 0; top: 0; width: 100%; border: none; box-shadow: none; }
        .hide-on-print { display: none !important; }
        aside, header { display: none !important; }
    }
</style>
@endsection
