@extends('layouts.app')

@section('title', 'Detalle de producto')

@section('content')
<div class="mb-6 flex items-center justify-between">
  <div>
    <h1 class="text-2xl font-semibold text-slate-900">{{ $producto->nombre }}</h1>
    <p class="text-sm text-slate-500">{{ $producto->descripcion ?: 'Sin descripción' }}</p>
  </div>
  <a href="{{ route('productos.edit', $producto) }}" class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Editar</a>
</div>

<div class="grid gap-6 md:grid-cols-2">
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-sm font-semibold uppercase tracking-widest text-slate-400">Información</h2>
    <dl class="mt-4 space-y-2 text-sm">
      <div class="flex justify-between">
        <dt class="text-slate-500">Categoría</dt>
        <dd class="font-medium text-slate-700">{{ $categoriasDisponibles[$producto->categoria] ?? ucfirst($producto->categoria) }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Precio</dt>
        <dd class="font-medium text-slate-700">S/ {{ number_format($producto->precio, 2) }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Requiere cocina</dt>
        <dd class="font-medium text-slate-700">{{ $producto->requiere_cocina ? 'Sí' : 'No' }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Estado</dt>
        <dd class="font-medium text-slate-700">{{ $estadosDisponibles[$producto->estado] ?? ucfirst($producto->estado) }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Actualizado</dt>
        <dd class="font-medium text-slate-700">{{ $producto->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
      </div>
    </dl>
  </div>
</div>

<div class="mt-6">
  <a href="{{ route('productos.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Volver al listado</a>
</div>
@endsection
