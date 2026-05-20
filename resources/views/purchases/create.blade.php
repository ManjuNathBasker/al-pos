@extends('layouts.app')

@section('content')
<div x-data="purchaseForm()" class="pb-12">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Create Purchase Order</h2>
            <p class="mt-1 text-sm text-slate-500">Create a new purchase order for your inventory items.</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600">
            <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to List
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Supplier & Items --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Supplier Info --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Supplier *</label>
                            <select name="supplier_id" required class="w-full rounded-lg border-slate-200 text-sm">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Date *</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-slate-200 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-800 text-sm">Purchase Items</h3>
                        <button type="button" @click="addItem()" class="text-indigo-600 hover:text-indigo-700 text-sm font-bold flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Item
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Inventory Item</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Unit Cost</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-6 py-4">
                                            <select :name="'items['+index+'][inventory_item_id]'" x-model="item.inventory_item_id" required class="w-full rounded-lg border-slate-200 text-sm">
                                                <option value="">Select Item</option>
                                                @foreach($inventoryItems as $invItem)
                                                <option value="{{ $invItem->id }}">{{ $invItem->name }} ({{ $invItem->unit_type }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" step="0.001" min="0.001" required class="w-full rounded-lg border-slate-200 text-sm" @input="calculateItemTotal(index)">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" :name="'items['+index+'][unit_cost]'" x-model.number="item.unit_cost" step="0.01" min="0" required class="w-full rounded-lg border-slate-200 text-sm" @input="calculateItemTotal(index)">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" :name="'items['+index+'][total_cost]'" x-model.number="item.total_cost" readonly class="w-full rounded-lg border-slate-100 bg-slate-50 text-sm font-semibold">
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button type="button" @click="removeItem(index)" class="text-slate-400 hover:text-red-500 transition-colors">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Notes</label>
                    <textarea name="notes" rows="3" placeholder="Additional information..." class="w-full rounded-lg border-slate-200 text-sm"></textarea>
                </div>
            </div>

            {{-- Right Column: Summary & Payment --}}
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 text-sm mb-4">Financial Summary</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-bold text-slate-900" x-text="'$'+subtotal.toFixed(2)"></span>
                            <input type="hidden" name="subtotal" :value="subtotal">
                        </div>
                        <div class="flex justify-between items-center text-sm text-slate-600">
                            <span>Discount</span>
                            <input type="number" name="discount" x-model.number="discount" step="0.01" class="w-24 rounded-lg border-slate-200 text-sm text-right px-2 py-1">
                        </div>
                        <div class="flex justify-between items-center text-sm text-slate-600">
                            <span>Tax</span>
                            <input type="number" name="tax" x-model.number="tax" step="0.01" class="w-24 rounded-lg border-slate-200 text-sm text-right px-2 py-1">
                        </div>
                        <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-800">Total Amount</span>
                            <span class="text-lg font-black text-indigo-600" x-text="'$'+total.toFixed(2)"></span>
                            <input type="hidden" name="total_amount" :value="total">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 text-sm mb-4">Settings & Payment</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Status</label>
                            <select name="status" class="w-full rounded-lg border-slate-200 text-sm">
                                <option value="draft">Draft (No Stock Change)</option>
                                <option value="approved">Approved (No Stock Change)</option>
                                <option value="received">Received (Increases Stock)</option>
                                <option value="completed">Completed (Increases Stock)</option>
                            </select>
                            <p class="mt-1 text-[10px] text-slate-400 leading-tight">Stock will be automatically updated for 'Received' or 'Completed' status.</p>
                        </div>
                        
                        <div class="pt-4 border-t border-slate-100">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Received (Paid Now)</label>
                            <input type="number" name="paid_amount" step="0.01" class="w-full rounded-lg border-slate-200 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full rounded-lg border-slate-200 text-sm">
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-lg hover:bg-indigo-700 transition-all active:scale-[0.98]">
                    Save Purchase Order
                </button>
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
                item.total_cost = parseFloat(item.total_cost);
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
