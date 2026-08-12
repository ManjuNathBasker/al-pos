@extends('layouts.app')

@section('content')
<div x-data="{
    activeSection: 'all',
    statusFilter: 'all',
    isModalOpen: false,
    isSaving: false,
    modalData: {
        id: null,
        tableName: '',
        status: 'available',
        customer_name: '',
        customer_phone: ''
    },
    openStatusModal(id, tableName, status, name, phone) {
        this.modalData = {
            id: id,
            tableName: tableName || ('#' + id),
            status: status || 'available',
            customer_name: name || '',
            customer_phone: phone || ''
        };
        this.isModalOpen = true;
    },
    closeModal() {
        this.isModalOpen = false;
    },
    async saveStatus() {
        this.isSaving = true;
        try {
            const res = await fetch('/tables/' + this.modalData.id + '/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: this.modalData.status,
                    customer_name: this.modalData.customer_name,
                    customer_phone: this.modalData.customer_phone
                })
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error updating table status');
            }
        } catch (e) {
            alert('Network error updating status');
        } finally {
            this.isSaving = false;
        }
    }
}" class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════
         TOP HEADER BAR
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white px-7 py-6 rounded-3xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-md shadow-brand-500/25 flex-shrink-0"
                 style="background-color: #F5703E;">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Floor & Table Map</h1>
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        Live Monitor
                    </span>
                </div>
                <p class="text-xs text-gray-500 font-medium mt-1">Real-time table occupancy, seating layout, and dining status.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('tables.index') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 shadow-xs transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Table Settings</span>
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         FLOOR TABS & STATUS FILTER TOOLBAR (CLEAN 2-ROW LAYOUT)
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-5 rounded-3xl border border-gray-200/80 shadow-xs space-y-4">
        {{-- Row 1: Floor Section Tabs --}}
        <div class="flex items-center justify-between gap-4 pb-3 border-b border-gray-100">
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

            <div class="hidden sm:flex items-center gap-2 text-xs font-bold text-gray-400 pr-2">
                <span>Total Capacity: <strong class="text-gray-800">{{ $sections->sum(fn($s) => $s->tables->sum('capacity')) }} Seats</strong></span>
            </div>
        </div>

        {{-- Row 2: Real-time Status Filter Pills --}}
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
         FLOOR ZONES & REALISTIC ARCHITECTURAL TABLE CANVAS
    ════════════════════════════════════════════════════════════ --}}
    <div class="space-y-8">
        @forelse($sections as $section)
            <div x-show="activeSection === 'all' || activeSection == '{{ $section->id }}'"
                 class="bg-white rounded-3xl border border-gray-200/80 p-7 shadow-xs floor-canvas">

                {{-- Floor Section Header (Clean, no emoji/icon) --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 mb-8 border-b border-gray-100 gap-3">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 tracking-tight">{{ $section->name }} Floor</h2>
                        <p class="text-xs font-semibold text-gray-400 mt-0.5">
                            {{ $section->tables->count() }} Tables · {{ $section->tables->sum('capacity') }} Total Seats
                        </p>
                    </div>

                    {{-- Floor Status Ratio Pills with generous padding --}}
                    <div class="flex items-center gap-2.5 text-xs font-bold">
                        <span class="px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                            {{ $section->tables->where('status', 'available')->count() }} Free
                        </span>
                        <span class="px-3.5 py-1.5 rounded-full bg-orange-50 text-brand-700 border border-brand-200 text-xs font-bold">
                            {{ $section->tables->where('status', 'occupied')->count() }} Seated
                        </span>
                        <span class="px-3.5 py-1.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold">
                            {{ $section->tables->where('status', 'reserved')->count() }} Booked
                        </span>
                    </div>
                </div>

                {{-- Tables Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-y-16 gap-x-12 justify-items-center py-6">
                    @foreach($section->tables as $table)
                        @php
                            $isOccupied = $table->status === 'occupied';
                            $isReserved = $table->status === 'reserved';
                            $isCleaning = $table->status === 'cleaning';
                            $isAvailable = $table->status === 'available';

                            $capacity = (int) $table->capacity;
                            if ($capacity <= 2) {
                                $shapeClass = 'shape-round-2';
                                $shapeName = '2-SEATER ROUND';
                            } elseif ($capacity == 3) {
                                $shapeClass = 'shape-round-3';
                                $shapeName = '3-SEATER ROUND';
                            } elseif ($capacity == 4) {
                                $shapeClass = 'shape-square-4';
                                $shapeName = '4-SEATER SQUARE';
                            } else {
                                $shapeClass = 'shape-rect-6';
                                $shapeName = $capacity . '-SEATER RECTANGLE';
                            }
                        @endphp

                        <div x-show="statusFilter === 'all' || statusFilter === '{{ $table->status }}'"
                             class="table-unit group relative flex items-center justify-center">

                            {{-- Physical Tabletop Core --}}
                            <div class="tabletop {{ $shapeClass }} status-{{ $table->status }}">

                                {{-- ── CHAIR NODES (Positioned SNUG against Tabletop) ── --}}
                                @if($capacity <= 2)
                                    {{-- 2 Chairs (Top & Bottom) --}}
                                    <div class="chair-node chair-pos-top status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-pos-bottom status-chair-{{ $table->status }}"></div>

                                @elseif($capacity == 3)
                                    {{-- 3 Chairs (1 Top, 1 Left, 1 Right) --}}
                                    <div class="chair-node chair-pos-top status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-pos-left status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-pos-right status-chair-{{ $table->status }}"></div>

                                @elseif($capacity == 4)
                                    {{-- 4 Chairs (Top, Bottom, Left, Right) --}}
                                    <div class="chair-node chair-pos-top status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-pos-bottom status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-pos-left status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-pos-right status-chair-{{ $table->status }}"></div>

                                @elseif($capacity == 5)
                                    {{-- 5 Chairs (3 Top, 2 Bottom) --}}
                                    <div class="chair-node chair-top-1 status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-top-2 status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-top-3 status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-bottom-left status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-bottom-right status-chair-{{ $table->status }}"></div>

                                @else
                                    {{-- 6 Chairs (3 Top, 3 Bottom) --}}
                                    <div class="chair-node chair-top-1 status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-top-2 status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-top-3 status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-bottom-1 status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-bottom-2 status-chair-{{ $table->status }}"></div>
                                    <div class="chair-node chair-bottom-3 status-chair-{{ $table->status }}"></div>
                                @endif

                                {{-- ── TABLETOP CARD INTERIOR ── --}}
                                <div class="tabletop-inner relative z-10 w-full h-full flex flex-col justify-between p-4 overflow-hidden rounded-[inherit]">

                                    {{-- Row 1: Status Pill & Capacity --}}
                                    <div class="flex items-center justify-between w-full">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider status-pill-{{ $table->status }}">
                                            <span class="w-1.5 h-1.5 rounded-full status-dot-{{ $table->status }}"></span>
                                            {{ ucfirst($table->status) }}
                                        </span>

                                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-500 flex items-center gap-1 bg-white/90 px-2 py-0.5 rounded-md border border-gray-100 shadow-2xs">
                                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            {{ $table->capacity }}
                                        </span>
                                    </div>

                                    {{-- Row 2: Table Name & Subtitle --}}
                                    <div class="text-center my-auto py-1">
                                        <h3 class="text-2xl font-black tracking-tight text-gray-900 leading-tight">
                                            {{ $table->name }}
                                        </h3>
                                        <p class="text-[9px] font-extrabold text-gray-400 uppercase tracking-widest mt-0.5">
                                            {{ $shapeName }}
                                        </p>
                                    </div>

                                    {{-- Row 3: Status Details & Touch Action Buttons --}}
                                    <div class="w-full text-center pt-2 border-t border-gray-100">
                                        @if($isOccupied && $table->activeOrder)
                                            <div class="flex flex-col items-center mb-2">
                                                <div class="flex items-center gap-1.5 text-xs font-black text-brand-700">
                                                    <span>{{ $table->activeOrder->order_number }}</span>
                                                    <span>·</span>
                                                    <span class="price tabular">₹{{ number_format($table->activeOrder->total_amount, 2) }}</span>
                                                </div>
                                                <span class="text-[9px] font-bold text-brand-600 mt-0.5">
                                                    ⏱ {{ $table->activeOrder->created_at->diffForHumans(null, true) }} seated
                                                </span>
                                            </div>
                                        @elseif($isReserved)
                                            <div class="flex flex-col items-center mb-2">
                                                <span class="text-xs font-black text-blue-700 truncate max-w-[140px]">
                                                    {{ $table->customer_name ?: 'Reserved Guest' }}
                                                </span>
                                                @if($table->customer_phone)
                                                    <span class="text-[9px] font-semibold text-blue-500">{{ $table->customer_phone }}</span>
                                                @endif
                                            </div>
                                        @elseif($isCleaning)
                                            <div class="flex items-center justify-center gap-1 text-[10px] font-bold text-gray-500 mb-2">
                                                <span>🧹 Sanitizing Table</span>
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center gap-1 text-[10px] font-bold text-emerald-600 mb-2">
                                                <span>✨ Ready for Guests</span>
                                            </div>
                                        @endif

                                        {{-- ── DUAL ACTION BUTTONS (Always Clickable) ── --}}
                                        <div class="grid grid-cols-2 gap-1.5">
                                            @if($isAvailable)
                                                <a href="{{ route('waiter.order', $table) }}"
                                                   class="py-1.5 px-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black tracking-wide shadow-xs flex items-center justify-center gap-1 transition-all">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                    <span>Order</span>
                                                </a>
                                            @elseif($isOccupied)
                                                <a href="{{ route('waiter.order', $table) }}"
                                                   class="py-1.5 px-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-[10px] font-black tracking-wide shadow-xs flex items-center justify-center gap-1 transition-all">
                                                    <span>View</span>
                                                </a>
                                            @else
                                                <a href="{{ route('waiter.order', $table) }}"
                                                   class="py-1.5 px-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black tracking-wide shadow-xs flex items-center justify-center gap-1 transition-all">
                                                    <span>Seat</span>
                                                </a>
                                            @endif

                                            <button type="button"
                                                @click.prevent="openStatusModal({{ $table->id }}, '{{ $table->name }}', '{{ $table->status }}', '{{ addslashes($table->customer_name ?? '') }}', '{{ addslashes($table->customer_phone ?? '') }}')"
                                                class="py-1.5 px-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-bold border border-gray-200 flex items-center justify-center gap-1 transition-all">
                                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>Status</span>
                                            </button>
                                        </div>

                                    </div>

                                </div>{{-- /tabletop-inner --}}

                            </div>{{-- /tabletop --}}

                        </div>{{-- /table-unit --}}
                    @endforeach
                </div>

            </div>
        @empty
            <div class="py-20 text-center bg-white rounded-3xl border border-gray-200 p-8 shadow-xs">
                <div class="w-16 h-16 rounded-3xl bg-orange-50 text-brand-500 flex items-center justify-center text-2xl mx-auto mb-3">🍽️</div>
                <h3 class="text-base font-bold text-gray-900">No tables found</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Please add dining tables and floor sections in Settings to view the live floor map.</p>
                <a href="{{ route('tables.index') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-brand-500 text-white text-xs font-bold hover:bg-brand-600 shadow-md shadow-brand-500/20">
                    + Configure Tables
                </a>
            </div>
        @endforelse
    </div>

    {{-- ════════════════════════════════════════════════════════════
         UPDATE TABLE STATUS MODAL
    ════════════════════════════════════════════════════════════ --}}
    <div x-show="isModalOpen"
         class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100"
             @click.away="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900" x-text="'Table ' + modalData.tableName"></h3>
                    <p class="text-xs text-gray-400 font-semibold mt-0.5">Select real-time dining state or guest reservation</p>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition-colors">
                    ✕
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-5">
                {{-- 4 Visual Status Options --}}
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Table Status</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        {{-- Available --}}
                        <button type="button" @click="modalData.status = 'available'"
                            class="flex flex-col items-center justify-center p-3.5 rounded-2xl border-2 transition-all"
                            :class="modalData.status === 'available' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 shadow-md shadow-emerald-500/10' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 mb-1.5"></span>
                            <span class="text-xs font-bold">Available</span>
                            <span class="text-[9px] text-gray-400 font-medium">Ready to seat</span>
                        </button>

                        {{-- Occupied --}}
                        <button type="button" @click="modalData.status = 'occupied'"
                            class="flex flex-col items-center justify-center p-3.5 rounded-2xl border-2 transition-all"
                            :class="modalData.status === 'occupied' ? 'bg-orange-50 border-brand-500 text-brand-900 shadow-md shadow-brand-500/10' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                            <span class="w-3.5 h-3.5 rounded-full bg-brand-500 mb-1.5"></span>
                            <span class="text-xs font-bold">Occupied</span>
                            <span class="text-[9px] text-gray-400 font-medium">Guests seated</span>
                        </button>

                        {{-- Reserved --}}
                        <button type="button" @click="modalData.status = 'reserved'"
                            class="flex flex-col items-center justify-center p-3.5 rounded-2xl border-2 transition-all"
                            :class="modalData.status === 'reserved' ? 'bg-blue-50 border-blue-500 text-blue-800 shadow-md shadow-blue-500/10' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                            <span class="w-3.5 h-3.5 rounded-full bg-blue-500 mb-1.5"></span>
                            <span class="text-xs font-bold">Reserved</span>
                            <span class="text-[9px] text-gray-400 font-medium">Customer booking</span>
                        </button>

                        {{-- Cleaning --}}
                        <button type="button" @click="modalData.status = 'cleaning'"
                            class="flex flex-col items-center justify-center p-3.5 rounded-2xl border-2 transition-all"
                            :class="modalData.status === 'cleaning' ? 'bg-gray-100 border-gray-500 text-gray-900 shadow-md shadow-gray-500/10' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                            <span class="w-3.5 h-3.5 rounded-full bg-gray-400 mb-1.5"></span>
                            <span class="text-xs font-bold">Cleaning</span>
                            <span class="text-[9px] text-gray-400 font-medium">Bussing & setup</span>
                        </button>
                    </div>
                </div>

                {{-- Customer Info (When Reserved) --}}
                <div x-show="modalData.status === 'reserved'" class="space-y-3 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-500 mb-1">Customer Name (Optional)</label>
                        <input type="text" x-model="modalData.customer_name" placeholder="e.g. John Doe"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs font-bold text-gray-900 bg-gray-50 border border-gray-200 focus:border-brand-500 focus:bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-500 mb-1">Phone Number (Optional)</label>
                        <input type="text" x-model="modalData.customer_phone" placeholder="e.g. 555-0199"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs font-bold text-gray-900 bg-gray-50 border border-gray-200 focus:border-brand-500 focus:bg-white outline-none">
                    </div>
                </div>
            </div>

            {{-- Modal Actions --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="button" @click="closeModal()"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="button" @click="saveStatus()" :disabled="isSaving"
                    class="px-6 py-2.5 rounded-xl text-xs font-black text-white rounded-xl shadow-lg shadow-brand-500/25 transition-all flex items-center gap-2"
                    style="background-color: #F5703E;">
                    <span x-show="!isSaving">Save Changes</span>
                    <span x-show="isSaving">Updating...</span>
                </button>
            </div>

        </div>
    </div>

</div>

<style>
    /* ── Floor Canvas Dot Pattern ── */
    .floor-canvas {
        background-image: radial-gradient(#CBD5E1 1.2px, transparent 1.2px);
        background-size: 20px 20px;
    }

    /* ── Table Unit ── */
    .table-unit {
        position: relative;
    }

    /* ── Tabletop Cores ── */
    .tabletop {
        position: relative;
        background: #FFFFFF;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease, border-color 0.2s ease;
        box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.06), 0 2px 6px rgba(0, 0, 0, 0.04);
    }
    .table-unit:hover .tabletop {
        transform: translateY(-4px);
    }

    /* ── Table Shapes (Generous internal dimensions) ── */
    /* 1. Round 2-Seater */
    .tabletop.shape-round-2 {
        width: 200px;
        height: 200px;
        border-radius: 9999px;
    }
    /* 2. Round 3-Seater */
    .tabletop.shape-round-3 {
        width: 206px;
        height: 206px;
        border-radius: 9999px;
    }
    /* 3. Square 4-Seater */
    .tabletop.shape-square-4 {
        width: 200px;
        height: 200px;
        border-radius: 28px;
    }
    /* 4. Rectangular 5/6-Seater */
    .tabletop.shape-rect-6 {
        width: 250px;
        height: 180px;
        border-radius: 24px;
    }

    /* ── Chair Nodes (Anchored Snugly to Tabletop Boundaries) ── */
    .chair-node {
        position: absolute;
        z-index: 5;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease;
    }

    /* Top Chair */
    .chair-pos-top {
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 48px;
        height: 12px;
        border-top-left-radius: 7px;
        border-top-right-radius: 7px;
    }

    /* Bottom Chair */
    .chair-pos-bottom {
        bottom: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 48px;
        height: 12px;
        border-bottom-left-radius: 7px;
        border-bottom-right-radius: 7px;
    }

    /* Left Chair */
    .chair-pos-left {
        left: -12px;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 48px;
        border-top-left-radius: 7px;
        border-bottom-left-radius: 7px;
    }

    /* Right Chair */
    .chair-pos-right {
        right: -12px;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 48px;
        border-top-right-radius: 7px;
        border-bottom-right-radius: 7px;
    }

    /* Rectangular Multi-Chair Top/Bottom Rows */
    .chair-top-1 {
        top: -12px;
        left: 20%;
        transform: translateX(-50%);
        width: 42px;
        height: 12px;
        border-top-left-radius: 7px;
        border-top-right-radius: 7px;
    }
    .chair-top-2 {
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 42px;
        height: 12px;
        border-top-left-radius: 7px;
        border-top-right-radius: 7px;
    }
    .chair-top-3 {
        top: -12px;
        left: 80%;
        transform: translateX(-50%);
        width: 42px;
        height: 12px;
        border-top-left-radius: 7px;
        border-top-right-radius: 7px;
    }
    .chair-bottom-1 {
        bottom: -12px;
        left: 20%;
        transform: translateX(-50%);
        width: 42px;
        height: 12px;
        border-bottom-left-radius: 7px;
        border-bottom-right-radius: 7px;
    }
    .chair-bottom-2 {
        bottom: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 42px;
        height: 12px;
        border-bottom-left-radius: 7px;
        border-bottom-right-radius: 7px;
    }
    .chair-bottom-3 {
        bottom: -12px;
        left: 80%;
        transform: translateX(-50%);
        width: 42px;
        height: 12px;
        border-bottom-left-radius: 7px;
        border-bottom-right-radius: 7px;
    }
    .chair-bottom-left {
        bottom: -12px;
        left: 30%;
        transform: translateX(-50%);
        width: 44px;
        height: 12px;
        border-bottom-left-radius: 7px;
        border-bottom-right-radius: 7px;
    }
    .chair-bottom-right {
        bottom: -12px;
        left: 70%;
        transform: translateX(-50%);
        width: 44px;
        height: 12px;
        border-bottom-left-radius: 7px;
        border-bottom-right-radius: 7px;
    }

    /* ── Status Theme Colors ── */
    /* 1. GREEN = Available */
    .status-available {
        border: 2px solid #10B981;
    }
    .table-unit:hover .status-available {
        border-color: #059669;
        box-shadow: 0 12px 28px -4px rgba(16, 185, 129, 0.25);
    }
    .status-chair-available {
        background: #D1FAE5;
        border: 1.5px solid #10B981;
    }
    .status-pill-available {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }
    .status-dot-available {
        background: #10B981;
    }

    /* 2. ORANGE = Occupied */
    .status-occupied {
        border: 2.5px solid #F5703E;
        background: #FFFBF8;
        box-shadow: 0 6px 20px -2px rgba(245, 112, 62, 0.25);
    }
    .table-unit:hover .status-occupied {
        box-shadow: 0 14px 32px -4px rgba(245, 112, 62, 0.35);
    }
    .status-chair-occupied {
        background: #FFEDD5;
        border: 1.5px solid #F5703E;
    }
    .status-pill-occupied {
        background: #FFF7ED;
        color: #C2410C;
        border: 1px solid #FDBA74;
    }
    .status-dot-occupied {
        background: #F5703E;
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }

    /* 3. BLUE = Reserved */
    .status-reserved {
        border: 2px solid #3B82F6;
        background: #F8FAFC;
    }
    .table-unit:hover .status-reserved {
        box-shadow: 0 12px 28px -4px rgba(59, 130, 246, 0.25);
    }
    .status-chair-reserved {
        background: #DBEAFE;
        border: 1.5px solid #3B82F6;
    }
    .status-pill-reserved {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }
    .status-dot-reserved {
        background: #3B82F6;
    }

    /* 4. GRAY = Cleaning */
    .status-cleaning {
        border: 2px solid #94A3B8;
        background: #F8FAFC;
    }
    .status-chair-cleaning {
        background: #E2E8F0;
        border: 1.5px solid #94A3B8;
    }
    .status-pill-cleaning {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
    }
    .status-dot-cleaning {
        background: #94A3B8;
    }
</style>
@endsection
