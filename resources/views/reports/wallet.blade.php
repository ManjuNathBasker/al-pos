@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Wallet Report</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Track customer wallet balances and transaction history.</p>
        </div>
    </div>

    {{-- KPI Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Total Outstanding Liability</p>
            <p class="text-2xl font-bold font-mono text-[#F5703E] mt-1.5">₹{{ number_format($totalWalletBalance, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5">
            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-wider">Filtered Transactions</p>
            <p class="text-2xl font-bold text-[#172033] mt-1.5">{{ number_format($transactions->total()) }}</p>
        </div>
    </div>

    {{-- Filter + Table Card --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        {{-- Filter Toolbar --}}
        <div class="p-4 border-b border-[#E5E7EB] bg-slate-50/75">
            <form action="{{ route('reports.wallet') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-[#172033] mb-1.5">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                           class="h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#172033] mb-1.5">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                           class="h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#172033] mb-1.5">Customer</label>
                    <select name="customer_id" class="h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} ({{ $customer->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#172033] mb-1.5">Type</label>
                    <select name="type" class="h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit (Added)</option>
                        <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit (Deducted)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-10 px-4 rounded-lg btn-brand text-white text-sm font-medium transition-colors shadow-sm">Filter</button>
                    <a href="{{ route('reports.wallet') }}" class="h-10 px-3.5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-100 text-xs font-semibold text-[#64748B] flex items-center transition-colors">Clear</a>
                </div>
            </form>
        </div>

        {{-- Transactions Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Customer</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Order Ref</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Type</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Amount</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4 text-xs font-medium text-[#172033]">{{ $tx->created_at->format('M d, Y h:i A') }}</td>
                        <td class="py-4 px-4">
                            <a href="{{ route('customers.show', $tx->customer_id) }}" class="text-xs font-semibold text-[#F5703E] hover:underline">
                                {{ $tx->customer->name ?? 'Unknown' }}
                            </a>
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">
                            @if($tx->order)
                                <a href="{{ route('orders.show', $tx->order_id) }}" class="text-[#F5703E] hover:underline font-mono">{{ $tx->order->order_number }}</a>
                            @else
                                <span class="text-[#94A3B8]">—</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            @if($tx->type === 'credit')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">Credit</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-[#FF4848] border border-red-200">Debit</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right text-sm font-bold font-mono {{ $tx->type === 'credit' ? 'text-[#29AB6C]' : 'text-[#FF4848]' }}">
                            {{ $tx->type === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B] max-w-xs truncate">{{ $tx->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-xs text-[#94A3B8]">No wallet transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
