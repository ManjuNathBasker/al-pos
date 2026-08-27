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
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-bold font-mono text-[#172033]">{{ $order->order_number }}</h1>
                    
                    {{-- Order Status Badge --}}
                    @if($order->status == 'paid')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-[#29AB6C] border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Paid
                        </span>
                    @elseif($order->status == 'pending')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-[#FF9932] border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                        </span>
                    @elseif($order->status == 'cancelled')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-[#FF4848] border border-red-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Cancelled
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-[#64748B] border border-slate-200">{{ ucfirst($order->status) }}</span>
                    @endif

                    {{-- Service Type Badge --}}
                    @php
                        $stKey = $order->service_type ?: ($order->table_id ? 'dine_in' : 'retail');
                        $serviceType = $stKey;
                        $serviceLabel = match ($stKey) {
                            'dine_in' => 'Dine-In',
                            'takeaway', 'pickup' => 'Takeaway',
                            'delivery' => 'Delivery',
                            default => 'Counter',
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold 
                        {{ $stKey === 'delivery' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                        {{ $stKey === 'dine_in' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                        {{ $stKey === 'takeaway' || $stKey === 'pickup' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                        {{ in_array($stKey, ['retail', 'counter']) ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                        ⚡ {{ $serviceLabel }}
                    </span>

                    {{-- Delivery Status Badge (If Delivery) --}}
                    @if($serviceType === 'delivery' && $order->delivery_status)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                            🚚 Delivery: {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}
                        </span>
                    @endif

                    {{-- Delivery Settlement Status Badge --}}
                    @if($order->settlement_status)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->settlement_status === 'settled' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            Partner Settlement: {{ ucfirst($order->settlement_status) }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2.5 mt-2 text-xs text-[#64748B]">
                    <span>Placed on {{ $order->created_at->format('M d, Y') }} at {{ $order->created_at->format('h:i A') }}</span>
                    <span class="text-slate-300">•</span>
                    <span>Cashier: <strong class="text-[#172033]">{{ $order->user->name ?? 'System' }}</strong></span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5">
                {{-- Print Bill --}}
                <button onclick="window.print()" class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#172033] flex items-center gap-1.5 transition-colors shadow-sm">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print Bill</span>
                </button>

                {{-- WhatsApp Receipt --}}
                @php
                    $waMessage = "Receipt for Order {$order->order_number}%0A";
                    $waMessage .= "Date: " . $order->created_at->format('Y-m-d H:i') . "%0A";
                    $waMessage .= "-------------------%0A";
                    foreach($order->items as $item) {
                        $waMessage .= "{$item->quantity}x {$item->product_name} - " . format_currency($item->subtotal, $order) . "%0A";
                    }
                    $waMessage .= "-------------------%0A";
                    $waMessage .= "Total: " . format_currency($order->total_amount, $order);
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
         2. MAIN CONTENT GRID
    ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Items & Financial Breakdown --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Ordered Items Table --}}
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
                                    @currency($item->unit_price, $order)
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-bold text-[#172033]">
                                    @currency($item->subtotal, $order)
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
                            <span>Items Subtotal</span>
                            <span class="font-mono font-medium text-[#172033]">@currency($order->items->sum('subtotal'), $order)</span>
                        </div>
                        @if($order->discount_amount > 0)
                        <div class="flex justify-between text-[#29AB6C]">
                            <span>Discount Applied {{ $order->coupon ? '('.$order->coupon->code.')' : '' }}</span>
                            <span class="font-mono font-medium">-@currency($order->discount_amount, $order)</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-[#64748B]">
                            <span>Tax Amount</span>
                            <span class="font-mono font-medium text-[#172033]">@currency($order->tax_amount ?? 0, $order)</span>
                        </div>
                        @if($order->delivery_commission_amount > 0)
                        <div class="flex justify-between text-blue-600">
                            <span>Delivery Partner Commission</span>
                            <span class="font-mono font-medium">@currency($order->delivery_commission_amount, $order)</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-base font-bold text-[#172033] pt-2 border-t border-[#E5E7EB]">
                            <span>Grand Total</span>
                            <span class="font-mono text-lg text-[#F5703E]">@currency($order->total_amount, $order)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Notes (If Any) --}}
            @if($order->note)
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 hide-on-print">
                <h3 class="text-sm font-semibold text-[#172033] mb-1">Order Notes</h3>
                <p class="text-xs text-[#64748B] bg-slate-50 p-3 rounded-lg border border-slate-100 leading-relaxed font-mono">
                    {{ $order->note }}
                </p>
            </div>
            @endif

        </div>

        {{-- Right Column: Service, Delivery, Customer & Payment Details --}}
        <div class="space-y-6 hide-on-print">

            {{-- Service Mode & Dining Details --}}
            @if($serviceType === 'dine_in' || $order->table || $order->waiter)
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-amber-50/50 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-amber-900">🪑 Dining Details</h3>
                    <span class="text-xs font-bold text-amber-700 uppercase">Dine-In Order</span>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    @if($order->table)
                    <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                        <span class="text-xs text-[#64748B]">Table</span>
                        <span class="font-bold text-[#172033]">{{ $order->table->name }} {{ $order->table->section ? '('.$order->table->section->name.')' : '' }}</span>
                    </div>
                    @endif
                    @if($order->waiter)
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-[#64748B]">Assigned Waiter</span>
                        <span class="font-semibold text-[#172033]">{{ $order->waiter->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Delivery & Address Card (If Delivery Service Mode or Delivery Partner exists) --}}
            @if($serviceType === 'delivery' || $order->delivery_partner_id || $order->billing_address)
            <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/60 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-blue-900">🚚 Delivery Information</h3>
                    <span class="text-xs font-bold text-blue-700 uppercase">Delivery</span>
                </div>
                <div class="p-6 space-y-3.5 text-sm">
                    {{-- Delivery Partner --}}
                    @php $partner = $order->deliveryPartner ?? ($order->delivery_partner_id ? \App\Models\DeliveryPartner::find($order->delivery_partner_id) : null); @endphp
                    @if($partner)
                    <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                        <span class="text-xs text-[#64748B]">Delivery Partner</span>
                        <span class="font-bold text-blue-900 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                            {{ $partner->name }} ({{ $partner->commission_percentage }}%)
                        </span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                        <span class="text-xs text-[#64748B]">Partner Commission</span>
                        <span class="font-mono font-bold text-blue-800">@currency($order->delivery_commission_amount, $order)</span>
                    </div>
                    @endif

                    {{-- Delivery Status --}}
                    @if($order->delivery_status)
                    <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                        <span class="text-xs text-[#64748B]">Delivery Status</span>
                        <span class="font-semibold text-xs text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                            {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}
                        </span>
                    </div>
                    @endif

                    {{-- Partner Settlement Status --}}
                    @if($order->settlement_status)
                    <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                        <span class="text-xs text-[#64748B]">Settlement Status</span>
                        <span class="font-semibold text-xs {{ $order->settlement_status === 'settled' ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-amber-700 bg-amber-50 border-amber-200' }} px-2 py-0.5 rounded border">
                            {{ ucfirst($order->settlement_status) }}
                        </span>
                    </div>
                    @endif

                    {{-- Delivery Address --}}
                    @php $address = $order->billing_address ?: ($order->customer ? $order->customer->address : null); @endphp
                    @if($address)
                    <div>
                        <span class="block text-xs font-semibold text-[#64748B] mb-1">Delivery Address</span>
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-[#172033] font-medium leading-relaxed">
                            {{ $address }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Customer Information Card --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#172033]">Customer Information</h3>
                    <span class="text-xs font-medium text-[#64748B]">Profile & Contact</span>
                </div>
                <div class="p-6 space-y-3.5 text-sm">
                    @php
                        $custName = $order->customer ? $order->customer->name : ($order->customer_name ?: 'Walk-in Customer');
                        $custPhone = $order->customer ? $order->customer->phone : ($order->customer_phone ?: null);
                    @endphp

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-[#F5703E] flex items-center justify-center font-bold text-sm border border-orange-200 flex-shrink-0">
                            {{ strtoupper(substr($custName, 0, 2)) }}
                        </div>
                        <div>
                            @if($order->customer)
                                <a href="{{ route('customers.show', $order->customer) }}" class="font-semibold text-[#172033] hover:text-[#F5703E] transition-colors block">
                                    {{ $custName }}
                                </a>
                            @else
                                <span class="font-semibold text-[#172033] block">{{ $custName }}</span>
                            @endif
                            <span class="text-xs text-[#64748B]">{{ $custPhone ?: 'No phone provided' }}</span>
                        </div>
                    </div>

                    @if($order->customer)
                    <div class="pt-2.5 border-t border-[#E5E7EB] flex justify-between text-xs">
                        <span class="text-[#64748B]">Customer Wallet Balance:</span>
                        <span class="font-mono font-bold {{ $order->customer->wallet_balance >= 0 ? 'text-[#29AB6C]' : 'text-[#FF4848]' }}">
                            @currency($order->customer->wallet_balance)
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Payment Breakdown & Card Commission Card --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#172033]">Payment Breakdown</h3>
                    <span class="text-xs font-semibold text-slate-500">Transactions</span>
                </div>
                <div class="p-6 space-y-3 text-sm">
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
                            <span class="font-mono font-bold text-[#172033]">@currency($payment->amount, $order)</span>
                        </div>
                        @endforeach

                        <div class="flex justify-between pt-3 mt-2 border-t border-[#E5E7EB] text-xs font-semibold text-[#64748B]">
                            <span>Total Paid</span>
                            <span class="font-mono font-bold text-[#172033] text-sm">@currency($order->payments->sum('amount'), $order)</span>
                        </div>

                        @if($order->change_returned > 0)
                        <div class="flex justify-between text-xs text-amber-600 font-semibold pt-1">
                            <span>Change Returned</span>
                            <span class="font-mono">@currency($order->change_returned, $order)</span>
                        </div>
                        @endif
                    @else
                        <div class="flex justify-between">
                            <span class="text-xs text-[#64748B]">Payment Method</span>
                            <span class="text-xs font-semibold text-[#172033]">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                        </div>
                    @endif

                    {{-- Card Commission Settlement Breakdown --}}
                    @if($order->card_commission_amount > 0 || $order->card_type_id)
                    @php $cardType = $order->cardType ?? ($order->card_type_id ? \App\Models\CardType::find($order->card_type_id) : null); @endphp
                    <div class="mt-4 pt-3 border-t border-slate-200 bg-slate-50 p-3 rounded-lg space-y-1.5 text-xs">
                        <div class="font-bold text-slate-800 flex justify-between items-center mb-1">
                            <span>💳 Card Processing Settlement</span>
                            <span class="text-[10px] bg-slate-200 px-1.5 py-0.5 rounded font-mono">{{ $cardType->name ?? 'Card Settlement' }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Bank Commission Fee:</span>
                            <span class="font-mono">@currency($order->card_commission_amount, $order)</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Commission Tax:</span>
                            <span class="font-mono">@currency($order->card_commission_tax_amount, $order)</span>
                        </div>
                        <div class="flex justify-between font-semibold text-slate-900 border-t border-slate-200 pt-1.5 mt-1">
                            <span>Total Bank Deduction:</span>
                            <span class="font-mono text-red-600">-@currency($order->card_commission_total_deduction, $order)</span>
                        </div>
                        <div class="flex justify-between font-bold text-emerald-700 bg-emerald-50 p-1.5 rounded border border-emerald-200 mt-1">
                            <span>Net Revenue Received:</span>
                            <span class="font-mono">@currency($order->card_net_received, $order)</span>
                        </div>
                    </div>
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
            @if($order->customer_id || $order->customer_name)
                <p style="margin: 0;">Customer: {{ $order->customer->name ?? $order->customer_name }}</p>
            @endif
            @if($order->service_type)
                <p style="margin: 0;">Service Mode: {{ $serviceLabel }}</p>
            @endif
            @if($order->billing_address)
                <p style="margin: 0;">Address: {{ $order->billing_address }}</p>
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
                    <td style="text-align: right; vertical-align: top; padding: 4px 0;">@currency($item->subtotal, $order)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="border-top: 1px dashed #000; padding-top: 8px;">
            <div style="display: flex; justify-content: space-between;">
                <span>SUBTOTAL:</span>
                <span>@currency($order->items->sum('subtotal'), $order)</span>
            </div>
            @if($order->discount_amount > 0)
            <div style="display: flex; justify-content: space-between;">
                <span>DISCOUNT:</span>
                <span>-@currency($order->discount_amount, $order)</span>
            </div>
            @endif
            <div style="display: flex; justify-content: space-between;">
                <span>TAX:</span>
                <span>@currency($order->tax_amount ?? 0, $order)</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-top: 8px; border-top: 1px dashed #000; padding-top: 8px;">
                <span>TOTAL:</span>
                <span>@currency($order->total_amount, $order)</span>
            </div>
        </div>
        <div style="text-align: center; margin-top: 24px; font-size: 12px; border-top: 1px dashed #000; padding-top: 12px;">
            <p style="margin: 0;">THANK YOU FOR VISITING!</p>
        </div>
    </div>

</div>
@endsection
