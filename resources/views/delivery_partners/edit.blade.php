@extends('layouts.app')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('delivery-partners.index') }}" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Edit Delivery Partner</h2>
            <p class="mt-1 text-sm text-slate-500">Update details for {{ $deliveryPartner->name }}</p>
        </div>
    </div>
</div>

<form action="{{ route('delivery-partners.update', $deliveryPartner) }}" method="POST" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 space-y-5">
            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Delivery Partner Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $deliveryPartner->name) }}"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none transition-all @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Commission Value --}}
            <div>
                <label for="commission_percentage" class="block text-sm font-semibold text-slate-700 mb-1.5">Commission Percentage (%) <span class="text-red-500">*</span></label>
                <input type="number" name="commission_percentage" id="commission_percentage" value="{{ old('commission_percentage', $deliveryPartner->commission_percentage) }}"
                       step="0.0001" min="0" max="100"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-400 outline-none font-mono @error('commission_percentage') border-red-400 @enderror">
                @error('commission_percentage')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-xs text-slate-400">Commission charged by the delivery partner (e.g. 20 for 20%).</p>
            </div>

            {{-- Status --}}
            <div class="flex items-center gap-3">
                <input type="hidden" name="status" value="0">
                <input type="checkbox" name="status" id="status" value="1" {{ old('status', $deliveryPartner->status) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                <label for="status" class="text-sm font-semibold text-slate-700">Active (visible in POS)</label>
            </div>
        </div>

        <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('delivery-partners.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                Save Changes
            </button>
        </div>
    </div>
</form>
@endsection
