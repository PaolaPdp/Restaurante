@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 portrait:flex-col portrait:items-start portrait:gap-3">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Historial de ventas</h1>
        <p class="text-sm text-slate-500">Pagos registrados en el sistema.</p>
    </div>
</div>

<div class="mt-6 grid gap-4 portrait:grid-cols-1 landscape:grid-cols-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-emerald-700">Ingresos hoy</p>
        <p class="mt-2 text-2xl font-semibold text-emerald-700">S/ {{ number_format($stats['ingresos_hoy'], 2) }}</p>
    </article>
    <article class="rounded-2xl border border-sky-200 bg-sky-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-sky-700">Ingresos del mes</p>
        <p class="mt-2 text-2xl font-semibold text-sky-700">S/ {{ number_format($stats['ingresos_mes'], 2) }}</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-400">Ticket promedio</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">S/ {{ number_format($stats['ticket_promedio'], 2) }}</p>
        <p class="text-xs text-slate-500">En {{ $stats['total'] }} ventas</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-400">Ventas registradas</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500">Histórico total</p>
    </article>
</div>

@if($mediosPago->isNotEmpty())
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Medios de pago</h2>
        <div class="mt-4 grid gap-3 portrait:grid-cols-1 landscape:grid-cols-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach($mediosPago as $medio)
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <div>
                        <p class="font-semibold text-slate-700">{{ ucfirst($medio->tipo_pago) }}</p>
                        <p class="text-xs text-slate-400">{{ $medio->total }} transacciones</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500">S/ {{ number_format($medio->monto, 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Código</th>
                <th class="px-4 py-3 text-left">Pedido</th>
                <th class="px-4 py-3 text-left">Mesa</th>
                <th class="px-4 py-3 text-left">Pago</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-left">Fecha</th>
                <th class="px-4 py-3 text-left">Responsable</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($ventas as $venta)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $venta->codigo }}</td>
                    <td class="px-4 py-3 text-slate-500">#{{ $venta->pedido_id }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $venta->pedido?->mesa?->numero ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500 capitalize">{{ $venta->tipo_pago }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-700">S/ {{ number_format($venta->total, 2) }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $venta->fecha?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $venta->responsable?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">No hay ventas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-6">
    {{ $ventas->links() }}
</div>
@endsection
