@extends('layouts.app')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
        <a href="{{ route('users.index') }}" class="hover:text-indigo-600">Users</a>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-slate-900 font-medium">Edit User</span>
    </div>
    <h2 class="text-2xl font-bold text-slate-800">Edit User: {{ $user->name }}</h2>
</div>

<form action="{{ route('users.update', $user) }}" method="POST">
    @csrf
    @method('PATCH')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <!-- User Details -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4 border-b pb-2">User Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="block w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                               class="block w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password (Leave blank to keep current)</label>
                        <input type="password" name="password" id="password"
                               class="block w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="block w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                    </div>
                </div>
            </div>

            <!-- Company Assignment -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4 border-b pb-2">Assign Companies</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($companies as $company)
                    <label class="relative flex items-start p-3 rounded-lg border border-slate-100 hover:bg-slate-50 cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="companies[]" value="{{ $company->id }}"
                                   {{ in_array($company->id, $userCompanies) ? 'checked' : '' }}
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded cursor-pointer">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-slate-700">{{ $company->name }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('companies') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="lg:col-span-1 space-y-8">
            <!-- Role Assignment -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4 border-b pb-2">Assign Roles</h3>
                <p class="text-xs text-slate-500 mb-4">Roles will be applied to the current company ({{ \App\Models\Company::find(session('company_id'))->name }}).</p>
                <div class="space-y-3">
                    @foreach($roles as $role)
                    <label class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   {{ in_array($role->name, $userRoles) ? 'checked' : '' }}
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded cursor-pointer">
                        </div>
                        <div class="ml-2 text-sm">
                            <span class="font-medium text-slate-700">{{ $role->name }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Update User
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
