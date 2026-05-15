@extends('layouts.app')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Live Table Map</h2>
        <p class="mt-1 text-sm text-slate-500">Real-time occupancy and status of all dining tables.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3">
        <div class="flex items-center gap-4 mr-4 text-xs font-medium text-slate-500">
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500"></span> Available</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Occupied</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Reserved</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-400"></span> Cleaning</div>
        </div>
        <a href="{{ route('tables.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
            Settings
        </a>
    </div>
</div>

@if(count($nonDineInOrders) > 0)
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <!-- Takeaway Summary -->
    @php $takeaways = $nonDineInOrders->where('service_type', 'takeaway'); @endphp
    @if(count($takeaways) > 0)
    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-amber-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            </div>
            <div>
                <h4 class="text-sm font-black text-amber-900 uppercase tracking-tight">Active Takeaways</h4>
                <p class="text-xs font-bold text-amber-600">{{ count($takeaways) }} Orders in progress</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-lg font-black text-amber-900">₹{{ number_format($takeaways->sum('total_amount'), 2) }}</p>
        </div>
    </div>
    @endif

    <!-- Delivery Summary -->
    @php $deliveries = $nonDineInOrders->where('service_type', 'delivery'); @endphp
    @if(count($deliveries) > 0)
    <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-rose-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
            </div>
            <div>
                <h4 class="text-sm font-black text-rose-900 uppercase tracking-tight">Active Deliveries</h4>
                <p class="text-xs font-bold text-rose-600">{{ count($deliveries) }} Orders in progress</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-lg font-black text-rose-900">₹{{ number_format($deliveries->sum('total_amount'), 2) }}</p>
        </div>
    </div>
    @endif
</div>
@endif

<div class="space-y-12">
    @forelse($sections as $section)
        <section>
            <div class="flex items-center gap-4 mb-6">
                <h3 class="text-lg font-bold text-slate-800">{{ $section->name }}</h3>
                <div class="h-px flex-1 bg-slate-200"></div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($section->tables as $table)
                    @php
                        $statusClasses = [
                            'available' => 'bg-white border-green-200 hover:border-green-400',
                            'occupied' => 'bg-amber-50 border-amber-200 hover:border-amber-400',
                            'reserved' => 'bg-blue-50 border-blue-200 hover:border-blue-400',
                            'cleaning' => 'bg-slate-50 border-slate-200 hover:border-slate-300',
                        ];
                        $statusIconColor = [
                            'available' => 'text-green-500',
                            'occupied' => 'text-amber-500',
                            'reserved' => 'text-blue-500',
                            'cleaning' => 'text-slate-400',
                        ];
                    @endphp
                    <div class="relative group cursor-pointer">
                        <div class="aspect-square rounded-2xl border-2 {{ $statusClasses[$table->status] }} transition-all flex flex-col items-center justify-center p-4 shadow-sm group-hover:shadow-md">
                            <div class="absolute top-3 right-3">
                                <div class="w-2 h-2 rounded-full {{ str_replace('text-', 'bg-', $statusIconColor[$table->status]) }}"></div>
                            </div>
                            
                            <span class="text-2xl font-black text-slate-800 mb-1">{{ $table->name }}</span>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">{{ $table->capacity }} SEATS</span>

                            @if($table->status === 'occupied' && $table->activeOrder)
                                <div class="mt-3 flex flex-col items-center">
                                    <span class="text-[10px] font-bold text-amber-700">{{ $table->activeOrder->order_number }}</span>
                                    <span class="text-[10px] font-medium text-amber-600">₹{{ number_format($table->activeOrder->total_amount, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Hover Overlay Actions -->
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 bg-slate-900/10 rounded-2xl backdrop-blur-[1px]">
                            @if($table->status === 'available')
                                <a href="{{ route('waiter.order', $table) }}" class="bg-indigo-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-lg">NEW ORDER</a>
                            @else
                                <a href="{{ route('waiter.order', $table) }}" class="bg-white text-slate-800 text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-lg border border-slate-200">VIEW ORDER</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="py-20 text-center">
            <p class="text-slate-500">No tables configured. Please go to Settings to add tables and sections.</p>
        </div>
    @endforelse
</div>

<style>
    /* Premium touch: subtle scale on hover */
    .group:hover .aspect-square {
        transform: translateY(-4px);
    }
</style>
@endsection
