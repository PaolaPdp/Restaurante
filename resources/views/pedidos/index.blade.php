@extends('layouts.app')

@section('title', 'Pedidos')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 portrait:flex-col portrait:items-start portrait:gap-3">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">Pedidos</h1>
        <p class="text-sm text-slate-500">Revisa y gestiona los pedidos activos y cerrados.</p>
    </div>
    @if(in_array(auth()->user()->role, ['admin', 'mozo'], true))
        <a href="{{ route('pedidos.create') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 portrait:w-full portrait:text-center">Nuevo pedido</a>
    @endif
</div>

<form method="GET" action="{{ route('pedidos.index') }}" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center portrait:w-full portrait:gap-4">
    <label class="text-sm font-medium text-slate-600">Estado:</label>
    <select name="estado" class="rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-100 portrait:w-full">
        <option value="">Todos</option>
        @foreach($estados as $valor => $label)
            <option value="{{ $valor }}" @selected($estado === $valor)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900 sm:w-auto portrait:w-full">Filtrar</button>
</form>

<div class="mt-6 rounded-xl border border-slate-200 bg-white shadow">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Pedido</th>
                <th class="px-4 py-3 text-left">Mesa</th>
                <th class="px-4 py-3 text-left">Mozo</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-left">Estado</th>
                <th class="px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($pedidos as $pedido)
                @php
                    $estadoBadge = [
                        'pendiente' => 'bg-amber-100 text-amber-700',
                        'en_cocina' => 'bg-sky-100 text-sky-700',
                        'listo' => 'bg-emerald-100 text-emerald-700',
                        'servido' => 'bg-slate-200 text-slate-700',
                        'pagado' => 'bg-lime-100 text-lime-700',
                        'anulado' => 'bg-rose-100 text-rose-700',
                    ][$pedido->estado] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold text-slate-700">#{{ $pedido->id }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $pedido->mesa?->numero ?? 'Para llevar' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $pedido->mozo?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($pedido->total, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $estadoBadge }}">{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('pedidos.show', $pedido) }}" class="text-xs font-semibold text-emerald-600 hover:underline">Ver</a>
                            @if(in_array(auth()->user()->role, ['admin', 'mozo'], true) && in_array($pedido->estado, [\App\Models\Pedido::ESTADO_PENDIENTE], true))
                                <form method="POST" action="{{ route('pedidos.enviar', $pedido) }}">
                                    @csrf
                                    <button class="rounded bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700">Enviar</button>
                                </form>
                            @endif
                            @if(in_array(auth()->user()->role, ['admin', 'mozo'], true) && in_array($pedido->estado, [\App\Models\Pedido::ESTADO_LISTO], true))
                                <form method="POST" action="{{ route('pedidos.servido', $pedido) }}">
                                    @csrf
                                    <button class="rounded bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Servido</button>
                                </form>
                            @endif
                            @if(in_array(auth()->user()->role, ['admin', 'mozo'], true) && !in_array($pedido->estado, [\App\Models\Pedido::ESTADO_PAGADO, \App\Models\Pedido::ESTADO_ANULADO], true))
                                <form method="POST" action="{{ route('pedidos.anular', $pedido) }}" onsubmit="return confirm('¿Anular pedido?');">
                                    @csrf
                                    <button class="rounded bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700">Anular</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No hay pedidos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-6">
    {{ $pedidos->links() }}
</div>
@endsection
