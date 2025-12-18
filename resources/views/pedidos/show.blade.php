@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->id)

@section('content')

<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">Pedido #{{ $pedido->id }}</h1>

        <p class="text-sm text-slate-500">
    @if($pedido->grupo)
        @php
            $numeros = \App\Models\Mesa::whereIn(
                'id',
                json_decode($pedido->grupo, true)
            )->pluck('numero')->join(', ');
        @endphp
        Mesas {{ $numeros }}
    @elseif($pedido->mesa)
        Mesa {{ $pedido->mesa->numero }}
    @else
        <span class="text-red-600 font-semibold">
            ⚠ Pedido sin mesa asignada
        </span>
    @endif
    · {{ $pedido->mozo?->name ?? 'Sin mozo asignado' }}
</p>

    </div>

    <div class="flex gap-2">
        <a href="{{ route('tickets.show', $pedido) }}" target="_blank"
           class="rounded-md bg-slate-200 px-3 py-2 text-xs font-semibold hover:bg-slate-300">
            Imprimir ticket
        </a>

        <a href="{{ route('pedidos.index') }}"
           class="rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-900">
            Volver
        </a>
    </div>
</div>

{{-- CONTENIDO --}}
<div class="mt-6 grid gap-6 lg:grid-cols-3">

    {{-- DETALLE --}}
    <section class="lg:col-span-2 space-y-4">

        <article class="rounded-xl border bg-white p-6 shadow-sm">
            <header class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold">Detalle del pedido</h2>
                    <p class="text-xs text-slate-400">
                        Total productos: {{ $pedido->detalles->sum('cantidad') }}
                    </p>
                </div>

                @php
                    $badges = [
                        'pendiente' => 'bg-amber-100 text-amber-700',
                        'en_cocina' => 'bg-sky-100 text-sky-700',
                        'listo' => 'bg-emerald-100 text-emerald-700',
                        'servido' => 'bg-slate-200 text-slate-700',
                        'pagado' => 'bg-lime-100 text-lime-700',
                        'anulado' => 'bg-rose-100 text-rose-700',
                    ];
                @endphp

                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $badges[$pedido->estado] }}">
                    {{ str_replace('_', ' ', $pedido->estado) }}
                </span>
            </header>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm divide-y">
                    <tbody class="divide-y">
                        @foreach($pedido->detalles as $detalle)
                            <tr>
                                <td class="px-3 py-2">
                                    {{ $detalle->producto->nombre ?? 'Producto eliminado' }}

                                    @if($detalle->comentario)
                                        <div class="mt-1 text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded">
                                            💬 {{ $detalle->comentario }}
                                        </div>
                                    @endif

                                    @if($detalle->nota_cocina)
                                        <div class="text-xs text-blue-600 mt-1">
                                            📝 {{ $detalle->nota_cocina }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-center">{{ $detalle->cantidad }}</td>
                                <td class="px-3 py-2 text-right">
                                    S/ {{ number_format($detalle->precio_unitario, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold">
                                    S/ {{ number_format($detalle->subtotal, 2) }}
                                </td>
                                <td class="px-3 py-2 text-xs uppercase text-slate-400 text-right">
                                    {{ str_replace('_', ' ', $detalle->estado) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pedido->nota_cocina)
                <p class="mt-4 bg-amber-50 text-amber-700 p-3 rounded">
                    {{ $pedido->nota_cocina }}
                </p>
            @endif
        </article>

        {{-- ACCIONES --}}
        <article class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold mb-3">Acciones</h2>

            <div class="flex flex-wrap gap-3">
                @if(auth()->user()->role === 'mozo' || auth()->user()->role === 'admin')

                    @if($pedido->estado === 'pendiente')
                        <form method="POST" action="{{ route('pedidos.enviar', $pedido) }}">
                            @csrf
                            <button class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700">
                                Enviar a cocina
                            </button>
                        </form>
                    @endif

                    @if($pedido->estado === 'listo')
                        <form method="POST" action="{{ route('pedidos.servido', $pedido) }}">
                            @csrf
                            <button class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">
                                Marcar servido
                            </button>
                        </form>
                    @endif

                    @if(!in_array($pedido->estado, ['anulado', 'pagado']))
                        <form method="POST" action="{{ route('pedidos.anular', $pedido) }}"
                              onsubmit="return confirm('¿Anular pedido?')">
                            @csrf
                            <button class="bg-rose-600 text-white px-4 py-2 rounded hover:bg-rose-700">
                                Anular
                            </button>
                        </form>
                    @endif

                @endif
            </div>
        </article>

    </section>

    {{-- RESUMEN --}}
    <aside class="space-y-4">

        <article class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold mb-3">Resumen</h2>

            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt>Subtotal</dt>
                    <dd class="font-semibold">S/ {{ number_format($pedido->total, 2) }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt>Estado</dt>
                    <dd class="font-semibold">{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt>Creado</dt>
                    <dd>{{ $pedido->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </article>

        

        @if($pedido->venta)
            <article class="rounded-xl border bg-lime-50 border-lime-200 p-4 shadow-sm">
                <h3 class="font-semibold text-lime-700">Venta registrada</h3>
                <p class="text-sm text-lime-700">Código: {{ $pedido->venta->codigo }}</p>
                <p class="text-sm text-lime-700">
                    Tipo pago: {{ ucfirst($pedido->venta->tipo_pago) }}
                </p>
            </article>
        @endif

       <article class="rounded-xl border bg-white p-4 shadow-sm">
    <h3 class="text-sm font-semibold mb-1">Mesas asignadas</h3>

    @if($pedido->grupo)
        @php
            $numeros = \App\Models\Mesa::whereIn(
                'id',
                json_decode($pedido->grupo, true)
            )->pluck('numero')->toArray();
        @endphp
        <p class="text-sm text-slate-600">
            {{ implode(', ', $numeros) }}
        </p>
    @elseif($pedido->mesa)
        <p class="text-sm text-slate-600">
            Mesa {{ $pedido->mesa->numero }}
        </p>
    @else
        <p class="text-sm text-red-600 font-semibold">
            ⚠ Pedido sin mesas asignadas
        </p>
    @endif
</article>



    </aside>

</div>

@endsection
