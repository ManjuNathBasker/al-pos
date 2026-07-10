@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Bank Settlements</h2>
    <p class="mt-1 text-sm text-slate-500">Reconcile card transaction settlements from your bank statements.</p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- Left: Settlement Form --}}
    <div class="xl:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5 sticky top-6">
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Record Settlement</h3>
            <form action="{{ route('settlements.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Pending Transaction <span class="text-red-500">*</span></label>
                    <select name="card_transaction_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-indigo-400 outline-none">
                        <option value="">Select Transaction</option>
                        @foreach($pendingTransactions as $tx)
                            <option value="{{ $tx->id }}" {{ old('card_transaction_id') == $tx->id ? 'selected' : '' }}>
                                #{{ $tx->order_id }} — {{ $tx->bank_name }} — ${{ number_format($tx->net_settlement_amount, 2) }} ({{ $tx->created_at->format('M d') }})
                            </option>
                        @endforeach
                    </select>
                    @error('card_transaction_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Actual Amount Received <span class="text-red-500">*</span></label>
                    <input type="number" name="actual_settlement_amount" value="{{ old('actual_settlement_amount') }}" step="0.01" min="0" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:bg-white focus:border-indigo-400 outline-none" />
                    @error('actual_settlement_amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Bank Charges <span class="text-red-500">*</span></label>
                        <input type="number" name="bank_charges" value="{{ old('bank_charges', 0) }}" step="0.01" min="0" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:bg-white focus:border-indigo-400 outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Processing Charges <span class="text-red-500">*</span></label>
                        <input type="number" name="processing_charges" value="{{ old('processing_charges', 0) }}" step="0.01" min="0" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:bg-white focus:border-indigo-400 outline-none" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Settlement Date <span class="text-red-500">*</span></label>
                    <input type="date" name="settlement_date" value="{{ old('settlement_date', now()->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Bank Reference</label>
                    <input type="text" name="bank_statement_reference" value="{{ old('bank_statement_reference') }}" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:border-indigo-400 outline-none" placeholder="UTR / Ref Number" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-indigo-400 outline-none">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="w-full px-4 py-3 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-all">
                    Record Settlement
                </button>
            </form>
        </div>
    </div>

    {{-- Right: Settlement History --}}
    <div class="xl:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-200 flex justify-between items-center">
                <h3 class="font-bold text-slate-700">Settlement History</h3>
                <span class="text-xs text-slate-500 font-semibold">{{ $settlements->total() }} record(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-6 py-3">Date / Reference</th>
                            <th class="px-6 py-3">Card Transaction</th>
                            <th class="px-6 py-3 text-right">Expected</th>
                            <th class="px-6 py-3 text-right">Actual</th>
                            <th class="px-6 py-3 text-right">Difference</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($settlements as $s)
                        <tr class="hover:bg-slate-50/30">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($s->settlement_date)->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $s->bank_statement_reference ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700">{{ $s->cardTransaction->bank_name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400">Order #{{ $s->cardTransaction->order_id ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-700">${{ number_format($s->expected_settlement_amount, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">${{ number_format($s->actual_settlement_amount, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold {{ $s->settlement_difference != 0 ? 'text-red-600' : 'text-slate-600' }}">
                                ${{ number_format($s->settlement_difference, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $s->status === 'completed' ? 'bg-green-100 text-green-800' : ($s->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 font-medium">No settlements recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($settlements->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $settlements->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
