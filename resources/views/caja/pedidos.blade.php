@extends('layouts.app')

@section('title', 'Caja')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Caja</h1>
        <p class="text-sm text-slate-500">Pedidos listos para cobrar</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">Pedido</th>
                    <th class="px-4 py-3 text-left">Mesa</th>
                    <th class="px-4 py-3 text-left">Mozo</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pedidos as $pedido)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-700">
                            #{{ $pedido->id }}
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $pedido->mesa?->numero ?? 'Para llevar' }}
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $pedido->mozo?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-700">
                            S/ {{ number_format($pedido->total, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('ventas.create', $pedido) }}"
                               class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                Cobrar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                            No hay pedidos pendientes de cobro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
