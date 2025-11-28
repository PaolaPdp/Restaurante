@extends('layouts.app')


@section('content')

<div class="p-4">

    <h2 class="text-xl font-bold mb-3">
        Pedido para Mesa {{ $mesa->numero ?? 'Grupo '.$grupo }}
    </h2>

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
                            <div class="p-3 border rounded shadow bg-white">
                                <h3 class="font-bold">${prod.nombre}</h3>
                                <p class="text-sm text-gray-600">S/. ${prod.precio}</p>
                            </div>
                        `;
                    });

                });

        });
    });

});
</script>

@endsection
