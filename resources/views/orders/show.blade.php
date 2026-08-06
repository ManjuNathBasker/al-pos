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

        @if($order->status !== 'cancelled')
        <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order and restore stock?')">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 hide-on-print">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel Order
            </button>
        </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Col: Items & Receipt -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- On-Screen View (Hidden during print) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden print:hidden">
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

    <!-- Thermal Print Receipt -->
    <div class="hidden print:block" style="width: 100%; max-width: 320px; font-family: 'Courier New', Courier, monospace; color: #000; font-size: 12px; line-height: 1.4; margin: 0;">
        <style type="text/css" media="print">
            @page { margin: 0; size: 80mm auto; }
            body { margin: 0; }
        </style>
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 12px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase;">{{ config('app.name', 'POS Store') }}</h2>
            <p style="margin: 2px 0;">123 Supermarket St, Retail City</p>
            <p style="margin: 2px 0;">Tel: +1 234 567 890</p>
            <div style="margin: 8px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; font-weight: bold;">
                RECEIPT: #{{ $order->order_number }}
            </div>
        </div>

        <!-- Info Section -->
        <div style="margin-bottom: 12px;">
            <p style="margin: 0;">Date: {{ $order->created_at->format('n/j/Y, g:i:s A') }}</p>
            <p style="margin: 0;">Cashier: {{ $order->user->name ?? 'Admin' }}</p>
            @if($order->customer_id)
                <p style="margin: 0;">Customer: {{ $order->customer->name ?? 'Walk-in' }}</p>
            @endif
        </div>

        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
            <thead>
                <tr style="border-bottom: 1px dashed #000;">
                    <th style="text-align: left; padding: 4px 0; font-weight: bold;">ITEM</th>
                    <th style="text-align: center; font-weight: bold;">QTY</th>
                    <th style="text-align: right; font-weight: bold;">PRICE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;">
                        <span>{{ Str::limit($item->product_name, 20) }}</span><br>
                        <small style="font-size: 10px; color: #555;">{{ $item->product->sku ?? '' }}</small>
                    </td>
                    <td style="text-align: center; vertical-align: top; padding: 4px 0;">{{ $item->quantity }}</td>
                    <td style="text-align: right; vertical-align: top; padding: 4px 0;">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div style="border-top: 1px dashed #000; padding-top: 8px;">
            <div style="display: flex; justify-content: space-between;">
                <span>SUBTOTAL:</span>
                <span>{{ number_format($order->items->sum('subtotal'), 2) }}</span>
            </div>
            @if($order->discount_amount > 0)
            <div style="display: flex; justify-content: space-between;">
                <span>DISCOUNT:</span>
                <span>-{{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif
            <div style="display: flex; justify-content: space-between;">
                <span>TAX:</span>
                <span>{{ number_format($order->tax_amount ?? 0, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-top: 8px; border-top: 1px dashed #000; padding-top: 8px;">
                <span>TOTAL:</span>
                <span>${{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 24px; font-size: 12px; border-top: 1px dashed #000; padding-top: 12px;">
            <p style="margin: 0;">THANK YOU FOR SHOPPING WITH US!</p>
            <p style="margin: 0; margin-top: 4px;">Please keep this receipt for returns.</p>
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
                    <span class="block text-slate-500">Service Type</span>
                    <span class="font-medium text-slate-900">{{ ucfirst(str_replace('_', ' ', $order->service_type ?? 'retail')) }}</span>
                </div>
                <div>
                    <span class="block text-slate-500">Status</span>
                    @if($order->status == 'paid')
                        <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Paid</span>
                    @elseif($order->status == 'cancelled')
                        <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">Cancelled</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2.5 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-500/10">{{ ucfirst($order->status) }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Payment Details</h3>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @if($order->payments && $order->payments->count() > 0)
                    @foreach($order->payments as $payment)
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                        <div class="flex items-center gap-2.5">
                            @if($payment->payment_method === 'wallet')
                                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <span class="font-medium text-slate-700">Wallet</span>
                            @elseif(is_numeric($payment->payment_method))
                                @php $accName = $accountNames[$payment->payment_method] ?? 'Account #' . $payment->payment_method; @endphp
                                <div class="w-8 h-8 rounded-lg {{ str_contains(strtolower($accName), 'cash') ? 'bg-emerald-100' : 'bg-blue-100' }} flex items-center justify-center">
                                    @if(str_contains(strtolower($accName), 'cash'))
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    @endif
                                </div>
                                <span class="font-medium text-slate-700">{{ $accName }}</span>
                            @else
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <span class="font-medium text-slate-700">{{ ucfirst($payment->payment_method) }}</span>
                            @endif
                        </div>
                        <span class="font-bold text-slate-900">${{ number_format($payment->amount, 2) }}</span>
                    </div>
                    @endforeach

                    {{-- Total Paid --}}
                    <div class="flex justify-between pt-3 mt-2 border-t border-slate-200">
                        <span class="font-semibold text-slate-700">Total Paid</span>
                        <span class="font-bold text-slate-900">${{ number_format($order->payments->sum('amount'), 2) }}</span>
                    </div>

                    @if($order->change_returned > 0)
                    <div class="flex justify-between text-amber-600">
                        <span class="font-medium">Change Returned</span>
                        <span class="font-bold">${{ number_format($order->change_returned, 2) }}</span>
                    </div>
                    @endif
                @else
                    {{-- Fallback for legacy orders without payment records --}}
                    <div class="flex justify-between">
                        <span class="text-slate-500">Payment Method</span>
                        <span class="font-medium text-slate-900">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                    </div>
                    @if($order->wallet_used > 0)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Wallet Used</span>
                        <span class="font-medium text-purple-700">${{ number_format($order->wallet_used, 2) }}</span>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Card Transaction Details (if any) --}}
        @if($order->cardTransactions && $order->cardTransactions->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Card Transactions</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($order->cardTransactions as $cardTx)
                <div class="text-sm space-y-2 {{ !$loop->last ? 'pb-4 border-b border-slate-100' : '' }}">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Card</span>
                        <span class="font-medium text-slate-900">{{ $cardTx->card->bank_name ?? $cardTx->bank_name }} — {{ $cardTx->card->card_network ?? '' }} ({{ $cardTx->card->card_type ?? '' }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Gross Amount</span>
                        <span class="font-medium text-slate-900">${{ number_format($cardTx->gross_amount, 2) }}</span>
                    </div>
                    @if($cardTx->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Discount</span>
                        <span class="font-medium">-${{ number_format($cardTx->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($cardTx->service_charge_amount > 0)
                    <div class="flex justify-between text-amber-600">
                        <span>Service Charge</span>
                        <span class="font-medium">+${{ number_format($cardTx->service_charge_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-semibold">
                        <span class="text-slate-700">Net Settlement</span>
                        <span class="text-slate-900">${{ number_format($cardTx->net_settlement_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status</span>
                        @if($cardTx->settlement_status === 'completed')
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Settled</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">{{ ucfirst($cardTx->settlement_status ?? 'Pending') }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Wallet Transactions (if any) --}}
        @if($order->walletTransactions && $order->walletTransactions->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mt-6 hide-on-print">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Wallet Transactions</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($order->walletTransactions as $walletTx)
                <div class="text-sm space-y-2 {{ !$loop->last ? 'pb-4 border-b border-slate-100' : '' }}">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Type</span>
                        <span class="font-medium {{ $walletTx->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ ucfirst($walletTx->type) }}
                        </span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span class="text-slate-700">Amount</span>
                        <span class="{{ $walletTx->type === 'credit' ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ $walletTx->type === 'credit' ? '+' : '-' }}${{ number_format($walletTx->amount, 2) }}
                        </span>
                    </div>
                    @if($walletTx->description)
                    <div class="flex flex-col mt-1">
                        <span class="text-slate-500 text-xs">Description</span>
                        <span class="text-slate-700">{{ $walletTx->description }}</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($order->customer)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Customer Information</h3>
                <a href="{{ route('customers.show', $order->customer) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                    View Profile →
                </a>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <span class="block text-slate-500">Customer Name</span>
                    <a href="{{ route('customers.show', $order->customer) }}" class="font-medium text-indigo-600 hover:text-indigo-700 hover:underline">{{ $order->customer->name }}</a>
                </div>
                <div>
                    <span class="block text-slate-500">Phone</span>
                    <span class="font-medium text-slate-900">{{ $order->customer->phone }}</span>
                </div>
                <div>
                    <span class="block text-slate-500">Wallet Balance</span>
                    <span class="inline-flex items-center rounded-md {{ $order->customer->wallet_balance > 0 ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-50 text-slate-600 ring-slate-500/10' }} px-2.5 py-0.5 text-sm font-medium ring-1 ring-inset">
                        ${{ number_format($order->customer->wallet_balance, 2) }}
                    </span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ route('customers.show', $order->customer) }}" class="inline-flex items-center justify-center w-full rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        View Order History
                    </a>
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
