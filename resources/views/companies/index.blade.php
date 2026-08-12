@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#172033] tracking-tight">Companies</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Manage all companies and branches you have access to.</p>
        </div>
        <a href="{{ route('companies.create') }}" 
           class="btn-brand h-11 px-4 rounded-lg text-white text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Company</span>
        </a>
    </div>

    {{-- Companies Table --}}
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-[#E5E7EB]">
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Company Name</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Email</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Phone</th>
                        <th class="py-3.5 px-4 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($companies as $company)
                    <tr class="hover:bg-[#FFF8F5] transition-colors">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-orange-50 border border-orange-200 text-[#F5703E] flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-[#172033]">{{ $company->name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-semibold uppercase text-[#94A3B8] bg-slate-100 px-1.5 py-0.5 rounded tracking-wide">{{ str_replace('_', ' ', $company->business_type) }}</span>
                                        @if(session('company_id') == $company->id)
                                            <span class="text-[10px] font-semibold text-blue-600 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded">Active Session</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">{{ $company->email ?: '—' }}</td>
                        <td class="py-4 px-4 text-xs text-[#64748B]">{{ $company->phone ?: '—' }}</td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-[#29AB6C] border border-emerald-200">Active</span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                @if(session('company_id') != $company->id)
                                <form action="{{ route('companies.switch', $company) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" title="Switch to this company"
                                            class="h-[34px] px-3 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition-colors">
                                        Switch
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('companies.modules', $company) }}" title="Modules"
                                   class="h-[34px] px-3 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#64748B] text-xs font-semibold flex items-center transition-colors">
                                    Modules
                                </a>
                                <a href="{{ route('companies.edit', $company) }}" title="Edit Company"
                                   class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-orange-50 text-[#64748B] hover:text-[#F5703E] hover:border-orange-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('companies.destroy', $company) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this company and all its data?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Delete Company"
                                            class="w-[34px] h-[34px] rounded-lg border border-[#E5E7EB] bg-white hover:bg-red-50 text-[#64748B] hover:text-[#FF4848] hover:border-red-200 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-xs text-[#94A3B8]">No companies found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
