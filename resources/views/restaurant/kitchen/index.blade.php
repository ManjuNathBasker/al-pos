@extends('layouts.app')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Kitchen Display System (KDS)</h2>
        <p class="mt-1 text-sm text-slate-500">Live incoming orders from all tables and stations.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3">
        <div class="flex items-center gap-4 mr-4 text-xs font-medium text-slate-500">
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-300"></span> Pending</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Preparing</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500"></span> Ready</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($tickets as $ticket)
        <div class="bg-white rounded-2xl shadow-lg border-t-4 {{ $ticket->status == 'pending' ? 'border-slate-300' : ($ticket->status == 'preparing' ? 'border-amber-500' : 'border-green-500') }} overflow-hidden flex flex-col h-[500px]">
            <!-- Ticket Header -->
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between relative">
                <div>
                    <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                        @if($ticket->order->service_type === 'takeaway')
                            <span class="p-1 bg-amber-100 text-amber-600 rounded" title="Takeaway">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            </span>
                        @elseif($ticket->order->service_type === 'delivery')
                            <span class="p-1 bg-rose-100 text-rose-600 rounded" title="Delivery">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                            </span>
                        @endif
                        {{ $ticket->order->table->name ?? ($ticket->order->service_type === 'takeaway' ? 'TAKEAWAY' : ($ticket->order->service_type === 'delivery' ? 'DELIVERY' : 'WALK-IN')) }}
                    </h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $ticket->ticket_number }}</span>
                        @if($ticket->order)
                            @if(in_array($ticket->order->status, ['paid', 'closed']) || $ticket->order->payment_status === 'paid')
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full border border-emerald-300 inline-flex items-center gap-1" title="Payment Completed">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> PAID
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-black rounded-full border border-amber-300 inline-flex items-center gap-1" title="Payment Pending">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> UNPAID
                                </span>
                            @endif
                        @endif
                    </div>
                    @if($ticket->order->service_type === 'delivery' && $ticket->order->billing_address)
                        <p class="mt-1 text-[10px] font-black text-rose-500 uppercase leading-tight max-w-[150px]">
                            📍 {{ Str::limit($ticket->order->billing_address, 40) }}
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $ticket->created_at->format('H:i') }}</span>
                    <span class="text-xs font-bold text-indigo-600">{{ $ticket->created_at->diffForHumans(null, true) }}</span>
                </div>
            </div>

            <!-- Items List -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @foreach($ticket->items as $item)
                    <div class="flex items-start justify-between gap-3 group">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold text-slate-800">{{ $item->quantity }}x</span>
                                <span class="text-base font-semibold text-slate-700">{{ $item->product_name }}</span>
                            </div>
                            @if($item->note)
                                <p class="mt-1 text-xs font-medium text-red-500 italic bg-red-50 px-2 py-0.5 rounded-md inline-block">
                                    "{{ $item->note }}"
                                </p>
                            @endif
                        </div>
                        <div x-data="{ status: '{{ $item->status }}' }">
                            <button 
                                @click="status = status === 'ready' ? 'pending' : 'ready'; 
                                        fetch('{{ route('kitchen.items.status', $item) }}', {
                                            method: 'PATCH',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ status: status })
                                        })"
                                :class="status === 'ready' ? 'bg-green-500 border-green-500 text-white' : 'bg-white border-slate-200 text-slate-300'"
                                class="w-7 h-7 rounded-lg border-2 flex items-center justify-center hover:scale-110 transition-all duration-200">
                                <svg class="w-4 h-4" :class="status === 'ready' ? 'text-white' : 'text-slate-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Ticket Footer / Actions -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 space-y-2">
                @php
                    $kotPayload = [
                        'service_type' => $ticket->order->service_type ?? 'retail',
                        'kot_id' => $ticket->ticket_number,
                        'order_number' => $ticket->order->order_number ?? ('#ORD-' . $ticket->order_id),
                        'table_name' => $ticket->order->table->name ?? ($ticket->order->service_type === 'takeaway' ? 'TAKEAWAY' : ($ticket->order->service_type === 'delivery' ? 'DELIVERY' : 'COUNTER')),
                        'time' => $ticket->created_at->format('h:i A'),
                        'items' => $ticket->items->map(function($i) {
                            return ['name' => $i->product_name, 'qty' => $i->quantity, 'note' => $i->note];
                        })->values()->toArray(),
                    ];
                @endphp
                <button type="button" 
                    onclick='printKOTSlip(@json($kotPayload))'
                    class="w-full py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold text-xs rounded-xl border border-indigo-200 transition-colors flex items-center justify-center gap-1.5 shadow-sm active:scale-95">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>PRINT KOT SLIP</span>
                </button>

                <form action="{{ route('kitchen.tickets.status', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    @if($ticket->status == 'pending')
                        <button type="submit" name="status" value="preparing" class="w-full py-3 bg-amber-500 text-white text-sm font-black rounded-xl hover:bg-amber-600 shadow-md shadow-amber-100 transition-all">
                            START COOKING
                        </button>
                    @elseif($ticket->status == 'preparing')
                        <button type="submit" name="status" value="ready" class="w-full py-3 bg-green-500 text-white text-sm font-black rounded-xl hover:bg-green-600 shadow-md shadow-green-100 transition-all">
                            MARK AS READY
                        </button>
                    @else
                        <button type="submit" name="status" value="served" class="w-full py-3 bg-slate-800 text-white text-sm font-black rounded-xl hover:bg-slate-900 shadow-md shadow-slate-100 transition-all">
                            SERVED
                        </button>
                    @endif
                    
                    <button type="submit" name="status" value="cancelled" class="w-full mt-2 py-2 text-xs font-bold text-red-500 hover:text-red-700 transition-all" onclick="return confirm('Cancel this ticket?')">
                        CANCEL TICKET
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-full py-24 flex flex-col items-center justify-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
            <div class="p-6 bg-white rounded-full shadow-sm mb-4">
                <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800">All caught up!</h3>
            <p class="mt-1 text-slate-500">New orders will appear here automatically.</p>
        </div>
    @endforelse
</div>

<style>
    /* Pulse effect for pending tickets */
    @keyframes pulse-subtle {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    .border-slate-300 {
        animation: pulse-subtle 2s infinite ease-in-out;
    }
</style>
<script>
    function printKOTSlip(data) {
        const win = window.open('', '_blank', 'width=400,height=600');
        const itemsHtml = (data.items || []).map(item => `
            <tr style="border-bottom: 1px dashed #ccc;">
                <td style="padding: 4px 0; font-size: 14px; font-weight: bold; width: 40px; vertical-align: top;">${item.qty || item.quantity}x</td>
                <td style="padding: 4px 0; font-size: 13px; font-weight: bold; vertical-align: top;">
                    ${item.name || item.product_name}
                    ${item.note ? `<div style="font-size: 11px; font-weight: normal; color: #333; font-style: italic;">Note: ${item.note}</div>` : ''}
                </td>
            </tr>
        `).join('');

        const html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>KOT Print Slip</title>
    <style>
        body { margin: 0; padding: 10px; font-family: 'Courier New', monospace; font-size: 12px; background: #fff; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 2px; }
        .kot-container { width: 100%; max-width: 300px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="kot-container">
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase;">*** KITCHEN TICKET (KOT) ***</h2>
            <div style="font-size: 14px; font-weight: bold; margin-top: 4px; background: #000; color: #fff; padding: 3px 8px; display: inline-block; border-radius: 4px;">
                SERVICE: ${(data.service_type || 'COUNTER').toUpperCase().replace('_', ' ')}
            </div>
        </div>

        <table style="width: 100%; font-size: 11px; margin-bottom: 8px; border-bottom: 1px dashed #000; padding-bottom: 6px;">
            <tr>
                <td><strong>KOT ID:</strong> ${data.kot_id || 'KOT-' + (data.order_id || '')}</td>
                <td style="text-align: right;"><strong>ORDER #:</strong> ${data.order_number || ('#ORD-' + (data.order_id || ''))}</td>
            </tr>
            <tr>
                <td><strong>TABLE:</strong> ${data.table_name || 'N/A'}</td>
                <td style="text-align: right;"><strong>TIME:</strong> ${data.time || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</td>
            </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <thead>
                <tr style="border-bottom: 1px solid #000;">
                    <th style="text-align: left; padding: 4px 0; font-size: 11px;">QTY</th>
                    <th style="text-align: left; padding: 4px 0; font-size: 11px;">ITEM</th>
                </tr>
            </thead>
            <tbody>
                ${itemsHtml}
            </tbody>
        </table>

        <div style="text-align: center; border-top: 2px solid #000; padding-top: 8px; font-size: 10px; font-weight: bold;">
            *** END OF KOT SLIP ***
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 500);
        };
    <\/script>
</body>
</html>`;

        win.document.write(html);
        win.document.close();
    }

    // Auto refresh every 20 seconds to keep KDS up to date
    setInterval(() => {
        window.location.reload();
    }, 20000);
</script>
@endsection
