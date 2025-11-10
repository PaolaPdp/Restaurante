@extends('layouts.app')

@section('title', 'Mesas')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 portrait:flex-col portrait:items-start portrait:gap-3 portrait:justify-start">
  <div>
    <h1 class="text-2xl font-semibold text-slate-900">Gestión de mesas</h1>
    <p class="text-sm text-slate-500">Controla el estado y pedidos activos.</p>
  </div>
  <a href="{{ route('pedidos.create') }}" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 portrait:w-full portrait:justify-center">
    <span class="text-base">＋</span>
    Nuevo pedido
  </a>
</div>

<div class="mt-6 grid gap-4 portrait:grid-cols-1 landscape:grid-cols-4 sm:grid-cols-2 xl:grid-cols-5">
  <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <p class="text-xs font-semibold uppercase text-slate-400">Total</p>
    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
    <p class="text-xs text-slate-500">Mesas registradas</p>
  </article>
  <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
    <p class="text-xs font-semibold uppercase text-emerald-700">Libres</p>
    <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $stats['libres'] }}</p>
    <p class="text-xs text-emerald-600">Disponibles</p>
  </article>
  <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
    <p class="text-xs font-semibold uppercase text-amber-700">Ocupadas</p>
    <p class="mt-2 text-2xl font-semibold text-amber-700">{{ $stats['ocupadas'] }}</p>
    <p class="text-xs text-amber-600">En servicio</p>
  </article>
  <article class="rounded-2xl border border-sky-200 bg-sky-50 p-4 shadow-sm">
    <p class="text-xs font-semibold uppercase text-sky-700">En cuenta</p>
    <p class="mt-2 text-2xl font-semibold text-sky-700">{{ $stats['en_cuenta'] }}</p>
    <p class="text-xs text-sky-600">Esperando pago</p>
  </article>
  <article class="rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
    <p class="text-xs font-semibold uppercase text-rose-700">Bloqueadas</p>
    <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $stats['bloqueadas'] }}</p>
    <p class="text-xs text-rose-600">Reservadas o fuera de servicio</p>
  </article>
</div>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
  <header class="flex flex-wrap items-center justify-between gap-4 portrait:flex-col portrait:items-start portrait:gap-3">
    <div>
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Ocupación actual</p>
      <p class="text-xl font-semibold text-slate-800">{{ $stats['ocupacion'] }}% de mesas en uso</p>
    </div>
    <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-500">{{ $stats['ocupadas'] + $stats['en_cuenta'] }} / {{ $stats['total'] }} en servicio</span>
  </header>
  <div class="mt-4 h-3 rounded-full bg-slate-100">
    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $stats['ocupacion'] }}%"></div>
  </div>
</div>

<div class="mt-6 grid gap-4 portrait:grid-cols-1 landscape:grid-cols-2 md:grid-cols-2 xl:grid-cols-3">
  @forelse($mesas as $mesa)
    @php
      $estadoBadge = [
        'libre' => 'bg-emerald-100 text-emerald-700',
        'ocupada' => 'bg-amber-100 text-amber-700',
        'en_cuenta' => 'bg-sky-100 text-sky-700',
        'bloqueada' => 'bg-rose-100 text-rose-700',
      ][$mesa->estado] ?? 'bg-slate-100 text-slate-700';
    @endphp
    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <header class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-slate-800">Mesa {{ $mesa->numero }}</h2>
          <p class="text-xs text-slate-400">Capacidad {{ $mesa->capacidad }} personas</p>
        </div>
        <span class="rounded-full px-2 py-1 text-xs font-medium uppercase {{ $estadoBadge }}">{{ str_replace('_', ' ', $mesa->estado) }}</span>
      </header>

      <div class="mt-4 space-y-2 text-sm text-slate-500">
        @forelse($mesa->pedidos as $pedido)
          <div class="flex items-center justify-between rounded border border-slate-100 px-2 py-1">
            <a href="{{ route('pedidos.show', $pedido) }}" class="font-semibold text-slate-700">Pedido #{{ $pedido->id }}</a>
            <span>{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</span>
          </div>
        @empty
          <p class="text-slate-400">No hay pedidos abiertos.</p>
        @endforelse
      </div>

      <div class="mt-4 space-y-2">
        <form method="POST" action="{{ route('mesas.estado', $mesa) }}" class="flex flex-wrap items-center gap-2 text-sm portrait:flex-col portrait:items-stretch portrait:gap-3">
          @csrf
          @method('PATCH')
          <label class="text-slate-500">Estado:</label>
          <select name="estado" class="rounded border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300">
            <option value="libre" @selected($mesa->estado === 'libre')>Libre</option>
            <option value="ocupada" @selected($mesa->estado === 'ocupada')>Ocupada</option>
            <option value="en_cuenta" @selected($mesa->estado === 'en_cuenta')>En cuenta</option>
            <option value="bloqueada" @selected($mesa->estado === 'bloqueada')>Bloqueada</option>
          </select>
          <button class="rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-900">Actualizar</button>
        </form>

        <div class="flex flex-wrap gap-2 portrait:flex-col portrait:items-stretch portrait:gap-3">
          @if(in_array(auth()->user()->role, ['admin', 'mozo'], true))
            <a href="{{ route('pedidos.create', ['mesa_id' => $mesa->id]) }}" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 portrait:w-full portrait:justify-center">Crear pedido</a>
          @endif
          <form method="POST" action="{{ route('mesas.liberar', $mesa) }}">
            @csrf
            <button class="inline-flex items-center rounded-md bg-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-300 portrait:w-full portrait:justify-center">Liberar</button>
          </form>
        </div>
      </div>
    </article>
  @empty
    <p class="text-sm text-slate-500">No hay mesas registradas todavía.</p>
  @endforelse
</div>
@endsection
