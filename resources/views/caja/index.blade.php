@extends('layouts.app')

@section('title', 'Caja')

@section('content')
<h1 class="text-2xl font-semibold mb-6">Mesas por cobrar</h1>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($mesas as $mesa)
        <article class="rounded-xl border bg-white p-4 shadow">
            <header class="flex justify-between items-center">
                <span class="text-sm font-semibold">Mesa {{ $mesa->numero }}</span>
                <span class="text-xs rounded-full bg-amber-100 text-amber-700 px-2 py-1">
                    En cuenta
                </span>
            </header>

            <div class="mt-3 text-sm text-slate-600">
                @foreach($mesa->pedidos as $pedido)
                    <p>Pedido #{{ $pedido->id }} — S/ {{ number_format($pedido->total, 2) }}</p>
                @endforeach
            </div>

            <div class="mt-3 font-semibold text-right">
                Total:
                S/ {{ number_format($mesa->pedidos->sum('total'), 2) }}
            </div>

            <a href="{{ route('ventas.create', $pedido->id) }}"
   class="btn btn-success">
    Cobrar mesa
</a>

        </article>
    @empty
        <p class="text-slate-500">No hay mesas para cobrar.</p>
    @endforelse
</div>
@endsection
