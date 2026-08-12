@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. BACK LINK & HERO HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="hide-on-print">
        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#64748B] hover:text-[#F5703E] transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Orders</span>
        </a>

        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold font-mono text-[#172033]">{{ $order->order_number }}</h1>
                    @if($order->status == 'paid')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">Paid</span>
                    @elseif($order->status == 'pending')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-[#FF9932] border border-amber-200">Pending</span>
                    @elseif($order->status == 'cancelled')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-[#FF4848] border border-red-200">Cancelled</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-[#64748B] border border-slate-200">{{ ucfirst($order->status) }}</span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2.5 mt-1 text-xs text-[#64748B]">
                    <span>Placed on {{ $order->created_at->format('M d, Y') }} at {{ $order->created_at->format('h:i A') }}</span>
                    <span class="text-slate-300">•</span>
                    <span>Cashier: <strong class="text-[#172033]">{{ $order->user->name ?? 'System' }}</strong></span>
                    @if($order->service_type)
                        <span class="text-slate-300">•</span>
                        <span>Service: <strong class="text-[#172033]">{{ ucfirst(str_replace('_', ' ', $order->service_type)) }}</strong></span>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5">
                {{-- Print Bill --}}
                <button onclick="window.print()" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#172033] flex items-center gap-1.5 transition-colors shadow-sm">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print Bill</span>
                </button>

                {{-- WhatsApp --}}
                @php
                    $waMessage = "Receipt for Order {$order->order_number}%0A";
                    $waMessage .= "Date: " . $order->created_at->format('Y-m-d H:i') . "%0A";
                    $waMessage .= "-------------------%0A";
                    foreach($order->items as $item) {
                        $waMessage .= "{$item->quantity}x {$item->product_name} - ₹" . number_format($item->subtotal, 2) . "%0A";
                    }
                    $waMessage .= "-------------------%0A";
                    $waMessage .= "Total: ₹" . number_format($order->total_amount, 2);
                @endphp
                <a href="https://wa.me/?text={{ $waMessage }}" target="_blank" 
                   class="h-10 px-4 rounded-lg bg-[#29AB6C] hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                    <span>WhatsApp</span>
                </a>

                {{-- Cancel Order --}}
                @if($order->status !== 'cancelled')
                <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order and restore stock?')">
                    @csrf
                    <button type="submit" class="h-10 px-4 rounded-lg bg-[#FF4848] hover:bg-red-700 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Cancel Order</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. MAIN ORDER DETAILS & BILL
    ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Items & Financial Breakdown --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden print:hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-[#172033]">Ordered Items</h3>
                        <p class="text-xs text-[#64748B] mt-0.5">Line items breakdown for this order</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-200 text-[#172033]">
                        {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                                <th class="py-3 px-6 text-xs font-semibold text-[#64748B] uppercase">Item</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase text-center">Qty</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase text-right">Price</th>
                                <th class="py-3 px-6 text-xs font-semibold text-[#64748B] uppercase text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB] text-sm">
                            @foreach($order->items as $item)
                            <tr class="hover:bg-[#FFF8F5] transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-[#172033]">{{ $item->product_name }}</div>
                                    @if($item->product && $item->product->sku)
                                        <div class="text-xs text-[#64748B]">SKU: {{ $item->product->sku }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center font-mono font-medium text-[#172033]">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-4 px-4 text-right font-mono text-[#64748B]">
                                    ₹{{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-bold text-[#172033]">
                                    ₹{{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Financial Summary Footer --}}
                <div class="p-6 bg-slate-50/50 border-t border-[#E5E7EB]">
                    <div class="w-full sm:w-80 ml-auto space-y-2.5 text-sm">
                        <div class="flex justify-between text-[#64748B]">
                            <span>Subtotal</span>
                            <span class="font-mono font-medium text-[#172033]">₹{{ number_format($order->items->sum('subtotal'), 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                        <div class="flex justify-between text-[#29AB6C]">
                            <span>Discount Applied</span>
                            <span class="font-mono font-medium">-₹{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-[#64748B] pb-2.5 border-b border-[#E5E7EB]">
                            <span>Tax Amount</span>
                            <span class="font-mono font-medium text-[#172033]">₹{{ number_format($order->tax_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-[#172033] pt-1">
                            <span>Grand Total</span>
                            <span class="font-mono text-lg text-[#F5703E]">₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Payment & Customer Details --}}
        <div class="space-y-6 hide-on-print">

            {{-- Payment Breakdown Card --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75">
                    <h3 class="text-sm font-semibold text-[#172033]">Payment Breakdown</h3>
                </div>
                <div class="p-6 space-y-3.5 text-sm">
                    @if($order->payments && $order->payments->count() > 0)
                        @foreach($order->payments as $payment)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                            <div class="flex items-center gap-2.5">
                                @if($payment->payment_method === 'wallet')
                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                    <span class="font-medium text-[#172033]">Customer Wallet</span>
                                @elseif(is_numeric($payment->payment_method))
                                    @php $accName = $accountNames[$payment->payment_method] ?? 'Account #' . $payment->payment_method; @endphp
                                    <span class="w-2.5 h-2.5 rounded-full {{ str_contains(strtolower($accName), 'cash') ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                                    <span class="font-medium text-[#172033]">{{ $accName }}</span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                                    <span class="font-medium text-[#172033]">{{ ucfirst($payment->payment_method) }}</span>
                                @endif
                            </div>
                            <span class="font-mono font-bold text-[#172033]">₹{{ number_format($payment->amount, 2) }}</span>
                        </div>
                        @endforeach

                        <div class="flex justify-between pt-3 mt-2 border-t border-[#E5E7EB] text-xs font-semibold text-[#64748B]">
                            <span>Total Paid</span>
                            <span class="font-mono font-bold text-[#172033] text-sm">₹{{ number_format($order->payments->sum('amount'), 2) }}</span>
                        </div>

                        @if($order->change_returned > 0)
                        <div class="flex justify-between text-xs text-amber-600 font-semibold">
                            <span>Change Returned</span>
                            <span class="font-mono">₹{{ number_format($order->change_returned, 2) }}</span>
                        </div>
                        @endif
                    @else
                        <div class="flex justify-between">
                            <span class="text-xs text-[#64748B]">Payment Method</span>
                            <span class="text-xs font-semibold text-[#172033]">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer Card --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75">
                    <h3 class="text-sm font-semibold text-[#172033]">Customer Information</h3>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    @if($order->customer)
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-orange-100 text-[#F5703E] flex items-center justify-center font-bold text-sm border border-orange-200">
                                {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                            </div>
                            <div>
                                <a href="{{ route('customers.show', $order->customer) }}" class="font-semibold text-[#172033] hover:text-[#F5703E] transition-colors block">
                                    {{ $order->customer->name }}
                                </a>
                                <span class="text-xs text-[#64748B]">{{ $order->customer->phone ?: 'No phone' }}</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-[#E5E7EB] flex justify-between text-xs">
                            <span class="text-[#64748B]">Wallet Balance:</span>
                            <span class="font-mono font-bold {{ $order->customer->wallet_balance >= 0 ? 'text-[#29AB6C]' : 'text-[#FF4848]' }}">
                                ₹{{ number_format($order->customer->wallet_balance, 2) }}
                            </span>
                        </div>
                    @else
                        <p class="text-xs text-[#64748B]">Walk-in / Guest Customer</p>
                    @endif
                </div>
            </div>

        </div>

    </div>

    {{-- Thermal Print Receipt Section (Preserved for window.print) --}}
    <div class="hidden print:block" style="width: 100%; max-width: 320px; font-family: 'Courier New', Courier, monospace; color: #000; font-size: 12px; line-height: 1.4; margin: 0;">
        <style type="text/css" media="print">
            @page { margin: 0; size: 80mm auto; }
            body { margin: 0; }
        </style>
        <div style="text-align: center; margin-bottom: 12px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase;">{{ config('app.name', 'POS Store') }}</h2>
            <div style="margin: 8px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; font-weight: bold;">
                RECEIPT: #{{ $order->order_number }}
            </div>
        </div>
        <div style="margin-bottom: 12px;">
            <p style="margin: 0;">Date: {{ $order->created_at->format('n/j/Y, g:i:s A') }}</p>
            <p style="margin: 0;">Cashier: {{ $order->user->name ?? 'Admin' }}</p>
            @if($order->customer_id)
                <p style="margin: 0;">Customer: {{ $order->customer->name ?? 'Walk-in' }}</p>
            @endif
        </div>
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
                        <span>{{ Str::limit($item->product_name, 20) }}</span>
                    </td>
                    <td style="text-align: center; vertical-align: top; padding: 4px 0;">{{ $item->quantity }}</td>
                    <td style="text-align: right; vertical-align: top; padding: 4px 0;">₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="border-top: 1px dashed #000; padding-top: 8px;">
            <div style="display: flex; justify-content: space-between;">
                <span>SUBTOTAL:</span>
                <span>₹{{ number_format($order->items->sum('subtotal'), 2) }}</span>
            </div>
            @if($order->discount_amount > 0)
            <div style="display: flex; justify-content: space-between;">
                <span>DISCOUNT:</span>
                <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif
            <div style="display: flex; justify-content: space-between;">
                <span>TAX:</span>
                <span>₹{{ number_format($order->tax_amount ?? 0, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-top: 8px; border-top: 1px dashed #000; padding-top: 8px;">
                <span>TOTAL:</span>
                <span>₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
        <div style="text-align: center; margin-top: 24px; font-size: 12px; border-top: 1px dashed #000; padding-top: 12px;">
            <p style="margin: 0;">THANK YOU FOR VISITING!</p>
        </div>
    </div>

</div>
@endsection
