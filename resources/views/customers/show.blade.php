@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('customers.index') }}" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ $customer->name }}</h2>
            <p class="mt-1 text-sm text-slate-500">Customer since {{ $customer->created_at->format('F j, Y') }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Left Column: Order History --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Orders</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total_orders'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Spent</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">${{ number_format($stats['total_spent'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Avg Order</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">${{ number_format($stats['avg_order_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Last Visit</p>
                <p class="mt-1 text-lg font-bold text-slate-900">
                    @if($stats['last_order_date'])
                        {{ \Carbon\Carbon::parse($stats['last_order_date'])->format('M j, Y') }}
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>

        {{-- Order History Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Order History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="py-3 pl-6 pr-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Order #</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Items</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Payment</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="pl-3 pr-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="whitespace-nowrap py-3.5 pl-6 pr-3 text-sm font-semibold text-slate-900">
                                {{ $order->order_number }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-slate-500">
                                {{ $order->created_at->format('M j, Y') }}
                                <span class="block text-xs text-slate-400">{{ $order->created_at->format('g:i a') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-slate-500">
                                {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm">
                                @if($order->payments->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($order->payments as $payment)
                                            @if($payment->payment_method === 'wallet')
                                                <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">Wallet</span>
                                            @elseif(is_numeric($payment->payment_method))
                                                @php
                                                    $payAccName = \App\Models\Account::find($payment->payment_method)?->account_name ?? 'Account';
                                                @endphp
                                                <span class="inline-flex items-center rounded-md {{ str_contains(strtolower($payAccName), 'cash') ? 'bg-emerald-50 text-emerald-700 ring-emerald-700/10' : 'bg-blue-50 text-blue-700 ring-blue-700/10' }} px-2 py-0.5 text-xs font-medium ring-1 ring-inset">{{ $payAccName }}</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-700/10">{{ ucfirst($payment->payment_method) }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-right font-semibold text-slate-900">
                                ${{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-center">
                                @if($order->status == 'paid')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Paid</span>
                                @elseif($order->status == 'cancelled')
                                    <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">Cancelled</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-500/10">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap pl-3 pr-6 py-3.5 text-sm text-right">
                                <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">View →</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">
                                No orders found for this customer.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div class="border-t border-slate-200 px-4 py-3 sm:px-6">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Right Column: Customer Info --}}
    <div class="space-y-6">
        {{-- Profile Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 bg-gradient-to-br from-indigo-500 to-indigo-600 text-white">
                <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl font-bold mb-3">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <h3 class="text-lg font-bold">{{ $customer->name }}</h3>
                <p class="text-indigo-200 text-sm">{{ $customer->phone }}</p>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <span class="block text-slate-500">Phone</span>
                    <span class="font-medium text-slate-900">{{ $customer->phone }}</span>
                </div>
                <div>
                    <span class="block text-slate-500">Wallet Balance</span>
                    <span class="inline-flex items-center rounded-md {{ $customer->wallet_balance > 0 ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-50 text-slate-600 ring-slate-500/10' }} px-2.5 py-0.5 text-sm font-semibold ring-1 ring-inset">
                        ${{ number_format($customer->wallet_balance, 2) }}
                    </span>
                </div>
                <div>
                    <span class="block text-slate-500">Registered</span>
                    <span class="font-medium text-slate-900">{{ $customer->created_at->format('M j, Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Quick Actions</h3>
            </div>
            <div class="p-4 space-y-2">
                <a href="{{ route('customers.edit', $customer) }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-50 text-sm font-medium text-slate-700 transition-colors">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Customer
                </a>
                <a href="{{ route('orders.index', ['search' => $customer->phone]) }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-50 text-sm font-medium text-slate-700 transition-colors">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Search Orders
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
