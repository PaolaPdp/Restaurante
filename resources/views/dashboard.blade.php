@extends('layouts.app')

@section('title', 'Panel')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Panel general</h1>
        <p class="text-sm text-slate-500">Resumen rápido del servicio en curso.</p>
    </div>
    @if(isset($stats['ventas_hoy']))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 shadow-sm">
            Ventas de hoy: <span class="text-base">S/ {{ number_format($stats['ventas_hoy'], 2) }}</span>
        </div>
    @endif
</div>

<div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <header class="flex items-center justify-between text-xs font-semibold uppercase text-slate-400">
            Ocupación
            <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] text-slate-500">{{ $stats['total_mesas'] }} mesas</span>
        </header>
        <p class="mt-4 text-3xl font-semibold text-slate-900">{{ $stats['ocupacion'] }}%</p>
        <p class="text-sm text-slate-500">{{ $stats['ocupadas'] + $stats['en_cuenta'] }} mesas en uso</p>
        <div class="mt-4 h-2 rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $stats['ocupacion'] }}%"></div>
        </div>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <header class="text-xs font-semibold uppercase text-slate-400">Mesas libres</header>
        <p class="mt-4 text-3xl font-semibold text-slate-900">{{ $stats['libres'] }}</p>
        <p class="text-sm text-slate-500">Disponibles para asignar</p>
        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-1 text-emerald-700">Libres</span>
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-amber-700">Ocupadas {{ $stats['ocupadas'] }}</span>
            <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-1 text-sky-700">Cuenta {{ $stats['en_cuenta'] }}</span>
        </div>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <header class="text-xs font-semibold uppercase text-slate-400">Pedidos activos</header>
        <p class="mt-4 text-3xl font-semibold text-slate-900">{{ $stats['pedidos_activos'] }}</p>
        <p class="text-sm text-slate-500">En seguimiento por el equipo</p>
        <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-500">
            {{ $stats['cocina_en_preparacion'] }} en cocina · {{ $stats['pedidos_activos'] - $stats['cocina_en_preparacion'] }} en sala
        </div>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <header class="text-xs font-semibold uppercase text-slate-400">Accesos directos</header>
        <div class="mt-4 grid gap-2 text-sm">
            <a href="{{ route('mesas.index') }}" class="inline-flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 font-semibold text-slate-600 hover:border-emerald-200 hover:text-emerald-600">Gestión de mesas<span class="text-xs">→</span></a>
            <a href="{{ route('pedidos.index') }}" class="inline-flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 font-semibold text-slate-600 hover:border-emerald-200 hover:text-emerald-600">Pedidos<span class="text-xs">→</span></a>
            <a href="{{ route('ventas.index') }}" class="inline-flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 font-semibold text-slate-600 hover:border-emerald-200 hover:text-emerald-600">Caja<span class="text-xs">→</span></a>
        </div>
    </article>
</div>

<div class="mt-8 grid gap-8 lg:grid-cols-3">
    <section class="lg:col-span-2 space-y-4">
        <h2 class="text-lg font-semibold text-slate-700">Mesas</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($mesas as $mesa)
                @php
                    $estadoBadge = [
                        'libre' => 'bg-emerald-100 text-emerald-700',
                        'ocupada' => 'bg-amber-100 text-amber-700',
                        'en_cuenta' => 'bg-sky-100 text-sky-700',
                        'bloqueada' => 'bg-rose-100 text-rose-700',
                    ][$mesa->estado] ?? 'bg-slate-100 text-slate-700';
                @endphp
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <header class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase text-slate-500">Mesa</span>
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $estadoBadge }}">{{ str_replace('_', ' ', $mesa->estado) }}</span>
                    </header>
                    <div class="mt-3 flex items-baseline justify-between">
                        <p class="text-3xl font-bold text-slate-800">{{ $mesa->numero }}</p>
                        <span class="text-xs text-slate-400">{{ $mesa->capacidad }} pax</span>
                    </div>
                    <div class="mt-3 space-y-2 text-xs text-slate-500">
                        @forelse($mesa->pedidos as $pedido)
                            <div class="flex items-center justify-between rounded border border-slate-100 px-2 py-1">
                                <a href="{{ route('pedidos.show', $pedido) }}" class="font-medium text-slate-600">Pedido #{{ $pedido->id }}</a>
                                <span>{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</span>
                            </div>
                        @empty
                            <p class="text-slate-400">Sin pedidos activos.</p>
                        @endforelse
                    </div>
                    @if(in_array(auth()->user()->role, ['admin', 'mozo'], true))
                        <a href="{{ route('pedidos.create', ['mesa_id' => $mesa->id]) }}" class="mt-4 inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Nuevo pedido</a>
                    @endif
                </article>
            @empty
                <p class="text-sm text-slate-500">No hay mesas registradas.</p>
            @endforelse
        </div>
    </section>

    <section class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-700">Pedidos recientes</h2>
        <div class="space-y-3">
            @forelse($pedidos as $pedido)
                <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Pedido #{{ $pedido->id }}</p>
                            <p class="text-xs text-slate-400">Mesa {{ $pedido->mesa?->numero ?? '—' }} · {{ $pedido->mozo?->name ?? '—' }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">
                        Total: <span class="font-semibold text-slate-900">S/ {{ number_format($pedido->total, 2) }}</span>
                    </p>
                    <a href="{{ route('pedidos.show', $pedido) }}" class="mt-3 inline-flex items-center text-xs font-semibold text-emerald-600">Ver detalles →</a>
                </article>
            @empty
                <p class="text-sm text-slate-500">Sin pedidos recientes.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
