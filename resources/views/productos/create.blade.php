@extends('layouts.app')

@section('title', 'Nuevo producto')

@section('content')
<div class="space-y-1 mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">
        Nuevo producto
    </h1>
    <p class="text-sm text-slate-500">
        Completa los datos para registrar un producto.
    </p>
</div>

<div class="mx-auto w-full max-w-sm sm:max-w-md lg:max-w-lg rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">

    <form action="{{ route('productos.store') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Nombre --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">
                Nombre
            </label>
            <input
                type="text"
                name="nombre"
                value="{{ old('nombre') }}"
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
                value="{{ old('precio') }}"
                required
                class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-300"
            >
        </div>

        {{-- Categoría --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">
                Categoría
            </label>
            <select
                name="categoria"
                required
                class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-300"
            >
                <option value="">Selecciona</option>
                @foreach($categoriasDisponibles as $clave => $label)
                    <option value="{{ $clave }}" @selected(old('categoria') === $clave)>
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
                placeholder="Detalle opcional"
            >{{ old('descripcion') }}</textarea>
        </div>

        {{-- Cocina --}}
        <label class="flex items-center gap-3 text-sm text-slate-600">
            <input
                type="checkbox"
                name="requiere_cocina"
                value="1"
                class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                {{ old('requiere_cocina', true) ? 'checked' : '' }}
            >
            Requiere preparación en cocina
        </label>

        {{-- Estado --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">
                Estado
            </label>
            @php($estadoSeleccionado = old('estado', \App\Models\Producto::ESTADO_ACTIVO))
            <select
                name="estado"
                class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-300"
            >
                @foreach($estadosDisponibles as $clave => $label)
                    <option value="{{ $clave }}" @selected($estadoSeleccionado === $clave)>
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
                Crear producto
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
