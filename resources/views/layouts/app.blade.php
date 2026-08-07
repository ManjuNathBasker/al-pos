<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS Admin') }}</title>

    {{-- Vite compiled CSS + JS (includes Alpine.js from app.js) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        body { background-color: #f8fafc; }

        /* Sidebar scrollbar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        /* Nav active state (also defined in app.css @layer) */
        .nav-active {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #4338ca;
        }
        .nav-active svg { color: #4f46e5; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden" x-data="{ sidebarOpen: false }">

    {{-- ============================================================
     SIDEBAR
    ============================================================ --}}
    <aside class="w-64 bg-white border-r border-slate-100 flex-col hidden md:flex flex-shrink-0" style="box-shadow: 1px 0 0 0 #f1f5f9;">

        {{-- Brand --}}
        <div class="h-16 flex items-center px-5 border-b border-slate-100 gap-3 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="flex flex-col leading-none">
                <span class="font-bold text-slate-900 text-sm">{{ config('app.name', 'POS Admin') }}</span>
                <span class="text-[10px] text-slate-400 font-medium mt-0.5">Management Console</span>
            </div>
        </div>

        {{-- Navigation --}}
        @include('layouts.navigation')

        {{-- Sidebar Footer: user info --}}
        <div class="border-t border-slate-100 px-4 py-4 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ============================================================
     MAIN CONTENT
    ============================================================ --}}
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top Header --}}
        <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 flex-shrink-0">

            {{-- Mobile brand --}}
            <div class="flex items-center gap-3 md:hidden">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="font-bold text-slate-900 text-sm">{{ config('app.name', 'POS Admin') }}</span>
            </div>

            {{-- Right controls --}}
            <div class="flex items-center ml-auto gap-3">

                {{-- Company Switcher --}}
                @php
                    $userCompanies = $userCompanies ?? collect();
                    $currentCompany = $currentCompany ?? \App\Models\Company::find(session('company_id'));
                    $initials = $currentCompany ? strtoupper(substr($currentCompany->name, 0, 1)) : 'C';
                @endphp

                <div x-data="{ open: false }" class="relative z-50">
                    <button @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-50 transition-colors focus:outline-none border border-transparent hover:border-slate-200">
                        <div class="w-6 h-6 rounded-md bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            {{ $initials }}
                        </div>
                        <div class="hidden sm:flex flex-col items-start leading-none gap-0.5">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Store</span>
                            <span class="text-xs font-semibold text-slate-700">{{ $currentCompany->name ?? 'Select Store' }}</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden py-1"
                         style="display: none;">
                        <div class="px-4 py-2.5 border-b border-slate-50">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Switch Store</p>
                        </div>
                        <div class="max-h-60 overflow-y-auto py-1">
                            @forelse($userCompanies as $company)
                                <form method="POST" action="{{ route('companies.switch', $company->id) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm flex items-center gap-3 hover:bg-slate-50 transition-colors {{ $currentCompany && $currentCompany->id === $company->id ? 'bg-indigo-50/60' : '' }}">
                                        <div class="w-7 h-7 rounded-lg {{ $currentCompany && $currentCompany->id === $company->id ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($company->name, 0, 1)) }}
                                        </div>
                                        <span class="font-semibold {{ $currentCompany && $currentCompany->id === $company->id ? 'text-indigo-700' : 'text-slate-700' }} flex-1 truncate">{{ $company->name }}</span>
                                        @if($currentCompany && $currentCompany->id === $company->id)
                                            <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                            @empty
                                <div class="px-4 py-4 text-sm text-slate-400 text-center">No other stores available.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="hidden sm:block w-px h-6 bg-slate-200"></div>
                <span class="hidden sm:block text-sm font-semibold text-slate-600">{{ auth()->user()->name ?? 'Admin' }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        {{-- Main Content Area --}}
        <div class="flex-1 overflow-y-auto bg-slate-50 p-5 sm:p-6 lg:p-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="mb-6 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800">
                    <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold flex-1">{{ session('success') }}</p>
                    <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="mb-6 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800">
                    <div class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold flex-1">{{ session('error') }}</p>
                    <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
