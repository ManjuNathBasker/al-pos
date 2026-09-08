@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Users</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Manage team members and their role assignments for this company.</p>
        </div>
        <a href="{{ route('users.create') }}" 
           class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add User</span>
        </a>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Name</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Email</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Roles</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($users as $user)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-orange-100 text-[#F5703E] flex items-center justify-center font-bold text-sm flex-shrink-0 border border-orange-200">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <span class="text-sm font-semibold text-[#172033]">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">{{ $user->email }}</td>
                        <td class="py-4 px-4">
                            @php
                                $userRoles = $user->roles()
                                    ->where(function($q) {
                                        $q->where('roles.team_id', session('company_id'))
                                          ->orWhereNull('roles.team_id');
                                    })->get();
                            @endphp
                            <div class="flex flex-wrap gap-1">
                                @forelse($userRoles as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">{{ $role->name }}</span>
                                @empty
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-[#64748B] border border-slate-200">No Role</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('users.edit', $user) }}" title="Edit User"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Remove this user from the company?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Remove User"
                                            class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-16 text-center">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F5703E] flex items-center justify-center text-xl mx-auto mb-3 border border-orange-100">👤</div>
                            <h3 class="text-sm font-bold text-[#172033]">No users yet</h3>
                            <p class="text-xs text-[#64748B] mt-1">
                                <a href="{{ route('users.create') }}" class="text-[#F5703E] hover:underline font-semibold">Add a user</a> to give team access.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
