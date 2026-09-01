@extends('layouts.app')

@section('content')
<div x-data="{
    activeSection: 'all',
    statusFilter: 'all',
    search: '',
    matches(tableStatus, tableName) {
        const matchesStatus = (this.statusFilter === 'all' || this.statusFilter === tableStatus);
        const matchesSearch = (!this.search || tableName.toLowerCase().includes(this.search.toLowerCase()));
        return matchesStatus && matchesSearch;
    },
    async completeTable(tableId) {
        if (!confirm('Mark order as COMPLETED and free this table?')) return;
        try {
            const res = await fetch('/waiter/order/' + tableId + '/complete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to complete order');
            }
        } catch (e) {
            alert('Error completing order');
        }
    }
}" x-cloak class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         TOP HEADER BAR
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white px-7 py-6 rounded-3xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-md shadow-brand-500/25 flex-shrink-0"
                 style="background-color: #F5703E;">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Waiter Service Dashboard</h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-orange-50 text-brand-700 border border-brand-200">
                        Order Station
                    </span>
                </div>
                <p class="text-xs text-gray-500 font-medium mt-1">Select any dining table to start a new order, add items, or manage kitchen status.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('tables.map') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 shadow-xs transition-all">
                <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>Live Floor Map</span>
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         FLOOR TABS, STATUS FILTER BUTTONS & SEARCH TOOLBAR (CLEAN 2-ROW)
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-5 rounded-3xl border border-gray-200/80 shadow-xs space-y-4">
        {{-- Row 1: Floor Tabs & Search Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2 p-1.5 bg-gray-100/90 rounded-2xl border border-gray-200/60 overflow-x-auto">
                <button type="button" @click="activeSection = 'all'"
                    class="px-5 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2.5 whitespace-nowrap"
                    :class="activeSection === 'all' ? 'bg-white text-gray-900 shadow-sm border border-gray-200' : 'text-gray-500 hover:text-gray-900'">
                    <span>All Floors</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black"
                        :class="activeSection === 'all' ? 'bg-brand-50 text-brand-600' : 'bg-gray-200 text-gray-600'">
                        {{ $sections->sum(fn($s) => $s->tables->count()) }}
                    </span>
                </button>

                @foreach($sections as $sec)
                    <button type="button" @click="activeSection = '{{ $sec->id }}'"
                        class="px-5 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2.5 whitespace-nowrap"
                        :class="activeSection === '{{ $sec->id }}' ? 'bg-white text-gray-900 shadow-sm border border-gray-200' : 'text-gray-500 hover:text-gray-900'">
                        <span>{{ $sec->name }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black"
                            :class="activeSection === '{{ $sec->id }}' ? 'bg-brand-50 text-brand-600' : 'bg-gray-200 text-gray-600'">
                            {{ $sec->tables->count() }}
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Table Search input --}}
            <div class="relative w-full sm:w-64 flex-shrink-0">
                <input type="text" x-model="search" placeholder="Search table name..."
                       class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold text-gray-800 placeholder-gray-400 focus:outline-none focus:border-brand-500 focus:bg-white shadow-2xs transition-all">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        {{-- Row 2: Status Filter Buttons with Generous Padding --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- All Tables --}}
            <button type="button" @click="statusFilter = 'all'"
                class="flex items-center gap-2.5 px-4.5 py-2.5 rounded-2xl text-xs font-black border transition-all shadow-2xs"
                :style="statusFilter === 'all' ? 'background-color: #0F172A; color: #FFFFFF; border-color: #0F172A;' : 'background-color: #F8FAFC; color: #475569; border-color: #E2E8F0;'">
                <span>All Status</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black"
                    :style="statusFilter === 'all' ? 'background-color: #334155; color: #FFFFFF;' : 'background-color: #E2E8F0; color: #334155;'">
                    {{ $sections->sum(fn($s) => $s->tables->count()) }}
                </span>
            </button>

            {{-- Available --}}
            <button type="button" @click="statusFilter = (statusFilter === 'available' ? 'all' : 'available')"
                class="flex items-center gap-2.5 px-4.5 py-2.5 rounded-2xl text-xs font-bold border transition-all shadow-2xs"
                :class="statusFilter === 'available' ? 'bg-emerald-600 text-white border-emerald-700 shadow-sm' : 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100'">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" :class="statusFilter === 'available' ? 'bg-white' : ''"></span>
                <span>Available</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black"
                    :class="statusFilter === 'available' ? 'bg-emerald-700 text-white' : 'bg-emerald-200 text-emerald-900'">
                    {{ $sections->sum(fn($s) => $s->tables->where('status', 'available')->count()) }}
                </span>
            </button>

            {{-- Occupied --}}
            <button type="button" @click="statusFilter = (statusFilter === 'occupied' ? 'all' : 'occupied')"
                class="flex items-center gap-2.5 px-4.5 py-2.5 rounded-2xl text-xs font-bold border transition-all shadow-2xs"
                :class="statusFilter === 'occupied' ? 'bg-brand-500 text-white border-brand-600 shadow-sm' : 'bg-orange-50 text-brand-900 border-brand-200 hover:bg-orange-100'">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-500" :class="statusFilter === 'occupied' ? 'bg-white' : ''"></span>
                <span>Occupied</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black"
                    :class="statusFilter === 'occupied' ? 'bg-brand-700 text-white' : 'bg-orange-200 text-brand-950'">
                    {{ $sections->sum(fn($s) => $s->tables->where('status', 'occupied')->count()) }}
                </span>
            </button>

            {{-- Reserved --}}
            <button type="button" @click="statusFilter = (statusFilter === 'reserved' ? 'all' : 'reserved')"
                class="flex items-center gap-2.5 px-4.5 py-2.5 rounded-2xl text-xs font-bold border transition-all shadow-2xs"
                :class="statusFilter === 'reserved' ? 'bg-blue-600 text-white border-blue-700 shadow-sm' : 'bg-blue-50 text-blue-800 border-blue-200 hover:bg-blue-100'">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500" :class="statusFilter === 'reserved' ? 'bg-white' : ''"></span>
                <span>Reserved</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black"
                    :class="statusFilter === 'reserved' ? 'bg-blue-800 text-white' : 'bg-blue-200 text-blue-900'">
                    {{ $sections->sum(fn($s) => $s->tables->where('status', 'reserved')->count()) }}
                </span>
            </button>

            {{-- Cleaning --}}
            <button type="button" @click="statusFilter = (statusFilter === 'cleaning' ? 'all' : 'cleaning')"
                class="flex items-center gap-2.5 px-4.5 py-2.5 rounded-2xl text-xs font-bold border transition-all shadow-2xs"
                :class="statusFilter === 'cleaning' ? 'bg-gray-600 text-white border-gray-700 shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-400" :class="statusFilter === 'cleaning' ? 'bg-white' : ''"></span>
                <span>Cleaning</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black"
                    :class="statusFilter === 'cleaning' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-800'">
                    {{ $sections->sum(fn($s) => $s->tables->where('status', 'cleaning')->count()) }}
                </span>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         TABLE CARDS BY SECTION
    ════════════════════════════════════════════════════════════ --}}
    <div class="space-y-8">
        @foreach($sections as $section)
            <div x-show="activeSection === 'all' || activeSection == '{{ $section->id }}'" class="space-y-4">
                {{-- Floor Heading (Clean, no icons) --}}
                <div class="flex items-center justify-between pb-2 border-b border-gray-200/60">
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">
                        {{ $section->name }} Floor
                    </h3>
                    <span class="text-xs font-semibold text-gray-400">{{ $section->tables->count() }} Tables Available</span>
                </div>

                {{-- Table Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    @foreach($section->tables as $table)
                        @php
                            $isAvailable = $table->status === 'available';
                            $isOccupied = $table->status === 'occupied';
                            $isReserved = $table->status === 'reserved';
                            $isCleaning = $table->status === 'cleaning';
                        @endphp
                        <a href="{{ route('waiter.order', $table) }}"
                           x-show="matches('{{ $table->status }}', '{{ $table->name }}')"
                           class="group relative bg-white rounded-3xl p-4 border-2 transition-all duration-200 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1"
                           style="min-height: 165px;"
                           :class="{
                               'border-emerald-400 bg-emerald-50/20 hover:border-emerald-500': '{{ $table->status }}' === 'available',
                               'border-brand-400 bg-orange-50/20 hover:border-brand-500': '{{ $table->status }}' === 'occupied',
                               'border-blue-400 bg-blue-50/20 hover:border-blue-500': '{{ $table->status }}' === 'reserved',
                               'border-gray-300 bg-gray-50/40 hover:border-gray-400': '{{ $table->status }}' === 'cleaning'
                           }">

                            {{-- Card Header: Status & Capacity --}}
                            <div class="flex items-center justify-between w-full">
                                @if($isAvailable)
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Free
                                    </span>
                                @elseif($isOccupied)
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-orange-100 text-brand-800 border border-brand-300 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span> Seated
                                    </span>
                                @elseif($isReserved)
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-300 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Booked
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-gray-200 text-gray-700 border border-gray-300 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Bus
                                    </span>
                                @endif

                                <span class="text-[10px] font-bold text-gray-400 flex items-center gap-0.5 bg-white/80 px-2 py-0.5 rounded-md border border-gray-100 shadow-2xs">
                                    <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    {{ $table->capacity }}
                                </span>
                            </div>

                            {{-- Center: Table Name --}}
                            <div class="text-center my-auto py-2">
                                <span class="text-2xl font-black text-gray-900 group-hover:scale-105 transition-transform block">
                                    {{ $table->name }}
                                </span>
                                <span class="text-[9px] font-extrabold text-gray-400 uppercase tracking-wider">
                                    {{ $table->capacity }} SEATS
                                </span>
                            </div>

                            {{-- Bottom Action Strip --}}
                            <div class="pt-2 border-t border-gray-100 text-center">
                                @if($isAvailable)
                                    <span class="text-[11px] font-black text-emerald-600 group-hover:underline flex items-center justify-center gap-1">
                                        <span>+ Take Order</span>
                                    </span>
                                @elseif($isOccupied)
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-[11px] font-black text-brand-600 group-hover:underline">
                                            Order →
                                        </span>
                                        <button type="button" @click.prevent.stop="completeTable('{{ $table->id }}')" class="px-2 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-[10px] font-black border border-emerald-300 transition-colors shadow-2xs">
                                            ✓ Free Table
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[11px] font-bold text-gray-500 flex items-center justify-center gap-1">
                                        <span>Manage Table</span>
                                    </span>
                                @endif
                            </div>

                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
