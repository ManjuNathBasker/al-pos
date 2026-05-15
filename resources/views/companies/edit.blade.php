@extends('layouts.app')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('companies.index') }}" class="p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Edit Company</h2>
            <p class="mt-1 text-sm text-slate-500">Update company details.</p>
        </div>
    </div>
</div>

<form action="{{ route('companies.update', $company) }}" method="POST" class="space-y-6 max-w-4xl">
    @csrf
    @method('PATCH')

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-slate-700">Company Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Business Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Business Type -->
                <div class="md:col-span-2">
                    <label for="business_type" class="block text-sm font-medium text-slate-700">Business Type</label>
                    <select name="business_type" id="business_type" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2 bg-white">
                        <option value="retail" {{ old('business_type', $company->business_type) == 'retail' ? 'selected' : '' }}>Retail Store</option>
                        <option value="restaurant" {{ old('business_type', $company->business_type) == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                        <option value="cafe" {{ old('business_type', $company->business_type) == 'cafe' ? 'selected' : '' }}>Cafe</option>
                        <option value="bakery" {{ old('business_type', $company->business_type) == 'bakery' ? 'selected' : '' }}>Bakery</option>
                        <option value="food_court" {{ old('business_type', $company->business_type) == 'food_court' ? 'selected' : '' }}>Food Court</option>
                        <option value="supermarket" {{ old('business_type', $company->business_type) == 'supermarket' ? 'selected' : '' }}>Supermarket</option>
                        <option value="bookstall" {{ old('business_type', $company->business_type) == 'bookstall' ? 'selected' : '' }}>Bookstall</option>
                        <option value="boutique" {{ old('business_type', $company->business_type) == 'boutique' ? 'selected' : '' }}>Boutique</option>
                        <option value="pharmacy" {{ old('business_type', $company->business_type) == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                    </select>
                    @error('business_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">{{ old('address', $company->address) }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="bg-slate-50 px-6 py-4 flex items-center justify-end border-t border-slate-200">
            <a href="{{ route('companies.index') }}" class="rounded-md border border-slate-300 bg-white py-2 px-4 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 mr-3">Cancel</a>
            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Update Company</button>
        </div>
    </div>
</form>
@endsection
