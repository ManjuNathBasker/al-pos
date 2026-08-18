@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="text-center mb-10">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('Dashboard') }}</h2>
        <p class="mt-2 text-base text-slate-500 font-medium">Welcome back, <span class="text-indigo-600">{{ auth()->user()->name }}</span>!</p>
    </div>
    
    <!-- Premium Center-Aligned Quick Access Navbar -->
    <div class="flex flex-wrap justify-center gap-4 mb-12">
        @can('access pos')
        <a href="{{ route('pos.index') }}" class="group relative flex flex-col items-center justify-center w-32 h-32 bg-white/80 backdrop-blur-md border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgb(79,70,229,0.15)] hover:bg-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="p-4 bg-indigo-50 text-indigo-600 rounded-xl mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-700 transition-colors">POS</span>
        </a>
        @endcan

        @can('view orders')
        <a href="{{ route('orders.index') }}" class="group relative flex flex-col items-center justify-center w-32 h-32 bg-white/80 backdrop-blur-md border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgb(16,185,129,0.15)] hover:bg-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="p-4 bg-emerald-50 text-emerald-600 rounded-xl mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <span class="text-sm font-bold text-slate-700 group-hover:text-emerald-700 transition-colors">Orders</span>
        </a>
        @endcan

        @can('view products')
        <a href="{{ route('products.index') }}" class="group relative flex flex-col items-center justify-center w-32 h-32 bg-white/80 backdrop-blur-md border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgb(245,158,11,0.15)] hover:bg-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="p-4 bg-amber-50 text-amber-600 rounded-xl mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <span class="text-sm font-bold text-slate-700 group-hover:text-amber-700 transition-colors">Products</span>
        </a>
        @endcan

        @can('view reports')
        <a href="{{ route('reports.sales') }}" class="group relative flex flex-col items-center justify-center w-32 h-32 bg-white/80 backdrop-blur-md border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgb(236,72,153,0.15)] hover:bg-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-pink-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="p-4 bg-pink-50 text-pink-600 rounded-xl mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <span class="text-sm font-bold text-slate-700 group-hover:text-pink-700 transition-colors">Reports</span>
        </a>
        @endcan
    </div>

    <!-- Analytics Cards (Bento Box Style with Glassmorphism) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Today's Sales -->
        <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl p-8 group hover:bg-white transition-colors duration-500">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-indigo-500/10 blur-2xl group-hover:bg-indigo-500/20 transition-all duration-500"></div>
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-slate-500 font-semibold tracking-wide uppercase text-xs">{{ $isStaffOnly ? 'My Sales Today' : 'Total Sales Today' }}</p>
                    <div class="p-2 bg-indigo-50 text-indigo-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <h3 class="text-4xl font-extrabold text-slate-800 tracking-tight">@currency($todaySales)</h3>
            </div>
        </div>

        <!-- Today's Orders -->
        <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl p-8 group hover:bg-white transition-colors duration-500">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-emerald-500/10 blur-2xl group-hover:bg-emerald-500/20 transition-all duration-500"></div>
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-slate-500 font-semibold tracking-wide uppercase text-xs">{{ $isStaffOnly ? 'My Orders Today' : 'Total Orders Today' }}</p>
                    <div class="p-2 bg-emerald-50 text-emerald-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                </div>
                <h3 class="text-4xl font-extrabold text-slate-800 tracking-tight">{{ number_format($todayOrdersCount) }}</h3>
            </div>
        </div>

        <!-- Monthly Sales -->
        <div class="relative overflow-hidden bg-white/70 backdrop-blur-xl border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl p-8 group hover:bg-white transition-colors duration-500">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-purple-500/10 blur-2xl group-hover:bg-purple-500/20 transition-all duration-500"></div>
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-slate-500 font-semibold tracking-wide uppercase text-xs">{{ $isStaffOnly ? 'My Monthly Sales' : 'Total Monthly Sales' }}</p>
                    <div class="p-2 bg-purple-50 text-purple-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                    </div>
                </div>
                <h3 class="text-4xl font-extrabold text-slate-800 tracking-tight">@currency($monthlySales)</h3>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
        <!-- Chart Section -->
        <div class="lg:col-span-2 bg-white/80 backdrop-blur-md border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl p-8">
            <h3 class="text-xl font-extrabold text-slate-800 mb-6">{{ $isStaffOnly ? 'My Sales History' : 'Revenue Overview' }}</h3>
            <div class="relative h-72 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="bg-white/80 backdrop-blur-md border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-extrabold text-slate-800">{{ $isStaffOnly ? 'My Recent Orders' : 'Recent Orders' }}</h3>
                @can('view orders')
                <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">View All</a>
                @endcan
            </div>
            
            <div class="space-y-4">
                @forelse($recentOrders as $order)
                <div class="group flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 hover:border-indigo-100">
                    <div>
                        <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-700 transition-colors">{{ $order->order_number }}</p>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $order->customer ? $order->customer->name : 'Walk-in Customer' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-extrabold text-slate-800">@currency($order->total_amount, $order)</p>
                        <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                            {{ $order->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 bg-white border border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center">
                    <svg class="h-10 w-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-sm font-medium">No recent orders found</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Data injected from Laravel controller
        const labels = {!! json_encode($chartLabels) !!};
        const data = {!! json_encode($chartData) !!};
        
        // Create an ultra-premium gradient for the chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.25)'); // Indigo 600
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue ({{ currency_symbol() }})',
                    data: data,
                    borderColor: '#4f46e5', // Indigo 600
                    backgroundColor: gradient,
                    borderWidth: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4 // Smooth curves
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 16,
                        titleFont: { size: 13, family: "'DM Sans', sans-serif", weight: 'bold' },
                        bodyFont: { size: 14, family: "'DM Sans', sans-serif", weight: 'bold' },
                        cornerRadius: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return window.formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f8fafc',
                            drawBorder: false,
                        },
                        border: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { family: "'DM Sans', sans-serif", weight: '600' },
                            padding: 10,
                            callback: function(value) {
                                return window.formatCurrency(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        border: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { family: "'DM Sans', sans-serif", weight: '600' },
                            padding: 10
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    });
</script>
@endpush
