@extends('layouts.app')

@section('title', 'Nuevo pedido')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-4">
  <div>
    <h1 class="text-2xl font-semibold text-slate-800">Nuevo pedido</h1>
    <p class="text-sm text-slate-500">Selecciona la mesa y los productos para registrar el pedido.</p>
  </div>
  <a href="{{ route('pedidos.index') }}" class="rounded-md bg-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-300">Volver a pedidos</a>
</div>

<form method="POST" action="{{ route('pedidos.store') }}" class="mt-6 space-y-6">
  @csrf
  <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-700">Datos generales</h2>
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
      <div>
        <label class="text-sm font-medium text-slate-600">Mesa</label>
        <select name="mesa_id" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm" required>
          <option value="">Selecciona una mesa</option>
          @foreach($mesas as $opcionMesa)
            <option value="{{ $opcionMesa->id }}" @selected(old('mesa_id', $mesa?->id) == $opcionMesa->id)>
              Mesa {{ $opcionMesa->numero }} ({{ $opcionMesa->capacidad }} pax)
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-sm font-medium text-slate-600">Notas para cocina</label>
        <textarea name="notas" rows="3" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm" placeholder="Ej: sin cebolla, poco picante">{{ old('notas') }}</textarea>
      </div>
    </div>
    <label class="mt-4 inline-flex items-center gap-2 text-sm text-slate-600">
      <input type="checkbox" name="enviar_a_cocina" value="1" class="h-4 w-4 rounded border-slate-300" {{ old('enviar_a_cocina', true) ? 'checked' : '' }}>
      Enviar automáticamente a cocina
    </label>
  </section>

  <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-700">Productos</h2>
    <p class="text-sm text-slate-500">Ingresa las cantidades para cada categoría.</p>

    <div class="mt-6 space-y-6">
      @foreach($productos as $categoria => $lista)
        @php
          $titulo = [
            'entrada' => 'Entradas',
            'menu' => 'Menú',
            'extra' => 'Extras',
            'bebida' => 'Bebidas',
          ][$categoria] ?? ucfirst($categoria);
        @endphp
        <div>
          <h3 class="text-base font-semibold text-slate-700">{{ $titulo }}</h3>
          <div class="mt-3 grid gap-3 lg:grid-cols-2">
            @foreach($lista as $producto)
              <article class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                <div>
                  <p class="text-sm font-medium text-slate-700">{{ $producto->nombre }}</p>
                  <p class="text-xs text-slate-500">S/ {{ number_format($producto->precio, 2) }}</p>
                </div>
                <input type="number" name="items[{{ $producto->id }}]" min="0" value="{{ old('items.' . $producto->id, 0) }}" class="w-20 rounded border border-slate-200 px-2 py-1 text-sm text-right">
              </article>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <div class="flex justify-end gap-3">
    <a href="{{ route('dashboard') }}" class="rounded-md bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">Cancelar</a>
    <button class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Registrar pedido</button>
  </div>
</form>
@endsection
