@extends('layouts.app')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
        <a href="{{ route('roles.index') }}" class="hover:text-indigo-600">Roles</a>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-slate-900 font-medium">Edit Role</span>
    </div>
    <h2 class="text-2xl font-bold text-slate-800">Edit Role: {{ $role->name }}</h2>
</div>

<form action="{{ route('roles.update', $role) }}" method="POST">
    @csrf
    @method('PATCH')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Role Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                               class="block w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"
                               placeholder="e.g. Manager, Cashier">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('roles.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Update Role
                </button>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Module-wise Permissions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-8">
                        @foreach($permissionsByModule as $module => $permissions)
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 mb-3 capitalize text-indigo-600">{{ $module }}</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @foreach($permissions as $permission)
                                <label class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                               {{ in_array($permission, $rolePermissions) ? 'checked' : '' }}
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded cursor-pointer">
                                    </div>
                                    <div class="ml-2 text-sm">
                                        <span class="font-medium text-slate-700 capitalize">
                                            @php
                                                $displayName = str_replace($module, '', $permission);
                                                $displayName = str_replace(strtolower($module), '', $displayName);
                                                $displayName = trim($displayName);
                                            @endphp
                                            {{ $displayName ?: $module }}
                                        </span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @if(!$loop->last)
                        <hr class="border-slate-100">
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
