@extends('layouts.app')

@section('title', 'Mesas')

@section('content')
<h1 class="text-2xl font-semibold text-slate-900 mb-4">Mesas</h1>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
@foreach($mesas as $mesa)
    @php
        $estadoColor = [
            'libre' => 'bg-emerald-100 text-emerald-700',
            'ocupada' => 'bg-amber-100 text-amber-700',
            'en_cuenta' => 'bg-sky-100 text-sky-700',
        ][$mesa->estado] ?? 'bg-slate-100 text-slate-700';
    @endphp

    <article class="rounded-xl border p-4 bg-white shadow-sm">
        <div class="flex justify-between">
            <span class="font-semibold">Mesa {{ $mesa->numero }}</span>
            <span class="text-xs px-2 py-1 rounded-full {{ $estadoColor }}">
                {{ str_replace('_', ' ', $mesa->estado) }}
            </span>
        </div>

        <div class="mt-3 text-sm text-slate-500">
            @forelse($mesa->pedidos as $pedido)
                <a href="{{ route('pedidos.show', $pedido) }}" class="block">
                    Pedido #{{ $pedido->id }}
                </a>
            @empty
                Sin pedidos
            @endforelse
        </div>

        <a href="{{ route('pedidos.create', ['mesa_id' => $mesa->id]) }}"
           class="mt-3 block text-center bg-emerald-600 text-white py-2 rounded">
            Nuevo pedido
        </a>
    </article>
@endforeach
</div>
@endsection
