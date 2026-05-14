@extends('layouts.app')

@section('content')
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ __('Dashboard') }}</h2>
            <p class="mt-1 text-sm text-slate-500">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-slate-900">
        {{ __("You're logged in!") }}
    </div>
@endsection
