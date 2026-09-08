@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. PAGE HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Customers</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Manage customer relationships, purchase history, and store wallet balances.</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. COMPACT SUMMARY KPI CARDS
    ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Customers --}}
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-[#64748B] uppercase tracking-wider">Total Customers</p>
                <h3 class="text-2xl font-bold text-[#172033] mt-1">{{ number_format($customers->total()) }}</h3>
                <p class="text-xs text-[#29AB6C] font-medium mt-0.5">Active directory</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-orange-50 text-[#F5703E] border border-orange-100 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5m10 0H7"></path></svg>
            </div>
        </div>

        {{-- Active Balance Count --}}
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-[#64748B] uppercase tracking-wider">Credit Accounts</p>
                <h3 class="text-2xl font-bold text-[#172033] mt-1">{{ number_format($customers->where('wallet_balance', '>', 0)->count()) }}</h3>
                <p class="text-xs text-[#64748B] font-medium mt-0.5">With positive balance</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-[#29AB6C] border border-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        {{-- Total Wallet Balance --}}
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-[#64748B] uppercase tracking-wider">Net Wallet Balance</p>
                <h3 class="text-2xl font-bold font-mono text-[#172033] mt-1">@currency($customers->sum('wallet_balance'))</h3>
                <p class="text-xs text-[#64748B] font-medium mt-0.5">Prepaid customer credits</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
        </div>

        {{-- Registered Summary --}}
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-[#64748B] uppercase tracking-wider">Page Results</p>
                <h3 class="text-2xl font-bold text-[#172033] mt-1">{{ $customers->count() }}</h3>
                <p class="text-xs text-[#64748B] font-medium mt-0.5">Showing in current view</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. TOOLBAR (SEARCH & FILTERS)
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
        <form action="{{ route('customers.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex flex-1 items-center gap-3">
                {{-- Search Input (44px H) --}}
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customers, phone, email..." 
                           class="w-full h-11 pl-10 pr-4 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E] transition-colors">
                </div>

                {{-- Submit Search Button --}}
                <button type="submit" class="h-11 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-sm font-medium text-[#172033] flex items-center gap-2 transition-colors">
                    <span>Search</span>
                </button>

                @if(request('search'))
                    <a href="{{ route('customers.index') }}" class="h-11 px-3.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-[#64748B] flex items-center gap-1.5 transition-colors">
                        <span>Clear</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         4. MAIN CUSTOMER TABLE
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Customer</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Phone</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Wallet Balance</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Registered</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-[#FFF8F5] transition-colors group">
                        {{-- Customer Avatar & Name --}}
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-orange-100 text-[#F5703E] flex items-center justify-center font-bold text-sm flex-shrink-0 border border-orange-200">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('customers.show', $customer) }}" class="text-sm font-semibold text-[#172033] hover:text-[#F5703E] transition-colors block">
                                        {{ $customer->name }}
                                    </a>
                                    <span class="text-xs text-[#64748B]">Customer #{{ $customer->id }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Phone Number --}}
                        <td class="py-4 px-4 text-sm font-medium text-[#172033]">
                            {{ $customer->phone ?: '—' }}
                        </td>

                        {{-- Wallet Balance --}}
                        <td class="py-4 px-4">
                            @if($customer->wallet_balance > 0)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold font-mono bg-emerald-50 text-[#29AB6C] border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#29AB6C]"></span>
                                    @currency($customer->wallet_balance)
                                </span>
                            @elseif($customer->wallet_balance < 0)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold font-mono bg-red-50 text-[#FF4848] border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF4848]"></span>
                                    @currency($customer->wallet_balance)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold font-mono bg-slate-100 text-[#64748B] border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    @currency(0)
                                </span>
                            @endif
                        </td>

                        {{-- Registered Date --}}
                        <td class="py-4 px-4">
                            <div class="text-xs font-medium text-[#172033]">{{ $customer->created_at->format('M d, Y') }}</div>
                            <div class="text-[11px] text-[#94A3B8]">{{ $customer->created_at->diffForHumans() }}</div>
                        </td>

                        {{-- Action Icon Buttons (34x34px) --}}
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                {{-- View Details --}}
                                <a href="{{ route('customers.show', $customer) }}" title="View Details"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-100 text-[#64748B] hover:text-[#172033] flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">
                                👥
                            </div>
                            <h3 class="text-sm font-bold text-[#172033]">
                                @if(request('search'))
                                    No customers found matching "{{ request('search') }}"
                                @else
                                    No customer records found
                                @endif
                            </h3>
                            <p class="text-xs text-[#64748B] mt-1 max-w-sm mx-auto">
                                Customers are registered automatically when dining at tables or during POS checkout.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 5. Pagination Bar --}}
        @if($customers->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E5E7EB] bg-slate-50/50">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
