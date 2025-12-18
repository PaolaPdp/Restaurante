@extends('layouts.app')

@section('content')

<div class="p-4">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">Modificar pedido #{{ $pedido->id }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('pedidos.show', $pedido) }}" class="px-3 py-2 rounded bg-slate-200 text-slate-700 text-sm">Volver</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">

        {{-- PANEL IZQUIERDO: DETALLE DEL PEDIDO --}}
        <div class="col-span-1 bg-white p-4 rounded shadow">
            <h3 class="text-lg font-bold mb-3">Detalle del Pedido</h3>

            <div id="detallePedido">
                <p class="text-gray-500">No hay productos agregados.</p>
            </div>

            <form id="pedidoForm" method="POST" action="{{ route('pedidos.update', $pedido) }}">
                @csrf
                @method('PATCH')

                <div id="inputsItems"></div>

                <div class="mt-3">
                    <label class="block text-sm text-slate-600 mb-1">Notas del pedido</label>
                    <textarea name="notas" id="notas" class="w-full border rounded p-2 text-sm" rows="3" placeholder="Notas generales para cocina">{{ old('notas', $pedido->notas) }}</textarea>
                </div>

                <button type="submit" class="mt-4 w-full bg-amber-600 text-white p-3 rounded-lg">
                    Guardar cambios
                </button>
            </form>
        </div>

        {{-- PANEL DERECHO: CATEGORÍAS Y PRODUCTOS --}}
        <div class="col-span-2">

            {{-- BOTONES DE FILTRO --}}
            <div class="flex gap-3 mb-4">
                <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="entrada">ENTRADA</button>
                <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="menu">MENU</button>
                <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="extra">EXTRA</button>
                <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="bebida">BEBIDAS</button>
                <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="ejecutivo">EJECUTIVOS</button>
            </div>

            {{-- CONTENEDOR DE PRODUCTOS --}}
            <div id="productosContainer" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <p class="text-gray-500">Seleccione una categoría para agregar productos.</p>
            </div>
        </div>

    </div>
</div>

{{-- ===================================================== --}}
{{--                     MODAL DETALLES                    --}}
{{-- ===================================================== --}}
<div id="modalDetalles" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white w-96 p-5 rounded shadow-lg">
        <h2 class="text-xl font-bold mb-3">Detalles del producto</h2>

        <textarea name="detalle" id="detalle" class="w-full border p-2 rounded" rows="4"
            placeholder="Ejemplo: sin crema, sin cebolla, papas fritas, etc."></textarea>

        <div class="flex justify-end gap-3 mt-4">
            <button id="cerrarModal" class="px-4 py-2 bg-gray-500 text-white rounded">Cerrar</button>
            <button id="guardarDetalle" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
        </div>
    </div>
    </div>

{{-- ===================================================== --}}
{{--                        JAVASCRIPT                     --}}
{{-- ===================================================== --}}
<script>
// Estructura compartida con create.blade: objeto 'detalle'
let detalle = {};
let productoSeleccionado = null;

// Precargar con datos del pedido
@php
    $prefill = $pedido->detalles->mapWithKeys(function($d){
        return [
            $d->producto_id => [
                'nombre' => $d->producto->nombre ?? ('Producto #' . $d->producto_id),
                'precio' => (float) ($d->precio_unitario),
                'cantidad' => (int) $d->cantidad,
                'descripcion' => $d->nota_cocina,
            ]
        ];
    });
@endphp
detalle = {!! json_encode($prefill, JSON_UNESCAPED_UNICODE) !!};

document.addEventListener('DOMContentLoaded', () => {
    const botones = document.querySelectorAll('.tab-btn');
    const contenedor = document.getElementById('productosContainer');

    botones.forEach(btn => {
        btn.addEventListener('click', () => {
            const categoria = btn.dataset.cat;

            botones.forEach(b => b.classList.remove('bg-emerald-500', 'text-white'));
            btn.classList.add('bg-emerald-500', 'text-white');

            fetch(`/productos/por-categoria/${categoria}`)
                .then(res => res.json())
                .then(data => {
                    contenedor.innerHTML = '';
                    if (data.length === 0) {
                        contenedor.innerHTML = `<p>No hay productos.</p>`;
                        return;
                    }
                    data.forEach(prod => {
                        contenedor.innerHTML += `
                            <div class="p-3 border rounded shadow bg-white cursor-pointer producto-item"
                                data-id="${prod.id}"
                                data-nombre="${prod.nombre}"
                                data-precio="${prod.precio}">
                                <h3 class="font-bold">${prod.nombre}</h3>
                                <p class="text-sm text-gray-600">S/. ${prod.precio}</p>
                            </div>
                        `;
                    });
                });
        });
    });

    // Render inicial
    renderDetalle();
});

// Click en producto para agregar
document.addEventListener('click', function(e) {
    const item = e.target.closest('.producto-item');
    if (!item) return;

    let id = item.dataset.id;
    let nombre = item.dataset.nombre;
    let precio = parseFloat(item.dataset.precio);

    if (!detalle[id]) {
        detalle[id] = { nombre, precio, cantidad: 1, descripcion: "" };
    } else {
        detalle[id].cantidad++;
    }

    renderDetalle();
});

// Enviar al backend (construye inputs items[..])
document.getElementById('pedidoForm').addEventListener('submit', function () {
    const inputsContainer = document.getElementById('inputsItems');
    inputsContainer.innerHTML = '';

    Object.keys(detalle).forEach(productoId => {
        const item = detalle[productoId];

        const inputCant = document.createElement('input');
        inputCant.type = 'hidden';
        inputCant.name = `items[${productoId}][cantidad]`;
        inputCant.value = item.cantidad;
        inputsContainer.appendChild(inputCant);

        const inputDesc = document.createElement('input');
        inputDesc.type = 'hidden';
        inputDesc.name = `items[${productoId}][descripcion]`;
        inputDesc.value = item.descripcion ?? "";
        inputsContainer.appendChild(inputDesc);
    });
});

// Render del detalle del pedido
function renderDetalle() {
    const cont = document.getElementById('detallePedido');
    cont.innerHTML = '';

    Object.entries(detalle).forEach(([id, item]) => {
        cont.innerHTML += `
            <div class="mb-3 pb-2 border-b">
                <div class="flex justify-between">
                    <div>
                        <p class="font-semibold">${item.nombre}</p>
                        <p class="text-sm text-gray-600">S/. ${item.precio}</p>
                        ${(item.descripcion ?? "") !== ""
                            ? `<p class='text-xs text-blue-600 mt-1'>${item.descripcion}</p>`
                            : ''}
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="btnMenos bg-red-500 text-white px-2 rounded" data-id="${id}">−</button>
                        <span>${item.cantidad}</span>
                        <button class="btnMas bg-emerald-500 text-white px-2 rounded" data-id="${id}">+</button>
                        <button class="btnDetalles bg-blue-500 text-white px-2 rounded" data-id="${id}">Detalles</button>
                        <button class="btnQuitar bg-slate-300 text-slate-800 px-2 rounded" data-id="${id}">Quitar</button>
                    </div>
                </div>
            </div>
        `;
    });

    if (Object.keys(detalle).length === 0) {
        cont.innerHTML = `<p class="text-gray-500">No hay productos agregados.</p>`;
    }
}

// Botones + − detalles
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btnMas')) {
        let id = e.target.dataset.id;
        detalle[id].cantidad++;
        renderDetalle();
    }
    if (e.target.classList.contains('btnMenos')) {
        let id = e.target.dataset.id;
        detalle[id].cantidad--;
        if (detalle[id].cantidad <= 0) delete detalle[id];
        renderDetalle();
    }
    if (e.target.classList.contains('btnQuitar')) {
        let id = e.target.dataset.id;
        delete detalle[id];
        renderDetalle();
    }
    if (e.target.classList.contains('btnDetalles')) {
        productoSeleccionado = e.target.dataset.id;
        document.getElementById('detalle').value = detalle[productoSeleccionado].descripcion ?? "";
        document.getElementById('modalDetalles').classList.remove('hidden');
    }
});

// Guardar detalle del modal
document.addEventListener('click', function(e) {
    if (e.target.id === 'guardarDetalle') {
        let texto = document.getElementById('detalle').value.trim();
        detalle[productoSeleccionado].descripcion = texto;
        document.getElementById('modalDetalles').classList.add('hidden');
        renderDetalle();
    }
});

// Cerrar modal
document.getElementById('cerrarModal').addEventListener('click', () => {
    document.getElementById('modalDetalles').classList.add('hidden');
});
</script>

@endsection
