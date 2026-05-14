@extends('layouts.app')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Customers</h2>
        <p class="mt-1 text-sm text-slate-500">A list of all customers and their wallet balances.</p>
    </div>
    
    <div class="mt-4 sm:mt-0 relative w-full sm:w-72">
        <form action="{{ route('customers.index') }}" method="GET">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or phone..." 
                       class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">Name</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Phone</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Wallet Balance</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Registered On</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($customers as $customer)
                <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                        <div class="font-medium text-slate-900">{{ $customer->name }}</div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                        {{ $customer->phone }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                        <span class="inline-flex items-center rounded-md {{ $customer->wallet_balance > 0 ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-50 text-slate-600 ring-slate-500/10' }} px-2.5 py-0.5 text-sm font-medium ring-1 ring-inset">
                            ${{ number_format($customer->wallet_balance, 2) }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                        {{ $customer->created_at->format('M d, Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-3 py-8 text-center text-sm text-slate-500">
                        @if(request('search'))
                            No customers found matching "{{ request('search') }}". <a href="{{ route('customers.index') }}" class="text-indigo-600 hover:underline">Clear search</a>.
                        @else
                            No customers found. Customers are automatically created during checkout in the POS.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="border-t border-slate-200 px-4 py-3 sm:px-6">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection
