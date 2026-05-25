@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('purchases.index') }}" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 hide-on-print">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Purchase Order #{{ $purchase->purchase_number }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $purchase->purchase_date->format('F j, Y') }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3 hide-on-print">
        <button onclick="window.print()" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Invoice
        </button>

        @if($purchase->payment_status !== 'paid')
        <button @click="$dispatch('open-modal', 'add-payment')" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Record Payment
        </button>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Left Col: Items & Receipt --}}
    <div class="lg:col-span-2 space-y-8">
        
        {{-- Printable Invoice Area --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden print-area">
            <div class="p-6 sm:p-8">
                {{-- Header --}}
                <div class="flex justify-between items-start mb-10 pb-10 border-b border-slate-200 border-dashed">
                    <div>
                        <h1 class="text-2xl font-black text-indigo-600 mb-2 uppercase">{{ config('app.name', 'POS Store') }}</h1>
                        <p class="text-slate-500 text-sm">Purchase Invoice</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Status</div>
                        <span class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-bold text-white">{{ strtoupper($purchase->status) }}</span>
                    </div>
                </div>

                {{-- Parties --}}
                <div class="grid grid-cols-2 gap-8 mb-10">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">From (Supplier)</h3>
                        <div class="font-bold text-slate-900 text-lg">{{ $purchase->supplier->name }}</div>
                        <div class="text-sm text-slate-600 mt-1 space-y-1">
                            <p>{{ $purchase->supplier->address }}</p>
                            <p>{{ $purchase->supplier->phone }}</p>
                            <p>{{ $purchase->supplier->email }}</p>
                            @if($purchase->supplier->tax_number)
                            <p class="pt-2 font-medium">Tax ID: {{ $purchase->supplier->tax_number }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">To (Store)</h3>
                        <div class="font-bold text-slate-900 text-lg">{{ auth()->user()->currentCompany()->name ?? 'Main Branch' }}</div>
                        <div class="text-sm text-slate-600 mt-1 space-y-1">
                            <p>Date: {{ $purchase->purchase_date->format('M j, Y') }}</p>
                            <p>PO Number: #{{ $purchase->purchase_number }}</p>
                            <p>Payment: {{ strtoupper($purchase->payment_status) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <table class="w-full text-left mb-10">
                    <thead>
                        <tr class="text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-200">
                            <th class="pb-3 font-medium">Item Description</th>
                            <th class="pb-3 font-medium text-center">Qty</th>
                            <th class="pb-3 font-medium text-right">Unit Cost</th>
                            <th class="pb-3 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 divide-y divide-slate-100">
                        @foreach($purchase->items as $item)
                        <tr>
                            <td class="py-4">
                                <div class="font-bold text-slate-900">{{ $item->inventoryItem->name }}</div>
                                <div class="text-xs text-slate-400">Code: {{ $item->inventoryItem->code ?? 'N/A' }}</div>
                            </td>
                            <td class="py-4 text-center text-sm font-medium">{{ number_format($item->quantity, 3) }} {{ $item->inventoryItem->unit_type }}</td>
                            <td class="py-4 text-right text-sm">${{ number_format($item->unit_cost, 2) }}</td>
                            <td class="py-4 text-right text-sm font-bold text-slate-900">${{ number_format($item->total_cost, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Totals --}}
                <div class="w-full sm:w-1/2 ml-auto space-y-3 text-slate-600">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-900">${{ number_format($purchase->subtotal, 2) }}</span>
                    </div>
                    @if($purchase->discount > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <span>Discount</span>
                        <span>-${{ number_format($purchase->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($purchase->tax > 0)
                    <div class="flex justify-between text-sm">
                        <span>Tax</span>
                        <span class="font-bold text-slate-900">${{ number_format($purchase->tax, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-xl font-black text-slate-900 pt-4 border-t border-slate-200">
                        <span>Total</span>
                        <span>${{ number_format($purchase->total_amount, 2) }}</span>
                    </div>
                    
                    <div class="pt-4 space-y-2">
                        <div class="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-widest">
                            <span>Paid Amount</span>
                            <span class="text-green-600">${{ number_format($purchase->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-widest">
                            <span>Due Amount</span>
                            <span class="text-red-600">${{ number_format($purchase->due_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if($purchase->notes)
                <div class="mt-12 p-4 bg-slate-50 rounded-lg border border-slate-200 text-sm text-slate-600 italic">
                    <strong>Notes:</strong> {{ $purchase->notes }}
                </div>
                @endif
            </div>
        </div>

        {{-- Payment History --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hide-on-print">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-sm">Payment History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Method</th>
                            <th class="px-6 py-3">Ref #</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchase->payments as $payment)
                        <tr class="text-sm text-slate-600">
                            <td class="px-6 py-4">{{ $payment->payment_date->format('M j, Y') }}</td>
                            <td class="px-6 py-4">{{ $payment->payment_method }}</td>
                            <td class="px-6 py-4">{{ $payment->reference_number ?? '-' }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">${{ number_format($payment->paid_amount, 2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('purchase-payments.destroy', $payment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this payment?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-slate-300 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">No payments recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Col: Workflow & Meta --}}
    <div class="space-y-6 hide-on-print">
        {{-- Status Update Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-sm">Purchase Workflow</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('purchases.status', $purchase) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Update Status</label>
                            <select name="status" class="w-full rounded-lg border-slate-200 text-sm">
                                <option value="draft" {{ $purchase->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ $purchase->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="received" {{ $purchase->status == 'received' ? 'selected' : '' }}>Received (Stock +)</option>
                                <option value="completed" {{ $purchase->status == 'completed' ? 'selected' : '' }}>Completed (Stock +)</option>
                                <option value="cancelled" {{ $purchase->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded-lg font-bold text-xs hover:bg-indigo-700 transition-colors">
                            Update Workflow
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Meta Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-sm">Order Meta</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Created By</span>
                    <span class="font-bold text-slate-900">{{ $purchase->creator->name ?? 'System' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Created At</span>
                    <span class="font-bold text-slate-900">{{ $purchase->created_at->format('M j, Y H:i') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Payment Status</span>
                    <span class="font-bold text-slate-900 uppercase text-[10px]">{{ $purchase->payment_status }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Payment Modal --}}
<x-modal name="add-payment" focusable>
    <form action="{{ route('purchase-payments.store', $purchase) }}" method="POST" class="p-6">
        @csrf
        <h2 class="text-lg font-bold text-slate-800 mb-6 border-b pb-4">Record Supplier Payment</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <div class="p-3 bg-amber-50 border border-amber-100 rounded-lg text-amber-800 text-xs flex justify-between font-bold">
                    <span>Remaining Balance:</span>
                    <span>${{ number_format($purchase->due_amount, 2) }}</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Paid Amount *</label>
                <input type="number" name="paid_amount" value="{{ $purchase->due_amount }}" step="0.01" max="{{ $purchase->due_amount }}" required class="w-full rounded-lg border-slate-200 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date *</label>
                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-slate-200 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method *</label>
                <select name="payment_method" required class="w-full rounded-lg border-slate-200 text-sm">
                    <option value="Cash">Cash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number</label>
                <input type="text" name="reference_number" placeholder="TXN-XXXXXX" class="w-full rounded-lg border-slate-200 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-slate-200 text-sm"></textarea>
            </div>
        </div>
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 shadow-sm">Confirm Payment</button>
        </div>
    </form>
</x-modal>

<style>
    @media print {
        body { visibility: hidden; background-color: white; padding: 0; margin: 0; }
        .print-area { visibility: visible; position: absolute; left: 0; top: 0; width: 100%; border: none; box-shadow: none; }
        .hide-on-print { display: none !important; }
        aside, header { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
    }
</style>
@endsection
