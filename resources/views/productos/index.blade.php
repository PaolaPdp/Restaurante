@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Catálogo de productos</h1>
        <p class="text-sm text-slate-500">Administra tus platos, bebidas y complementos.</p>
    </div>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-400">Total</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500">Productos cargados</p>
    </article>
    <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-emerald-700">Activos</p>
        <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $stats['activos'] }}</p>
        <p class="text-xs text-emerald-600">Disponibles en carta</p>
    </article>
    <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-amber-700">Requieren cocina</p>
        <p class="mt-2 text-2xl font-semibold text-amber-700">{{ $stats['requiere_cocina'] }}</p>
        <p class="text-xs text-amber-600">Platos con preparación</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">Inactivos</p>
        <p class="mt-2 text-2xl font-semibold text-slate-700">{{ $stats['inactivos'] }}</p>
        <p class="text-xs text-slate-500">Ocultos del catálogo</p>
    </article>
</div>

@if($categorias->isNotEmpty())
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-400">Distribución por categoría</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($categorias as $categoria)
                @php
                    $label = [
                        'entrada' => 'Entradas',
                        'menu' => 'Menú',
                        'extra' => 'Extras',
                        'bebida' => 'Bebidas',
                    ][$categoria->categoria] ?? ucfirst($categoria->categoria);
                @endphp
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                    <span class="font-semibold text-slate-700">{{ $label }}</span>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500">{{ $categoria->total }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <form action="{{ route('productos.store') }}" method="POST" class="lg:col-span-1 space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
        @csrf
        <h2 class="text-lg font-semibold text-slate-900">Agregar producto</h2>
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
                <option value="entrada">Entrada</option>
                <option value="menu">Menú</option>
                <option value="extra">Extra</option>
                <option value="bebida">Bebida</option>
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
            <select name="estado" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-emerald-300">
                <option value="activo" selected>Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
        <button class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Guardar</button>
    </form>

    <section class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
        <h2 class="text-lg font-semibold text-slate-700">Listado</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Producto</th>
                        <th class="px-3 py-2 text-left">Categoría</th>
                        <th class="px-3 py-2 text-right">Precio</th>
                        <th class="px-3 py-2 text-left">Estado</th>
                        <th class="px-3 py-2 text-left">Actualización</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productos as $producto)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2">
                                <p class="font-semibold text-slate-700">{{ $producto->nombre }}</p>
                                <p class="text-xs text-slate-400">{{ $producto->descripcion ?: 'Sin descripción' }}</p>
                            </td>
                            <td class="px-3 py-2 text-slate-500 capitalize">{{ $producto->categoria }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-slate-700">S/ {{ number_format($producto->precio, 2) }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $producto->estado === 'activo' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ ucfirst($producto->estado) }}</span>
                            </td>
                            <td class="px-3 py-2 text-slate-500 text-xs">{{ $producto->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">No hay productos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $productos->links() }}
        </div>
    </section>
</div>
@endsection

