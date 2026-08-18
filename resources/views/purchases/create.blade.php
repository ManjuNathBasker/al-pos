@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="purchaseForm()">

    {{-- ════════════════════════════════════════════════════════════
         1. PAGE HEADER
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('purchases.index') }}" title="Back to Purchase Orders"
           class="w-9 h-9 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#64748B] flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">New Purchase Order</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Create a new raw material or ingredient restock order.</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. FORM CONTAINER
    ════════════════════════════════════════════════════════════ --}}
    <form action="{{ route('purchases.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left 2 Columns: Supplier, Date & Item List --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Supplier & Date Selection --}}
                <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-[#172033] mb-4">Supplier & Date</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#172033] mb-1.5">Supplier <span class="text-[#FF4848]">*</span></label>
                            <select name="supplier_id" required 
                                    class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#172033] mb-1.5">Purchase Date <span class="text-[#FF4848]">*</span></label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required 
                                   class="w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                        </div>
                    </div>
                </div>

                {{-- Line Items Table --}}
                <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#E5E7EB] bg-slate-50/75 flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-[#172033]">Ordered Inventory Items</h3>
                        <button type="button" @click="addItem()" 
                                class="h-8 px-3 rounded-lg bg-orange-50 hover:bg-orange-100 text-xs font-semibold text-[#F5703E] border border-orange-200 flex items-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>Add Item</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                                    <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase w-2/5">Inventory Item</th>
                                    <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase w-1/5">Quantity</th>
                                    <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase w-1/5">Unit Cost (@currencySymbol)</th>
                                    <th class="py-3 px-4 text-xs font-semibold text-[#64748B] uppercase text-right">Total (@currencySymbol)</th>
                                    <th class="py-3 px-4 text-right w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB]">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                                        {{-- Item select --}}
                                        <td class="py-3.5 px-4">
                                            <select :name="'items['+index+'][inventory_item_id]'" x-model="item.inventory_item_id" required 
                                                    class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-xs text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]">
                                                <option value="">Select Item</option>
                                                @foreach($inventoryItems as $invItem)
                                                <option value="{{ $invItem->id }}">{{ $invItem->name }} ({{ $invItem->unit_type }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        {{-- Quantity --}}
                                        <td class="py-3.5 px-4">
                                            <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" step="0.001" min="0.001" required 
                                                   class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-xs font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" @input="calculateItemTotal(index)">
                                        </td>
                                        {{-- Unit Cost --}}
                                        <td class="py-3.5 px-4">
                                            <input type="number" :name="'items['+index+'][unit_cost]'" x-model.number="item.unit_cost" step="0.01" min="0" required 
                                                   class="w-full h-10 px-3 bg-white border border-[#E5E7EB] rounded-lg text-xs font-mono text-[#172033] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]" @input="calculateItemTotal(index)">
                                        </td>
                                        {{-- Line Total --}}
                                        <td class="py-3.5 px-4 text-right">
                                            <input type="number" :name="'items['+index+'][total_cost]'" x-model.number="item.total_cost" readonly 
                                                   class="w-full h-10 px-3 bg-slate-50 border border-[#E5E7EB] rounded-lg text-xs font-mono font-bold text-[#172033] text-right">
                                        </td>
                                        {{-- Remove button --}}
                                        <td class="py-3.5 px-4 text-right">
                                            <button type="button" @click="removeItem(index)" 
                                                    class="w-8 h-8 rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6">
                    <label class="block text-xs font-semibold text-[#172033] mb-1.5">Purchase Order Notes</label>
                    <textarea name="notes" rows="3" placeholder="Add delivery instructions, reference invoice numbers..." 
                              class="w-full p-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] placeholder-[#94A3B8] focus:outline-none focus:border-[#F5703E] focus:ring-1 focus:ring-[#F5703E]"></textarea>
                </div>
            </div>

            {{-- Right Column: Financial Summary & Status --}}
            <div class="space-y-6">
                {{-- Financial Summary Card --}}
                <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-[#172033] border-b border-[#E5E7EB] pb-3">Financial Summary</h3>
                    
                    <div class="flex justify-between items-center text-sm text-[#64748B]">
                        <span>Subtotal</span>
                        <span class="font-mono font-bold text-[#172033]" x-text="formatCurrency(subtotal)"></span>
                        <input type="hidden" name="subtotal" :value="subtotal">
                    </div>
                    <div class="flex justify-between items-center text-sm text-[#64748B]">
                        <span>Discount (@currencySymbol)</span>
                        <input type="number" name="discount" x-model.number="discount" step="0.01" 
                               class="w-28 h-9 px-3 bg-white border border-[#E5E7EB] rounded-lg text-xs font-mono text-right text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    </div>
                    <div class="flex justify-between items-center text-sm text-[#64748B] pb-3 border-b border-[#E5E7EB]">
                        <span>Tax (@currencySymbol)</span>
                        <input type="number" name="tax" x-model.number="tax" step="0.01" 
                               class="w-28 h-9 px-3 bg-white border border-[#E5E7EB] rounded-lg text-xs font-mono text-right text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-sm font-semibold text-[#172033]">Total Amount</span>
                        <span class="text-xl font-bold font-mono text-[#F5703E]" x-text="formatCurrency(total)"></span>
                        <input type="hidden" name="total_amount" :value="total">
                    </div>
                </div>

                {{-- Status & Payment Card --}}
                <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-[#172033] border-b border-[#E5E7EB] pb-3">Status & Payment</h3>
                    
                    <div>
                        <label class="block text-xs font-semibold text-[#172033]">Purchase Status</label>
                        <select name="status" 
                                class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                            <option value="draft">Draft (No Stock Change)</option>
                            <option value="approved">Approved (No Stock Change)</option>
                            <option value="received">Received (Increases Stock)</option>
                            <option value="completed">Completed (Increases Stock)</option>
                        </select>
                        <p class="mt-1 text-[11px] text-[#64748B]">Stock increases automatically on Received/Completed.</p>
                    </div>

                    <div class="pt-3 border-t border-[#E5E7EB]">
                        <label class="block text-xs font-semibold text-[#172033]">Paid Amount (@currencySymbol)</label>
                        <input type="number" name="paid_amount" step="0.01" placeholder="0.00" 
                               class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm font-mono text-[#172033] focus:outline-none focus:border-[#F5703E]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#172033]">Payment Method</label>
                        <select name="payment_method" 
                                class="mt-1.5 w-full h-11 px-3.5 bg-white border border-[#E5E7EB] rounded-lg text-sm text-[#172033] focus:outline-none focus:border-[#F5703E]">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('purchases.index') }}" 
                       class="flex-1 h-11 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-sm font-medium text-[#172033] transition-colors flex items-center justify-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="btn-brand flex-1 h-11 rounded-lg text-white text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                        <span>Save PO</span>
                    </button>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
    function purchaseForm() {
        return {
            items: [
                { inventory_item_id: '', quantity: 1, unit_cost: 0, total_cost: 0 }
            ],
            discount: 0,
            tax: 0,
            
            formatCurrency(val) {
                return window.formatCurrency(val);
            },

            addItem() {
                this.items.push({ inventory_item_id: '', quantity: 1, unit_cost: 0, total_cost: 0 });
            },
            
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            
            calculateItemTotal(index) {
                const item = this.items[index];
                item.total_cost = (item.quantity * item.unit_cost).toFixed(2);
                item.total_cost = parseFloat(item.total_cost) || 0;
            },
            
            get subtotal() {
                return this.items.reduce((sum, item) => sum + (parseFloat(item.total_cost) || 0), 0);
            },
            
            get total() {
                return this.subtotal - (parseFloat(this.discount) || 0) + (parseFloat(this.tax) || 0);
            }
        }
    }
</script>
@endsection
