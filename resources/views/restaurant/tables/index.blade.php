@extends('layouts.app')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Table Management</h2>
        <p class="mt-1 text-sm text-slate-500">Add and manage physical tables for your restaurant.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3">
        <a href="{{ route('sections.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
            Manage Sections
        </a>
        <button onclick="document.getElementById('add-table-modal').classList.remove('hidden')" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-all">
            Add New Table
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">Table Name</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Section</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Capacity</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Status</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">QR Code</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($tables as $table)
                <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                        <div class="font-medium text-slate-900">{{ $table->name }}</div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                        <span class="px-2 py-1 bg-slate-100 rounded text-xs">{{ $table->section->name }}</span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                        {{ $table->capacity }} Persons
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                        @php
                            $statusColors = [
                                'available' => 'bg-green-50 text-green-700 ring-green-600/20',
                                'occupied' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                'reserved' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                'cleaning' => 'bg-slate-50 text-slate-700 ring-slate-600/20',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $statusColors[$table->status] }}">
                            {{ ucfirst($table->status) }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-indigo-600">
                        <button onclick="showQR('{{ $table->name }}', '{{ route('guest.menu', $table->qr_token) }}')" class="hover:underline flex items-center gap-1 text-indigo-600 font-semibold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            View QR
                        </button>
                    </td>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <div class="flex items-center justify-end gap-3">
                            <button onclick='editTable(@json($table))' class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            <form action="{{ route('tables.destroy', $table) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-500">
                        No tables found. Add your first table to get started.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Table Modal -->
<div id="add-table-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('add-table-modal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('tables.store') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Add New Table</h3>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Table Name / Number</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5" placeholder="e.g. Table 01">
                    </div>

                    <div>
                        <label for="section_id" class="block text-sm font-medium text-slate-700">Section</label>
                        <select name="section_id" id="section_id" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5 bg-white">
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="capacity" class="block text-sm font-medium text-slate-700">Seating Capacity</label>
                        <input type="number" name="capacity" id="capacity" value="2" min="1" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5">
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:w-auto sm:text-sm">
                        Add Table
                    </button>
                    <button type="button" onclick="document.getElementById('add-table-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Table Modal -->
<div id="edit-table-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('edit-table-modal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="edit-table-form" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Edit Table</h3>
                    
                    <div>
                        <label for="edit-table-name" class="block text-sm font-medium text-slate-700">Table Name / Number</label>
                        <input type="text" name="name" id="edit-table-name" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5">
                    </div>

                    <div>
                        <label for="edit-section-id" class="block text-sm font-medium text-slate-700">Section</label>
                        <select name="section_id" id="edit-section-id" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5 bg-white">
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit-capacity" class="block text-sm font-medium text-slate-700">Seating Capacity</label>
                        <input type="number" name="capacity" id="edit-capacity" min="1" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5">
                    </div>

                    <div>
                        <label for="edit-status" class="block text-sm font-medium text-slate-700">Status</label>
                        <select name="status" id="edit-status" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5 bg-white">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="reserved">Reserved</option>
                            <option value="cleaning">Cleaning</option>
                        </select>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:w-auto sm:text-sm">
                        Save Changes
                    </button>
                    <button type="button" onclick="document.getElementById('edit-table-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editTable(table) {
        const modal = document.getElementById('edit-table-modal');
        const form = document.getElementById('edit-table-form');
        
        form.action = `/tables/${table.id}`;
        document.getElementById('edit-table-name').value = table.name;
        document.getElementById('edit-section-id').value = table.section_id;
        document.getElementById('edit-capacity').value = table.capacity;
        document.getElementById('edit-status').value = table.status;
        
        modal.classList.remove('hidden');
    }

    function showQR(tableName, url) {
        document.getElementById('qr-table-name').innerText = tableName;
        document.getElementById('qr-url').innerText = url;
        const qrImg = document.getElementById('qr-image');
        qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
        document.getElementById('qr-modal').classList.remove('hidden');
    }
</script>

<!-- QR Modal -->
<div id="qr-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('qr-modal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
            <div class="bg-white px-8 pt-8 pb-6 text-center">
                <div class="mb-6">
                    <h3 class="text-xl font-black text-slate-800" id="qr-table-name">Table QR</h3>
                    <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest mt-1">Digital Menu Access</p>
                </div>
                
                <div class="bg-slate-50 p-6 rounded-3xl border-2 border-dashed border-slate-200 mb-6 flex justify-center">
                    <img id="qr-image" src="" alt="QR Code" class="w-48 h-48">
                </div>

                <div class="bg-slate-50 rounded-2xl p-4 mb-6">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Direct Link</p>
                    <p class="text-[11px] font-mono text-slate-600 break-all" id="qr-url"></p>
                </div>

                <button onclick="window.open(document.getElementById('qr-url').innerText, '_blank')" class="w-full bg-slate-900 text-white rounded-2xl py-3 font-bold hover:bg-slate-800 transition-colors mb-3">
                    Open Menu
                </button>
                <button onclick="document.getElementById('qr-modal').classList.add('hidden')" class="w-full bg-slate-100 text-slate-500 rounded-2xl py-3 font-bold hover:bg-slate-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
