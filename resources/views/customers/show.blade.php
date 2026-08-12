@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. BACK LINK & HERO HEADER CARD
    ════════════════════════════════════════════════════════════ --}}
    <div>
        <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#64748B] hover:text-[#F5703E] transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Customers</span>
        </a>

        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                {{-- Customer Avatar --}}
                <div class="w-14 h-14 rounded-full bg-orange-100 text-[#F5703E] flex items-center justify-center font-bold text-xl flex-shrink-0 border-2 border-orange-200">
                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-[#172033]">{{ $customer->name }}</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">
                            Active Customer
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5 mt-1 text-xs text-[#64748B]">
                        <span class="inline-flex items-center gap-1 text-[#172033] font-medium">
                            <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span>{{ $customer->phone ?: 'No phone provided' }}</span>
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="text-[#64748B]">Customer ID: <strong class="text-[#172033]">#CUST-{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}</strong></span>
                        <span class="text-slate-300">•</span>
                        <span class="text-[#64748B]">Member since {{ $customer->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Header Actions --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.index', ['search' => $customer->phone]) }}" 
                   class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#172033] flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Search Orders</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. COMPACT SUMMARY KPI CARDS
    ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm">
            <span class="text-xs font-medium text-[#64748B] uppercase tracking-wider">Wallet Balance</span>
            <div class="text-2xl font-bold font-mono {{ $customer->wallet_balance >= 0 ? 'text-[#29AB6C]' : 'text-[#FF4848]' }} mt-1">
                ₹{{ number_format($customer->wallet_balance, 2) }}
            </div>
            <p class="text-xs text-[#64748B] mt-0.5">Available prepaid credits</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm">
            <span class="text-xs font-medium text-[#64748B] uppercase tracking-wider">Total Orders</span>
            <div class="text-2xl font-bold text-[#172033] mt-1">{{ $stats['total_orders'] }}</div>
            <p class="text-xs text-[#64748B] mt-0.5">Completed dining sessions</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm">
            <span class="text-xs font-medium text-[#64748B] uppercase tracking-wider">Lifetime Spend</span>
            <div class="text-2xl font-bold font-mono text-[#172033] mt-1">₹{{ number_format($stats['total_spent'], 2) }}</div>
            <p class="text-xs text-[#29AB6C] mt-0.5">Avg ₹{{ number_format($stats['avg_order_value'], 2) }} per order</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm">
            <span class="text-xs font-medium text-[#64748B] uppercase tracking-wider">Last Visit</span>
            <div class="text-2xl font-bold text-[#172033] mt-1">
                @if($stats['last_order_date'])
                    {{ \Carbon\Carbon::parse($stats['last_order_date'])->format('M d, Y') }}
                @else
                    —
                @endif
            </div>
            <p class="text-xs text-[#64748B] mt-0.5">Recent restaurant order</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. MAIN CONTENT: TABLES & WALLET ADJUSTMENT
    ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Order History & Wallet Ledger --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Order History Table --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-[#172033]">Order History</h3>
                        <p class="text-xs text-[#64748B] mt-0.5">All dining orders placed by this customer</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-200 text-[#172033]">{{ $orders->total() }} Orders</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                                <th class="py-3 px-6 text-xs font-semibold text-[#64748B] uppercase">Order #</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase">Date</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase">Items</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase">Payment</th>
                                <th class="py-3 px-4 text-right text-xs font-semibold text-[#64748B] uppercase">Total</th>
                                <th class="py-3 px-4 text-center text-xs font-semibold text-[#64748B] uppercase">Status</th>
                                <th class="py-3 px-6 text-right text-xs font-semibold text-[#64748B] uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB] text-sm">
                            @forelse($orders as $order)
                            <tr class="hover:bg-[#FFF8F5] transition-colors">
                                <td class="py-3.5 px-6 font-bold font-mono text-[#172033]">
                                    {{ $order->order_number }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-xs font-medium text-[#172033]">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-[11px] text-[#94A3B8]">{{ $order->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-[#64748B]">
                                    {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if($order->payments->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($order->payments as $payment)
                                                @if($payment->payment_method === 'wallet')
                                                    <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 border border-purple-200">Wallet</span>
                                                @elseif(is_numeric($payment->payment_method))
                                                    @php
                                                        $payAccName = \App\Models\Account::find($payment->payment_method)?->account_name ?? 'Account';
                                                    @endphp
                                                    <span class="inline-flex items-center rounded-md {{ str_contains(strtolower($payAccName), 'cash') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }} px-2 py-0.5 text-xs font-medium">{{ $payAccName }}</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700 border border-slate-200">{{ ucfirst($payment->payment_method) }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-[#172033]">
                                    ₹{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($order->status == 'paid')
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-[#29AB6C] border border-emerald-200">Paid</span>
                                    @elseif($order->status == 'cancelled')
                                        <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-[#FF4848] border border-red-200">Cancelled</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700 border border-slate-200">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <a href="{{ route('orders.show', $order) }}" class="text-xs font-semibold text-[#F5703E] hover:underline">
                                        View →
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-xs text-[#64748B]">
                                    No orders found for this customer yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                <div class="border-t border-[#E5E7EB] px-6 py-3.5 bg-slate-50/50">
                    {{ $orders->links() }}
                </div>
                @endif
            </div>

            {{-- Wallet Transactions Table --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-[#172033]">Wallet Transactions Ledger</h3>
                        <p class="text-xs text-[#64748B] mt-0.5">Audit log of credits and debits</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                                <th class="py-3 px-6 text-xs font-semibold text-[#64748B] uppercase">Date</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase">Order Ref</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase">Type</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase">Amount</th>
                                <th class="py-3 px-6 text-xs font-semibold text-[#64748B] uppercase">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB] text-sm">
                            @forelse($walletTransactions as $tx)
                            <tr class="hover:bg-[#FFF8F5] transition-colors">
                                <td class="py-3.5 px-6">
                                    <div class="text-xs font-medium text-[#172033]">{{ $tx->created_at->format('M d, Y') }}</div>
                                    <div class="text-[11px] text-[#94A3B8]">{{ $tx->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono font-bold text-[#F5703E]">
                                    @if($tx->order)
                                        <a href="{{ route('orders.show', $tx->order_id) }}" class="hover:underline">{{ $tx->order->order_number }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($tx->type === 'credit')
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-[#29AB6C] border border-emerald-200">Credit</span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-[#FF4848] border border-red-200">Debit</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono font-bold {{ $tx->type === 'credit' ? 'text-[#29AB6C]' : 'text-[#FF4848]' }}">
                                    {{ $tx->type === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                                </td>
                                <td class="py-3.5 px-6 text-xs text-[#64748B]">
                                    {{ $tx->description ?: '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-xs text-[#64748B]">
                                    No wallet transactions recorded.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($walletTransactions->hasPages())
                <div class="border-t border-[#E5E7EB] px-6 py-3.5 bg-slate-50/50">
                    {{ $walletTransactions->links() }}
                </div>
                @endif
            </div>

        </div>

        {{-- Right Column: Wallet Adjustment Card --}}
        <div class="space-y-6">
            
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75">
                    <h3 class="text-sm font-semibold text-[#172033]">Adjust Wallet Balance</h3>
                    <p class="text-xs text-[#64748B] mt-0.5">Add prepaid funds or manually deduct balance</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('customers.wallet.adjust', $customer) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="type" class="block text-xs font-semibold text-[#172033]">Adjustment Type</label>
                            <select id="type" name="type" required class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                                <option value="credit">Add Funds (Credit)</option>
                                <option value="debit">Deduct Funds (Debit)</option>
                            </select>
                        </div>
                        <div>
                            <label for="amount" class="block text-xs font-semibold text-[#172033]">Amount (₹)</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[#64748B] font-mono text-sm font-semibold">₹</span>
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" required 
                                       class="w-full h-11 pl-8 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label for="description" class="block text-xs font-semibold text-[#172033]">Description / Reason</label>
                            <input type="text" name="description" id="description" class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" placeholder="e.g. Counter deposit">
                        </div>
                        <button type="submit" class="w-full h-11 rounded-lg bg-[#F5703E] hover:bg-[#E05826] text-white text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                            <span>Process Adjustment</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
