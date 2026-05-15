@extends('layouts.app')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('companies.index') }}" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Module Settings</h2>
            <p class="mt-1 text-sm text-slate-500">Enable or disable features for <span class="font-semibold text-indigo-600">{{ $company->name }}</span>.</p>
        </div>
    </div>
</div>

<form action="{{ route('companies.modules.update', $company) }}" method="POST">
    @csrf
    @method('PUT')

    @php
        $groupedModules = collect($availableModules)->groupBy('category');
    @endphp

    @foreach($groupedModules as $category => $modules)
        <div class="mb-8">
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                {{ $category }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($modules as $key => $details)
                    <div class="bg-white rounded-2xl shadow-sm border {{ in_array($company->business_type, $details['defaults']) ? 'border-brand-200 ring-1 ring-brand-100' : 'border-slate-200' }} overflow-hidden hover:shadow-md transition-shadow relative">
                        @if(in_array($company->business_type, $details['defaults']))
                            <div class="absolute top-0 right-0">
                                <span class="bg-brand-500 text-white text-[9px] font-black uppercase px-2 py-1 rounded-bl-xl tracking-tighter">Recommended</span>
                            </div>
                        @endif
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-800">{{ $details['name'] }}</h3>
                                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ $details['description'] }}</p>
                                </div>
                                <div class="ml-4">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="modules[{{ $key }}]" value="1" class="sr-only peer" {{ ($enabledModules[$key] ?? false) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mr-1">Best for:</span>
                                @foreach($details['defaults'] as $type)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold {{ $company->business_type == $type ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-500' }} capitalize">
                                        {{ str_replace('_', ' ', $type) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="mt-8 flex items-center justify-end gap-4">
        <a href="{{ route('companies.index') }}" class="px-6 py-2.5 text-sm font-medium text-slate-700 hover:text-slate-800">Cancel</a>
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all shadow-lg shadow-indigo-100">
            Save Module Configurations
        </button>
    </div>
</form>
@endsection
