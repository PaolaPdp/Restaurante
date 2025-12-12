@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->id)

@section('content')

<div class="flex flex-wrap items-start justify-between gap-4 portrait:flex-col portrait:items-start portrait:gap-3">
  <div>
    <h1 class="text-2xl font-semibold text-slate-800">Pedido #{{ $pedido->id }}</h1>
    <p class="text-sm text-slate-500">
      Mesa {{ $pedido->mesa?->numero ?? 'para llevar' }}
      · {{ $pedido->mozo?->name ?? 'Sin mozo asignado' }}
    </p>
  </div>
  <div class="flex flex-wrap items-center gap-2 portrait:flex-col portrait:items-stretch portrait:gap-3">
    <a href="{{ route('tickets.show', $pedido) }}" target="_blank" class="rounded-md bg-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-300 portrait:w-full portrait:text-center">Imprimir ticket</a>
    <a href="{{ route('pedidos.index') }}" class="rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-900 portrait:w-full portrait:text-center">Volver</a>
  </div>
</div>

<div class="mt-6 grid gap-6 portrait:grid-cols-1 landscape:grid-cols-2 lg:grid-cols-3">
  <section class="lg:col-span-2 space-y-4">
    <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <header class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-slate-700">Detalle del pedido</h2>
          <p class="text-xs text-slate-400">Total productos: {{ $pedido->detalles->sum('cantidad') }}</p>
        </div>
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
        <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $estadoBadge }}">{{ str_replace('_', ' ', $pedido->estado) }}</span>
      </header>

  <div class="mt-4 overflow-x-auto">
  <table class="min-w-full divide-y divide-slate-200 text-sm">
       
        <tbody class="divide-y divide-slate-100">
    @foreach($pedido->detalles as $detalle)
        <tr>
            <td class="px-3 py-2 text-slate-700">

                {{-- Nombre --}}
                {{ $detalle->producto->nombre ?? 'Producto eliminado' }}

                {{-- Comentario con icono --}}
                @if($detalle->comentario)
                    <div class="mt-1 text-xs text-amber-700 bg-amber-50 px-2 py-1 rounded flex items-center gap-1">
                        <i class="fa-solid fa-message"></i>
                        {{ $detalle->comentario }}
                    </div>
                @endif

                {{-- Notas --}}
                @if($detalle->nota_cocina)
                    <p class="text-xs text-blue-600 mt-1">
                        📝 {{ $detalle->nota_cocina }}
                    </p>
                @endif

            </td>

            <td class="px-3 py-2 text-center text-slate-500">{{ $detalle->cantidad }}</td>
            <td class="px-3 py-2 text-right text-slate-500">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
            <td class="px-3 py-2 text-right font-semibold text-slate-700">S/ {{ number_format($detalle->subtotal, 2) }}</td>
            <td class="px-3 py-2 text-right text-xs uppercase text-slate-400">{{ str_replace('_', ' ', $detalle->estado) }}</td>
        </tr>
    @endforeach
</tbody>


  </table>
  </div>
      @if($pedido->nota_cocina)
        <p class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700">nota_cocina: {{ $pedido->nota_cocina }}</p>
      @endif
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 class="text-lg font-semibold text-slate-700">Acciones</h2>
      <div class="mt-4 flex flex-wrap gap-3 portrait:flex-col portrait:items-stretch portrait:gap-3">
  @if(in_array(auth()->user()->role, ['mozo', 'admin'], true))
          @if($pedido->estado === \App\Models\Pedido::ESTADO_PENDIENTE)
            <form method="POST" action="{{ route('pedidos.enviar', $pedido) }}">
              @csrf
              <button class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Enviar a cocina</button>
            </form>
          @endif
          @if($pedido->estado === \App\Models\Pedido::ESTADO_LISTO)
            <form method="POST" action="{{ route('pedidos.servido', $pedido) }}">
              @csrf
              <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Marcar servido</button>
            </form>
          @endif
          @if(!in_array($pedido->estado, [\App\Models\Pedido::ESTADO_ANULADO, \App\Models\Pedido::ESTADO_PAGADO], true))
            <form method="POST" action="{{ route('pedidos.anular', $pedido) }}" onsubmit="return confirm('¿Seguro que deseas anular el pedido?');">
              @csrf
              <button class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Anular pedido</button>
            </form>
            <button id="btnCambiarMesa"
  class="rounded-md bg-amber-600 px-4 py-2 text-sm text-white hover:bg-amber-700">
  Cambiar mesa
</button>

<div id="modalCambiarMesa" class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-96">
    <h3 class="text-lg font-semibold mb-4 text-slate-800">Cambiar pedido de mesa</h3>

    <form method="POST" action="{{ route('pedidos.cambiarMesa', $pedido->id) }}">
  @csrf
  <label for="nueva_mesa_id" class="block text-sm mb-2">Selecciona nueva mesa:</label>
  <select name="nueva_mesa_id" id="nueva_mesa_id" class="w-full border rounded-md p-2 mb-4">
    @foreach($mesas->where('estado', 'libre') as $mesa)
      <option value="{{ $mesa->id }}">Mesa {{ $mesa->numero }}</option>
    @endforeach
  </select>

  <div class="flex justify-end gap-2">
    <button type="button" id="btnCancelarCambio" class="px-4 py-2 bg-slate-200 rounded-md hover:bg-slate-300">Cancelar</button>
    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">Confirmar</button>
  </div>
</form>

  </div>
</div>

<script>
  const btnCambiarMesa = document.getElementById('btnCambiarMesa');
  const modalCambiarMesa = document.getElementById('modalCambiarMesa');
  const btnCancelarCambio = document.getElementById('btnCancelarCambio');

  btnCambiarMesa.addEventListener('click', () => modalCambiarMesa.classList.remove('hidden'));
  btnCancelarCambio.addEventListener('click', () => modalCambiarMesa.classList.add('hidden'));
</script>

          @endif
        @endif

  @if(in_array(auth()->user()->role, ['admin', 'caja'], true) && $pedido->estado === \App\Models\Pedido::ESTADO_SERVIDO)
          <form method="POST" action="{{ route('ventas.store') }}" class="flex flex-wrap items-center gap-2 text-sm portrait:flex-col portrait:items-stretch portrait:gap-3">
            @csrf
            <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
            <label class="text-slate-600">Pago:</label>
            <select name="tipo_pago" class="rounded border border-slate-200 px-3 py-2 text-sm portrait:w-full" required>
              <option value="efectivo">Efectivo</option>
              <option value="tarjeta">Tarjeta</option>
              <option value="yape">Yape</option>
              <option value="plin">Plin</option>
              <option value="transferencia">Transferencia</option>
            </select>
            <button class="rounded-md bg-lime-600 px-4 py-2 text-sm font-semibold text-white hover:bg-lime-700 portrait:w-full">Registrar pago</button>
          </form>
        @endif
      </div>
    </article>
  </section>

  <aside class="space-y-4">
    <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 class="text-lg font-semibold text-slate-700">Resumen</h2>
      <dl class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between">
          <dt class="text-slate-500">Subtotal</dt>
          <dd class="font-semibold text-slate-700">S/ {{ number_format($pedido->total, 2) }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-slate-500">Estado</dt>
          <dd class="font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-slate-500">Creado</dt>
          <dd class="text-slate-600">{{ $pedido->created_at?->format('d/m/Y H:i') }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-slate-500">Enviado a cocina</dt>
          <dd class="text-slate-600">{{ $pedido->enviado_a_cocina_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-slate-500">Entregado</dt>
          <dd class="text-slate-600">{{ $pedido->entregado_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        </div>
      </dl>
    </article>

    @if($pedido->venta)
      <article class="rounded-xl border border-lime-200 bg-lime-50 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-lime-700">Venta registrada</h2>
        <p class="mt-3 text-sm text-lime-700">Código: {{ $pedido->venta->codigo }}</p>
        <p class="text-sm text-lime-700">Registrado por: {{ $pedido->venta->responsable?->name ?? '—' }}</p>
        <p class="text-sm text-lime-700">Tipo pago: {{ ucfirst($pedido->venta->tipo_pago) }}</p>
      </article>
    @endif

    @if($pedido->mesas_unidas) 
    <p><strong>Mesas unidas:</strong> 
        {{ implode(', ', json_decode($pedido->mesas_unidas, true)) }}
    </p>
@else
    {{-- SI ES PEDIDO DE MESAS UNIDAS --}}
@if($pedido->mesas_unidas)
    @php
        // Convertimos JSON a array de IDs
        $ids = json_decode($pedido->mesas_unidas, true);

        // Obtenemos los números de mesa
        $nombresMesas = \App\Models\Mesa::whereIn('id', $ids)->pluck('numero')->toArray();
    @endphp

    <p>
        <strong>Mesas unidas:</strong> {{ implode(', ', $nombresMesas) }}
    </p>

{{-- SI ES PEDIDO DE UNA MESA NORMAL --}}
@elseif($pedido->mesa)
    <p>
        <strong>Mesa:</strong> {{ $pedido->mesa->numero }}
    </p>

{{-- SI NO HAY NADA --}}
@else
    <p><strong>Mesa:</strong> -</p>
@endif

@endif




    {{-- @if($pedido->mesas_unidas)
    <p><strong>Mesas Unidas:</strong> {{ implode(', ', json_decode($pedido->mesas_unidas)) }}</p>
@endif --}}

  </aside>
</div>
@endsection
