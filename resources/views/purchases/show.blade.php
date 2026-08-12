@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         1. PAGE HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.index') }}" title="Back to Purchase Orders"
               class="w-9 h-9 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#64748B] flex items-center justify-center transition-colors hide-on-print">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Purchase Order #{{ $purchase->purchase_number }}</h1>
                <p class="text-sm text-[#64748B] mt-0.5">{{ $purchase->purchase_date->format('F j, Y') }} &bull; {{ $purchase->supplier->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 hide-on-print">
            <button onclick="window.print()" 
                    class="h-9 px-3.5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#64748B] flex items-center gap-2 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Invoice</span>
            </button>
            @if($purchase->payment_status !== 'paid')
            <button @click="$dispatch('open-modal', 'add-payment')" 
                    class="h-9 px-3.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-2 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Record Payment</span>
            </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ════════════════════════════════════════════════════════════
             2. LEFT: Invoice & Payment History
        ════════════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Printable Invoice Card --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden print-area">
                <div class="p-6 sm:p-8">

                    {{-- Invoice Header --}}
                    <div class="flex justify-between items-start mb-8 pb-6 border-b border-dashed border-[#E5E7EB]">
                        <div>
                            <h2 class="text-xl font-bold text-[#F5703E] uppercase tracking-tight">{{ config('app.name', 'POS Store') }}</h2>
                            <p class="text-xs text-[#64748B] mt-0.5 uppercase tracking-widest">Purchase Invoice</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-[#94A3B8] uppercase tracking-widest font-semibold mb-1">Status</p>
                            @php
                                $statusBadge = [
                                    'draft'     => 'bg-slate-100 text-[#64748B] border-slate-200',
                                    'approved'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'received'  => 'bg-orange-50 text-[#F5703E] border-orange-200',
                                    'completed' => 'bg-emerald-50 text-[#29AB6C] border-emerald-200',
                                    'cancelled' => 'bg-red-50 text-[#FF4848] border-red-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border uppercase {{ $statusBadge[$purchase->status] ?? 'bg-slate-100 text-[#64748B] border-slate-200' }}">
                                {{ $purchase->status }}
                            </span>
                        </div>
                    </div>

                    {{-- Parties --}}
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-widest mb-2">From (Supplier)</p>
                            <div class="text-sm font-bold text-[#172033]">{{ $purchase->supplier->name }}</div>
                            <div class="text-xs text-[#64748B] mt-1 space-y-0.5">
                                @if($purchase->supplier->address)<p>{{ $purchase->supplier->address }}</p>@endif
                                @if($purchase->supplier->phone)<p>{{ $purchase->supplier->phone }}</p>@endif
                                @if($purchase->supplier->email)<p>{{ $purchase->supplier->email }}</p>@endif
                                @if($purchase->supplier->tax_number)<p class="font-medium pt-1">Tax ID: {{ $purchase->supplier->tax_number }}</p>@endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-[#94A3B8] uppercase tracking-widest mb-2">To (Store)</p>
                            <div class="text-sm font-bold text-[#172033]">{{ auth()->user()->currentCompany()->name ?? 'Main Branch' }}</div>
                            <div class="text-xs text-[#64748B] mt-1 space-y-0.5">
                                <p>Date: {{ $purchase->purchase_date->format('M j, Y') }}</p>
                                <p>PO Number: #{{ $purchase->purchase_number }}</p>
                                <p class="font-semibold uppercase">Payment: {{ $purchase->payment_status }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <table class="w-full text-left mb-8">
                        <thead>
                            <tr class="border-b border-[#E5E7EB]">
                                <th class="pb-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Item Description</th>
                                <th class="pb-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-center">Qty</th>
                                <th class="pb-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Unit Cost</th>
                                <th class="pb-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @foreach($purchase->items as $item)
                            <tr>
                                <td class="py-4">
                                    <div class="text-sm font-semibold text-[#172033]">{{ $item->inventoryItem->name }}</div>
                                    <div class="text-xs text-[#94A3B8]">Code: {{ $item->inventoryItem->code ?? 'N/A' }}</div>
                                </td>
                                <td class="py-4 text-center text-xs font-mono font-medium text-[#172033]">{{ number_format($item->quantity, 3) }} {{ $item->inventoryItem->unit_type }}</td>
                                <td class="py-4 text-right text-xs font-mono text-[#64748B]">₹{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="py-4 text-right text-sm font-bold font-mono text-[#172033]">₹{{ number_format($item->total_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Totals Summary --}}
                    <div class="w-full sm:w-80 ml-auto space-y-2.5 text-sm">
                        <div class="flex justify-between text-[#64748B]">
                            <span>Subtotal</span>
                            <span class="font-mono font-semibold text-[#172033]">₹{{ number_format($purchase->subtotal, 2) }}</span>
                        </div>
                        @if($purchase->discount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Discount</span>
                            <span class="font-mono">−₹{{ number_format($purchase->discount, 2) }}</span>
                        </div>
                        @endif
                        @if($purchase->tax > 0)
                        <div class="flex justify-between text-[#64748B]">
                            <span>Tax</span>
                            <span class="font-mono font-semibold text-[#172033]">₹{{ number_format($purchase->tax, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between pt-3 border-t border-[#E5E7EB]">
                            <span class="text-base font-bold text-[#172033]">Grand Total</span>
                            <span class="text-base font-bold font-mono text-[#F5703E]">₹{{ number_format($purchase->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Paid</span>
                            <span class="text-xs font-mono font-bold text-[#29AB6C]">₹{{ number_format($purchase->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Due</span>
                            <span class="text-xs font-mono font-bold text-[#FF4848]">₹{{ number_format($purchase->due_amount, 2) }}</span>
                        </div>
                    </div>

                    @if($purchase->notes)
                    <div class="mt-8 p-4 bg-slate-50 rounded-lg border border-[#E5E7EB] text-xs text-[#64748B] italic">
                        <strong class="text-[#172033]">Notes:</strong> {{ $purchase->notes }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Payment History Card --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden hide-on-print">
                <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-slate-50/75">
                    <h3 class="text-sm font-semibold text-[#172033]">Payment History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E5E7EB] bg-slate-50/50">
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Date</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Method</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Ref #</th>
                                <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider text-right">Amount</th>
                                <th class="py-3 px-4 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @forelse($purchase->payments as $payment)
                            <tr class="hover:bg-[#FFF8F5] transition-colors">
                                <td class="py-3.5 px-4 text-xs font-medium text-[#172033]">{{ $payment->payment_date->format('M j, Y') }}</td>
                                <td class="py-3.5 px-4 text-xs text-[#64748B]">{{ $payment->payment_method }}</td>
                                <td class="py-3.5 px-4 text-xs font-mono text-[#64748B]">{{ $payment->reference_number ?? '—' }}</td>
                                <td class="py-3.5 px-4 text-right text-xs font-mono font-bold text-[#172033]">₹{{ number_format($payment->paid_amount, 2) }}</td>
                                <td class="py-3.5 px-4 text-right">
                                    <form action="{{ route('purchase-payments.destroy', $payment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this payment record?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Delete Payment"
                                                class="w-[28px] h-[28px] rounded-md border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#94A3B8] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-xs text-[#94A3B8]">No payments recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
             3. RIGHT: Workflow & Meta
        ════════════════════════════════════════════════════════════ --}}
        <div class="space-y-6 hide-on-print">

            {{-- Workflow Status Update --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-slate-50/75">
                    <h3 class="text-sm font-semibold text-[#172033]">Purchase Workflow</h3>
                </div>
                <div class="p-5 space-y-4">
                    <form action="{{ route('purchases.status', $purchase) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-[#172033] mb-1.5">Update Status</label>
                                <select name="status" 
                                        class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                                    <option value="draft" {{ $purchase->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="approved" {{ $purchase->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="received" {{ $purchase->status == 'received' ? 'selected' : '' }}>Received (Stock +)</option>
                                    <option value="completed" {{ $purchase->status == 'completed' ? 'selected' : '' }}>Completed (Stock +)</option>
                                    <option value="cancelled" {{ $purchase->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" 
                                    class="btn-brand w-full h-10 rounded-lg text-white text-xs font-semibold transition-colors shadow-sm flex items-center justify-center">
                                Update Workflow
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Order Meta --}}
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-[#E5E7EB] bg-slate-50/75">
                    <h3 class="text-sm font-semibold text-[#172033]">Order Details</h3>
                </div>
                <div class="p-5 space-y-3.5">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-[#64748B]">PO Number</span>
                        <span class="text-xs font-mono font-bold text-[#172033]">#{{ $purchase->purchase_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-[#64748B]">Supplier</span>
                        <span class="text-xs font-semibold text-[#172033]">{{ $purchase->supplier->name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-[#64748B]">Created By</span>
                        <span class="text-xs font-semibold text-[#172033]">{{ $purchase->creator->name ?? 'System' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-[#64748B]">Created At</span>
                        <span class="text-xs font-medium text-[#172033]">{{ $purchase->created_at->format('M j, Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-[#E5E7EB]">
                        <span class="text-xs text-[#64748B]">Payment Status</span>
                        @php
                            $paymentBadge = [
                                'unpaid'  => 'bg-red-50 text-[#FF4848] border-red-200',
                                'partial' => 'bg-amber-50 text-[#FF9932] border-amber-200',
                                'paid'    => 'bg-emerald-50 text-[#29AB6C] border-emerald-200',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold font-mono uppercase border {{ $paymentBadge[$purchase->payment_status] ?? 'bg-slate-100 text-[#64748B] border-slate-200' }}">
                            {{ $purchase->payment_status }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

{{-- ════════════════════════════════════════════════════════════
     RECORD PAYMENT MODAL
════════════════════════════════════════════════════════════ --}}
<x-modal name="add-payment" focusable>
    <form action="{{ route('purchase-payments.store', $purchase) }}" method="POST" class="p-6 space-y-5">
        @csrf
        
        <div class="border-b border-[#E5E7EB] pb-3">
            <h2 class="text-base font-semibold text-[#172033]">Record Supplier Payment</h2>
            <p class="text-xs text-[#64748B] mt-0.5">Log a payment towards purchase order #{{ $purchase->purchase_number }}</p>
        </div>

        {{-- Balance Due Banner --}}
        <div class="p-3 bg-orange-50 border border-orange-200 rounded-lg flex justify-between items-center">
            <span class="text-xs text-[#64748B]">Remaining Balance</span>
            <span class="text-sm font-bold font-mono text-[#F5703E]">₹{{ number_format($purchase->due_amount, 2) }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Paid Amount <span class="text-[#FF4848]">*</span></label>
                <input type="number" name="paid_amount" value="{{ $purchase->due_amount }}" step="0.01" max="{{ $purchase->due_amount }}" required 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Payment Date <span class="text-[#FF4848]">*</span></label>
                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Payment Method <span class="text-[#FF4848]">*</span></label>
                <select name="payment_method" required 
                        class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                    <option value="Cash">Cash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#172033]">Reference Number</label>
                <input type="text" name="reference_number" placeholder="TXN-XXXXXX" 
                       class="mt-1 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-[#172033]">Notes</label>
                <textarea name="notes" rows="2" 
                          class="mt-1 w-full p-3 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"></textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-[#E5E7EB] flex justify-end gap-2.5">
            <button type="button" x-on:click="$dispatch('close')" 
                    class="h-10 px-4 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-medium text-[#172033] transition-colors">
                Cancel
            </button>
            <button type="submit" 
                    class="h-10 px-5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors shadow-sm">
                Confirm Payment
            </button>
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
