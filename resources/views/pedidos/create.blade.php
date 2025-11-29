@extends('layouts.app')


@section('content')

<div class="p-4">

    <h2 class="text-xl font-bold mb-3">
        Pedido para Mesa {{ $mesa->numero ?? 'Grupo '.$grupo }}
    </h2>

    <div class="grid grid-cols-3 gap-4">

    {{-- PANEL IZQUIERDO: DETALLE DEL PEDIDO --}}
    <div class="col-span-1 bg-white p-4 rounded shadow">

        <h3 class="text-lg font-bold mb-3">Detalle del Pedido</h3>

        <div id="detallePedido">
            <p class="text-gray-500">No hay productos agregados.</p>
        </div>

    </div>

    {{-- PANEL DERECHO: CATEGORÍAS Y PRODUCTOS --}}
    <div class="col-span-2">


    {{-- BOTONES DE FILTRO --}}
    <div class="flex gap-3 mb-4">

        <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="entrada">
            ENTRADA
        </button>

        <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="menu">
            MENU
        </button>

        <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="extra">
            EXTRA
        </button>

        <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="bebida">
            BEBIDAS
        </button>

        <button class="tab-btn bg-gray-200 px-4 py-2 rounded" data-cat="ejecutivo">
            EJECUTIVOS
        </button>

    </div>

    {{-- CONTENEDOR DE PRODUCTOS --}}
    <div id="productosContainer" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <p class="text-gray-500">Seleccione una categoría.</p>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const botones = document.querySelectorAll('.tab-btn');
    const contenedor = document.getElementById('productosContainer');

    botones.forEach(btn => {
        btn.addEventListener('click', () => {

            const categoria = btn.dataset.cat;

            // MARCAR BOTÓN SELECCIONADO
            botones.forEach(b => b.classList.remove('bg-emerald-500', 'text-white'));
            btn.classList.add('bg-emerald-500', 'text-white');

            // CARGAR DATOS POR AJAX
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

                    // data.forEach(prod => {
                    //     contenedor.innerHTML += `
                    //         <div class="p-3 border rounded shadow bg-white">
                    //             <h3 class="font-bold">${prod.nombre}</h3>
                    //             <p class="text-sm text-gray-600">S/. ${prod.precio}</p>
                    //         </div>
                    //     `;
                    // });

                });

        });
    });

});

// CARRITO TEMPORAL
let detalle = {};

// CLICK EN PRODUCTO
document.addEventListener('click', function(e) {

    const item = e.target.closest('.producto-item');
    if (!item) return;

    let id = item.dataset.id;
    let nombre = item.dataset.nombre;
    let precio = parseFloat(item.dataset.precio);

    if (!detalle[id]) {
        detalle[id] = { nombre, precio, cantidad: 1 };
    } else {
        detalle[id].cantidad++;
    }

    renderDetalle();
});

// RENDERIZAR DETALLE
function renderDetalle() {
    const cont = document.getElementById('detallePedido');
    cont.innerHTML = '';

    Object.entries(detalle).forEach(([id, item]) => {
        cont.innerHTML += `
            <div class="flex justify-between items-center mb-2 border-b pb-2">

                <div>
                    <p class="font-semibold">${item.nombre}</p>
                    <p class="text-sm text-gray-600">S/. ${item.precio}</p>
                </div>

                <div class="flex items-center gap-2">

                    <button class="btnMenos bg-red-500 text-white px-2 rounded" data-id="${id}">−</button>

                    <span>${item.cantidad}</span>

                    <button class="btnMas bg-emerald-500 text-white px-2 rounded" data-id="${id}">+</button>
                </div>

            </div>
        `;
    });

    if (Object.keys(detalle).length === 0) {
        cont.innerHTML = `<p class="text-gray-500">No hay productos agregados.</p>`;
    }
}

// BOTONES + Y −
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
});


</script>

    </div> {{-- FIN PANEL DERECHO --}}
</div> {{-- FIN GRID --}}

@endsection
