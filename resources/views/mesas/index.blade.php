@extends('layouts.app')

@section('title', 'Mesas')

@section('content')

<div class="mt-6 flex justify-between items-center">
  <h2 class="text-lg font-semibold text-slate-800">Gestión de Mesas</h2>

  <button id="btnUnirMesas"
    class="rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
    Unir mesas
  </button>
</div>

{{-- AGRUPAR MESAS COMBINADAS --}}
@php
  $grupos = $mesas->where('combinada', 1)->groupBy('combinada_grupo');
@endphp

@if ($grupos->isNotEmpty())
  <div class="mb-4 space-y-2">
    @foreach ($grupos as $grupoId => $mesasGrupo)
      @php
        $numeros = $mesasGrupo->pluck('numero')->map(fn($n) => ltrim($n, '0'))->join(', ');
      @endphp

      <div class="flex items-center justify-between bg-emerald-50 rounded-lg p-3">
        
        <div class="text-emerald-700 font-semibold">
          Mesas unidas: {{ $numeros }}
        </div>

        <div class="flex gap-2">
          {{-- Abrir pedido para el grupo --}}
          <a href="{{ route('pedidos.create', ['grupo' => $grupoId]) }}"
             class="bg-emerald-600 text-white px-3 py-1 rounded text-sm font-medium hover:bg-emerald-700">
            Abrir pedido grupo
          </a>

          {{-- Separar solo ESTE grupo --}}
          <form action="{{ route('mesas.separar', $grupoId) }}" 
                method="POST"
                onsubmit="return confirm('¿Separar estas mesas?');">
            @csrf
            <button class="bg-red-600 text-white px-3 py-1 rounded text-sm font-medium hover:bg-red-700">
              Separar mesas
            </button>
          </form>
        </div>

      </div>
    @endforeach
  </div>
@endif


{{-- GRID DE MESAS --}}
<div class="mt-6 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
  @forelse ($mesas as $mesa)
    
    @php
      $colores = [
        'libre'     => 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200',
        'ocupada'   => 'bg-amber-100 text-amber-700 hover:bg-amber-200',
        'en_cuenta' => 'bg-sky-100 text-sky-700 hover:bg-sky-200',
        'bloqueada' => 'bg-rose-100 text-rose-700 hover:bg-rose-200',
      ];
      $color = $colores[$mesa->estado] ?? 'bg-slate-100 text-slate-700 hover:bg-slate-200';
    @endphp

    <div class="relative flex flex-col items-center justify-center rounded-lg {{ $color }} p-3 text-center text-xs font-semibold shadow-sm hover:scale-105">

      <span class="text-sm font-bold">Mesa {{ $mesa->numero }}</span>
      <span class="text-[10px] mb-1">{{ ucfirst($mesa->estado) }}</span>

      @if ($mesa->combinada)
    <form action="{{ route('pedidos.create') }}" method="GET">
        <input type="hidden" name="grupo" value="{{ $mesa->combinada_grupo }}">
        <button type="submit" class="btn btn-primary">
            Abrir Pedido (Grupo {{ $mesa->combinada_grupo }})
        </button>
    </form>
@else
    <a href="{{ route('pedidos.create', ['mesa_id' => $mesa->id]) }}" class="btn btn-success">
        Abrir Pedido
    </a>
@endif


    </div>

  @empty
    <p class="text-sm text-slate-500 col-span-full text-center">No hay mesas registradas.</p>
  @endforelse
</div>


{{-- MODAL UNIR MESAS --}}
<div id="modalUnirMesas" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50">
  <div class="flex items-center justify-center w-full h-full">

    <div class="bg-white rounded-xl shadow-lg p-6 w-96">
      <h3 class="text-lg font-semibold mb-4 text-slate-800">Selecciona las mesas a unir</h3>

      <form action="{{ route('mesas.unir') }}" method="POST" id="formUnirMesas">
        @csrf

        <div class="grid grid-cols-3 gap-2 mb-4 max-h-60 overflow-y-auto">
          @foreach ($mesas->where('estado', 'libre') as $mesa)
            <label class="cursor-pointer rounded-md border border-slate-200 py-2 text-center text-sm font-medium text-slate-700 hover:bg-emerald-50">

              <input type="checkbox" name="mesas[]" value="{{ $mesa->id }}" class="peer sr-only">
              <span class="peer-checked:text-emerald-700 peer-checked:font-bold peer-checked:underline">
                Mesa {{ $mesa->numero }}
              </span>

            </label>
          @endforeach
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" id="btnCancelar" class="px-4 py-2 text-sm rounded-md bg-slate-200 hover:bg-slate-300">
            Cancelar
          </button>

          <button type="submit" id="btnConfirmar" 
                  class="px-4 py-2 text-sm rounded-md bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50"
                  disabled>
            Unir
          </button>
        </div>

      </form>
    </div>

  </div>
</div>


{{-- SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

  const modal = document.getElementById('modalUnirMesas');
  const btnUnir = document.getElementById('btnUnirMesas');
  const btnCancelar = document.getElementById('btnCancelar');
  const btnConfirmar = document.getElementById('btnConfirmar');
  const checkboxes = document.querySelectorAll('input[name="mesas[]"]');

  btnUnir.addEventListener('click', () => modal.classList.remove('hidden'));
  btnCancelar.addEventListener('click', () => modal.classList.add('hidden'));

  checkboxes.forEach(chk => {
    chk.addEventListener('change', () => {
      const seleccionadas = [...checkboxes].filter(c => c.checked);
      btnConfirmar.disabled = seleccionadas.length < 2;
    });
  });

});
</script>

@endsection
