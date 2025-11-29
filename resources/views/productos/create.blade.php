@extends('layouts.app')

@section('title', 'Nuevo producto')

@section('content')
<div class="mb-6">
  <h1 class="text-2xl font-semibold text-slate-900">Nuevo producto</h1>
  <p class="text-sm text-slate-500">Completa los datos para registrar un producto.</p>
</div>

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg max-w-2xl">
  <form action="{{ route('productos.store') }}" method="POST" class="space-y-4">
    @csrf

    <div>
      <label class="text-sm font-medium text-slate-600">Nombre</label>
      <input type="text" name="nombre" value="{{ old('nombre') }}" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300" required>
    </div>

    <div>
      <label class="text-sm font-medium text-slate-600">Precio (S/)</label>
      <input type="number" name="precio" step="0.01" min="0" value="{{ old('precio') }}" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300" required>
    </div>

    <div>
      <label class="text-sm font-medium text-slate-600">Categoría</label>
      <select name="categoria" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300" required>
        <option value="">Selecciona</option>
        @foreach($categoriasDisponibles as $clave => $label)
          <option value="{{ $clave }}" @selected(old('categoria')===$clave)>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="text-sm font-medium text-slate-600">Descripción</label>
      <textarea name="descripcion" rows="3" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300" placeholder="Detalle opcional">{{ old('descripcion') }}</textarea>
    </div>

    <div class="flex items-center gap-2 text-sm text-slate-600">
      <input type="checkbox" name="requiere_cocina" value="1" class="h-4 w-4 rounded border-slate-300" {{ old('requiere_cocina', true) ? 'checked' : '' }}>
      Requiere preparación en cocina
    </div>

    <div>
      <label class="text-sm font-medium text-slate-600">Estado</label>
      @php($estadoSeleccionado = old('estado', \App\Models\Producto::ESTADO_ACTIVO))
      <select name="estado" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300">
        @foreach($estadosDisponibles as $clave => $label)
          <option value="{{ $clave }}" @selected($estadoSeleccionado===$clave)>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div class="flex items-center gap-2">
      <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Crear</button>
      <a href="{{ route('productos.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Cancelar</a>
    </div>
  </form>
</div>
@endsection
