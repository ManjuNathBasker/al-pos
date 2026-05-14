@extends('layouts.app')

@section('content')
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ __('Profile') }}</h2>
            <p class="mt-1 text-sm text-slate-500">Manage your account settings and security.</p>
        </div>
    </div>

    <div class="space-y-6">
        <div class="p-4 sm:p-8 bg-white shadow-sm border border-slate-200 rounded-xl">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow-sm border border-slate-200 rounded-xl">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow-sm border border-slate-200 rounded-xl">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
