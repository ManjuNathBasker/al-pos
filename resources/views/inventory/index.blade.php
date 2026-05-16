@extends('layouts.app')

@section('content')
<div class="h-full flex flex-col" x-data="{ showAddModal: false, showEditModal: false, selectedItem: {} }">
    <!-- Header -->
    <header class="h-16 border-b border-slate-100 flex items-center justify-between px-8 bg-white/50 backdrop-blur-sm sticky top-0 z-10">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Inventory Management</h1>
            <p class="text-xs text-slate-400">Track and manage your ingredients and raw materials</p>
        </div>
        <button @click="showAddModal = true" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Item
        </button>
    </header>

    <main class="flex-1 overflow-y-auto p-8 space-y-8">
        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Items</p>
                <p class="text-2xl font-black text-slate-800">{{ $stats['total_items'] }}</p>
            </div>
            
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Low Stock</p>
                <p class="text-2xl font-black text-slate-800">{{ $stats['low_stock'] }}</p>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Out of Stock</p>
                <p class="text-2xl font-black text-slate-800">{{ $stats['out_of_stock'] }}</p>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Inventory Value</p>
                <p class="text-2xl font-black text-slate-800">₹{{ number_format($stats['total_value'], 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Inventory Table -->
            <div class="xl:col-span-2 bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Ingredient List</h3>
                    <div class="flex items-center gap-2">
                        <input type="text" placeholder="Search..." class="bg-slate-50 border-none rounded-xl text-sm px-4 py-2 focus:ring-2 focus:ring-indigo-100 transition-all w-48">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item Name</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Unit</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Current Stock</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($items as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $item->code ?? 'NO CODE' }}</p>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="px-3 py-1 bg-slate-100 rounded-full text-[10px] font-black text-slate-600 uppercase">{{ $item->unit_type }}</span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <p class="font-black {{ $item->is_low_stock ? 'text-red-500' : 'text-slate-700' }}">{{ $item->current_stock }}</p>
                                    <p class="text-[10px] text-slate-400">Min: {{ $item->minimum_stock }}</p>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    @if($item->current_stock <= 0)
                                        <span class="w-2 h-2 rounded-full bg-red-500 inline-block ring-4 ring-red-100"></span>
                                    @elseif($item->is_low_stock)
                                        <span class="w-2 h-2 rounded-full bg-amber-500 inline-block ring-4 ring-amber-100"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block ring-4 ring-emerald-100"></span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <button @click="selectedItem = {{ json_encode($item) }}; showEditModal = true" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-8 py-6 border-b border-slate-50">
                    <h3 class="font-bold text-slate-800">Recent Logs</h3>
                </div>
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    @foreach($recentTransactions as $tx)
                    <div class="flex items-start gap-4 p-4 rounded-2xl {{ $tx->transaction_type === 'deduction' ? 'bg-red-50/50' : 'bg-emerald-50/50' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $tx->transaction_type === 'deduction' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                @if($tx->transaction_type === 'deduction')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                @endif
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ $tx->inventoryItem->name }}</p>
                            <p class="text-xs text-slate-500">{{ $tx->notes }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-[10px] font-black uppercase {{ $tx->transaction_type === 'deduction' ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ $tx->transaction_type === 'deduction' ? '-' : '+' }}{{ $tx->quantity }} {{ $tx->inventoryItem->unit_type }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $tx->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>

    <!-- Add Modal -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl" @click.away="showAddModal = false">
            <h3 class="text-2xl font-black text-slate-800 mb-6">New Ingredient</h3>
            <form action="{{ route('inventory.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Item Name</label>
                    <input type="text" name="name" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all" placeholder="e.g. Chicken Patty">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Code (SKU)</label>
                        <input type="text" name="code" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all" placeholder="CP-01">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Unit Type</label>
                        <select name="unit_type" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all appearance-none">
                            <option value="kg">kg</option>
                            <option value="gram">gram</option>
                            <option value="liter">liter</option>
                            <option value="ml">ml</option>
                            <option value="piece">piece</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Initial Stock</label>
                        <input type="number" step="0.001" name="current_stock" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all" value="0">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Min. Threshold</label>
                        <input type="number" step="0.001" name="minimum_stock" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all" value="5">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Cost Price</label>
                    <input type="number" step="0.01" name="cost_price" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all" value="0">
                </div>
                <div class="pt-4 flex gap-4">
                    <button type="button" @click="showAddModal = false" class="flex-1 py-4 text-slate-400 font-bold hover:text-slate-600 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-indigo-600 text-white rounded-2xl py-4 font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">SAVE ITEM</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl" @click.away="showEditModal = false">
            <h3 class="text-2xl font-black text-slate-800 mb-6">Edit Ingredient</h3>
            <form :action="'/inventory/' + selectedItem.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Item Name</label>
                    <input type="text" name="name" x-model="selectedItem.name" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Current Stock</label>
                        <input type="number" step="0.001" name="current_stock" x-model="selectedItem.current_stock" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Min. Threshold</label>
                        <input type="number" step="0.001" name="minimum_stock" x-model="selectedItem.minimum_stock" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-4">Cost Price</label>
                    <input type="number" step="0.01" name="cost_price" x-model="selectedItem.cost_price" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-slate-800 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div class="pt-4 flex gap-4">
                    <button type="button" @click="showEditModal = false" class="flex-1 py-4 text-slate-400 font-bold hover:text-slate-600 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-indigo-600 text-white rounded-2xl py-4 font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">UPDATE ITEM</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
