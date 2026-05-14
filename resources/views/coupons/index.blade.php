@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Coupons</h1>
        <a href="{{ route('coupons.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            Add New Coupon
        </a>
    </div>

    <div class="bg-white shadow-sm border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Code</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Value</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Expiry</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Usage</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($coupons as $coupon)
                <tr>
                    <td class="px-6 py-4 font-mono font-bold text-indigo-600">{{ $coupon->code }}</td>
                    <td class="px-6 py-4 text-sm">{{ ucfirst($coupon->type) }}</td>
                    <td class="px-6 py-4 text-sm">
                        {{ $coupon->type === 'percent' ? $coupon->value . '%' : '$' . number_format($coupon->value, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        {{ $coupon->expiry_date ? $coupon->expiry_date->format('Y-m-d') : 'No Expiry' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $coupon->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('coupons.edit', $coupon) }}" class="text-slate-400 hover:text-indigo-600">
                                Edit
                            </a>
                            <form action="{{ route('coupons.destroy', $coupon) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $coupons->links() }}
    </div>
</div>
@endsection
