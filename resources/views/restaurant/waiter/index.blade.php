@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-bold text-slate-800">Waiter Dashboard</h2>
    <p class="mt-1 text-sm text-slate-500">Select a table to start a new order or manage an existing one.</p>
</div>

<div class="space-y-10">
    @foreach($sections as $section)
        <section>
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4">{{ $section->name }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($section->tables as $table)
                    <a href="{{ route('waiter.order', $table) }}" class="group">
                        <div class="aspect-square rounded-2xl border-2 {{ $table->status == 'available' ? 'bg-white border-slate-100 hover:border-indigo-400' : 'bg-amber-50 border-amber-100 hover:border-amber-400' }} transition-all flex flex-col items-center justify-center p-4">
                            <span class="text-2xl font-black text-slate-800 group-hover:scale-110 transition-transform">{{ $table->name }}</span>
                            <span class="mt-1 text-[10px] font-bold {{ $table->status == 'available' ? 'text-slate-400' : 'text-amber-600' }} uppercase tracking-tighter">
                                {{ $table->status == 'available' ? 'AVAILABLE' : 'OCCUPIED' }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@endsection
