@extends('layouts.app')

@section('title', 'Editar producto')

@section('content')
<div class="mb-6">
  <h1 class="text-2xl font-semibold text-slate-900">Editar producto</h1>
  <p class="text-sm text-slate-500">Actualiza la información del producto.</p>
</div>

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg max-w-2xl">
  <form action="{{ route('productos.update', $producto) }}" method="POST" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
      <label class="text-sm font-medium text-slate-600">Nombre</label>
      <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300" required>
    </div>

    <div>
      <label class="text-sm font-medium text-slate-600">Precio (S/)</label>
      <input type="number" name="precio" step="0.01" min="0" value="{{ old('precio', $producto->precio) }}" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300" required>
    </div>

    <div>
      <label class="text-sm font-medium text-slate-600">Categoría</label>
      @php($categoriaActual = old('categoria', $producto->categoria))
      <select name="categoria" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300" required>
        @foreach($categoriasDisponibles as $clave => $label)
          <option value="{{ $clave }}" @selected($categoriaActual===$clave)>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="text-sm font-medium text-slate-600">Descripción</label>
      <textarea name="descripcion" rows="3" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300" placeholder="Detalle opcional">{{ old('descripcion', $producto->descripcion) }}</textarea>
    </div>

    <div class="flex items-center gap-2 text-sm text-slate-600">
      <input type="checkbox" name="requiere_cocina" value="1" class="h-4 w-4 rounded border-slate-300" {{ old('requiere_cocina', $producto->requiere_cocina) ? 'checked' : '' }}>
      Requiere preparación en cocina
    </div>

    <div>
      <label class="text-sm font-medium text-slate-600">Estado</label>
      @php($estadoActual = old('estado', $producto->estado))
      <select name="estado" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300">
        @foreach($estadosDisponibles as $clave => $label)
          <option value="{{ $clave }}" @selected($estadoActual===$clave)>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div class="flex items-center gap-2">
      <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Guardar cambios</button>
      <a href="{{ route('productos.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Cancelar</a>
    </div>
  </form>
</div>
@endsection
