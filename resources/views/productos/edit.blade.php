@extends('layouts.app')

@section('title', 'Editar producto')

@section('content')
<div class="space-y-1 mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">
        Editar producto
    </h1>
    <p class="text-sm text-slate-500">
        Actualiza la información del producto.
    </p>
</div>

<div class="mx-auto w-full max-w-md sm:max-w-lg lg:max-w-2xl rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
    <form action="{{ route('productos.update', $producto) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">
                Nombre
            </label>
            <input
                type="text"
                name="nombre"
                value="{{ old('nombre', $producto->nombre) }}"
                required
                class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-300"
            >
        </div>

        {{-- Precio --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">
                Precio (S/)
            </label>
            <input
                type="number"
                name="precio"
                step="0.01"
                min="0"
                value="{{ old('precio', $producto->precio) }}"
                required
                class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-300"
            >
        </div>

        {{-- Categoría --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">
                Categoría
            </label>
            @php($categoriaActual = old('categoria', $producto->categoria))
            <select
                name="categoria"
                required
                class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-300"
            >
                @foreach($categoriasDisponibles as $clave => $label)
                    <option value="{{ $clave }}" @selected($categoriaActual === $clave)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Descripción --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">
                Descripción
            </label>
            <textarea
                name="descripcion"
                rows="3"
                class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-300"
            >{{ old('descripcion', $producto->descripcion) }}</textarea>
        </div>

        

        {{-- Estado --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">
                Estado
            </label>
            @php($estadoActual = old('estado', $producto->estado))
            <select
                name="estado"
                class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-300"
            >
                @foreach($estadosDisponibles as $clave => $label)
                    <option value="{{ $clave }}" @selected($estadoActual === $clave)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Acciones --}}
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button
                type="submit"
                class="w-full sm:w-auto rounded-md bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
            >
                Guardar cambios
            </button>

            <a
                href="{{ route('productos.index') }}"
                class="w-full sm:w-auto text-center text-sm font-semibold text-slate-600 hover:underline py-2"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
