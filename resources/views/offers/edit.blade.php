@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('offers.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">&larr; Back to Offers</a>
    <h2 class="text-2xl font-bold text-slate-800 mt-2">Edit Bank Offer</h2>
    <p class="mt-1 text-sm text-slate-500">Update <strong>{{ $offer->name }}</strong>.</p>
</div>

<form action="{{ route('offers.update', $offer) }}" method="POST" class="space-y-6 max-w-4xl">
    @csrf @method('PUT')
    @include('offers._form', ['offer' => $offer])
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-all shadow-sm">Update Offer</button>
        <a href="{{ route('offers.index') }}" class="px-6 py-3 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl hover:bg-slate-200 transition-all">Cancel</a>
    </div>
</form>
@endsection
