@extends('layouts.app')

@section('title', 'Cocina')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 portrait:flex-col portrait:items-start portrait:gap-3">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Panel de cocina</h1>
        <p class="text-sm text-slate-500">Pedidos pendientes y en preparación.</p>
    </div>
    <span class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white portrait:w-full portrait:justify-center">
        {{ now()->format('d M · H:i') }}
    </span>
</div>

<div class="mt-6 grid gap-4 portrait:grid-cols-1 landscape:grid-cols-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-amber-700">Pedidos pendientes</p>
        <p class="mt-2 text-2xl font-semibold text-amber-700">{{ $stats['pendientes'] }}</p>
    </article>
    <article class="rounded-2xl border border-sky-200 bg-sky-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-sky-700">En cocina</p>
        <p class="mt-2 text-2xl font-semibold text-sky-700">{{ $stats['en_cocina'] }}</p>
    </article>
    <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-emerald-700">Platos en preparación</p>
        <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $stats['detalles_en_preparacion'] }}</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-400">Espera promedio</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['promedio_espera'] }} min</p>
        <p class="text-xs text-slate-500">Desde envío a cocina</p>
    </article>
</div>

<div class="mt-6 grid gap-4 portrait:grid-cols-1 landscape:grid-cols-2 md:grid-cols-2 xl:grid-cols-3">
    @forelse($pedidos as $pedido)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->id }}</h2>
                    <p class="text-xs text-slate-400">Mesa {{ $pedido->mesa?->numero ?? 'para llevar' }} · {{ $pedido->mozo?->name ?? 'sin mozo' }}</p>
                    @if($pedido->enviado_a_cocina_at)
                        <p class="text-[11px] text-slate-400">En cocina desde {{ $pedido->enviado_a_cocina_at->diffForHumans(null, true) }}</p>
                    @endif
                </div>
                @if($pedido->estado !== \App\Models\Pedido::ESTADO_LISTO)
                    <form method="POST" action="{{ route('cocina.pedidos.listo', $pedido) }}" onsubmit="return confirm('¿Marcar pedido como listo?');">
                        @csrf
                        <button class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Listo</button>
                    </form>
                @endif
            </header>

            <div class="mt-4 space-y-3 text-sm">
                @foreach($pedido->detalles as $detalle)
                    @php
                        $detalleBadge = [
                            'en_preparacion' => 'bg-amber-100 text-amber-700',
                            'listo' => 'bg-emerald-100 text-emerald-700',
                            'anulado' => 'bg-rose-100 text-rose-700',
                        ][$detalle->estado] ?? 'bg-slate-200 text-slate-600';
                    @endphp
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</p>
                                <p class="text-[11px] text-slate-400">{{ $detalle->created_at?->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-white px-2 py-1 text-xs font-semibold text-slate-600">x{{ $detalle->cantidad }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $detalleBadge }}">{{ str_replace('_', ' ', $detalle->estado) }}</span>
                            </div>
                        </div>
                        @if($detalle->estado === \App\Models\DetallePedido::ESTADO_ANULADO)
                            <p class="mt-2 text-xs italic text-rose-600">Artículo anulado</p>
                        @else
                            <form method="POST" action="{{ route('cocina.detalles.actualizar', $detalle) }}" class="mt-3 flex flex-wrap items-center gap-2 text-xs portrait:flex-col portrait:items-stretch portrait:gap-3">
                                @csrf
                                @method('PATCH')
                                <select name="estado" class="rounded border border-slate-200 px-3 py-2 text-xs focus:border-emerald-400 focus:outline-none focus:ring-emerald-300 portrait:w-full">
                                    <option value="en_preparacion" @selected($detalle->estado === 'en_preparacion')>En preparación</option>
                                    <option value="listo" @selected($detalle->estado === 'listo')>Listo</option>
                                </select>
                                <input type="text" name="nota" value="{{ $detalle->nota_cocina }}" placeholder="Nota" class="flex-1 rounded border border-slate-200 px-3 py-2 text-xs focus:border-emerald-400 focus:outline-none focus:ring-emerald-300 portrait:w-full">
                                <button class="rounded bg-sky-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-sky-700 portrait:w-full">Actualizar</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>
    @empty
        <p class="text-sm text-slate-500">No hay pedidos pendientes.</p>
    @endforelse
</div>
@endsection
