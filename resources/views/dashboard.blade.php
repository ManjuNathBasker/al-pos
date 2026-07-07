@extends('layouts.app')

@section('content')
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ __('Dashboard') }}</h2>
            <p class="mt-1 text-sm text-slate-500">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-slate-900">
            <h3 class="font-bold text-lg mb-2">Welcome to Your Dashboard</h3>
            <p class="text-slate-500">{{ __("You're logged in and ready to manage your business.") }}</p>
        </div>

        <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-xl shadow-lg border border-indigo-500 p-6 flex flex-col items-center justify-center text-center hover:shadow-xl transition-all hover:-translate-y-1">
            <h3 class="font-bold text-xl text-white mb-2">Ready to take orders?</h3>
            <p class="text-indigo-100 mb-6 text-sm">Launch your point of sale terminal to start processing sales immediately.</p>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white text-indigo-700 font-bold rounded-lg shadow hover:bg-slate-50 transition-colors w-full sm:w-auto">
                <svg class="w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Open POS Terminal
            </a>
        </div>
    </div>
@endsection
