@extends('layouts.app')

@section('title', 'Cobrar mesa')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-xl shadow p-6 space-y-4">

    <h2 class="text-xl font-bold">
        Cobrar Mesa {{ $pedido->mesa?->numero ?? '—' }}
    </h2>

    <p class="text-sm text-slate-500">
        Pedido #{{ $pedido->id }}
    </p>

    <p class="text-lg font-semibold">
        Total: S/ {{ number_format($pedido->total, 2) }}
    </p>

    <form method="POST" action="{{ route('ventas.store') }}">
        @csrf

        <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">

        <label class="block text-sm font-medium mb-1">Método de pago</label>
        <select name="tipo_pago" class="w-full border rounded p-2 mb-4" required>
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="yape">Yape</option>
            <option value="plin">Plin</option>
            <option value="transferencia">Transferencia</option>
        </select>

        <button class="w-full bg-emerald-600 text-white py-2 rounded font-semibold hover:bg-emerald-700">
            Confirmar pago
        </button>
    </form>

</div>
@endsection
