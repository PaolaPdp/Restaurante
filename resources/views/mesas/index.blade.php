@extends('layouts.app')

@section('title', 'Mesas')

@section('content')
<div class="mt-6 flex justify-between items-center">
  <h2 class="text-lg font-semibold text-slate-800">Gestión de Mesas</h2>

  {{-- Botón para abrir el modal --}}
  <button id="btnUnirMesas"
    class="rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
    Unir mesas
  </button>
</div>

{{-- === GRID DE MESAS === --}}
<div class="mt-6 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
  @forelse($mesas as $mesa)
    @php
      $colores = [
        'libre' => 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200',
        'ocupada' => 'bg-amber-100 text-amber-700 hover:bg-amber-200',
        'en_cuenta' => 'bg-sky-100 text-sky-700 hover:bg-sky-200',
        'bloqueada' => 'bg-rose-100 text-rose-700 hover:bg-rose-200',
      ];
      $color = $colores[$mesa->estado] ?? 'bg-slate-100 text-slate-700 hover:bg-slate-200';
    @endphp

    <a href="{{ route('pedidos.create', ['mesa_id' => $mesa->id]) }}"
      class="flex flex-col items-center justify-center rounded-lg {{ $color }} p-3 text-center text-xs font-semibold shadow-sm transition-all hover:scale-105">
      <span class="text-sm font-bold">Mesa {{ $mesa->numero }}</span>
      <span class="text-[10px]">{{ ucfirst($mesa->estado) }}</span>
    </a>
  @empty
    <p class="text-sm text-slate-500 col-span-full text-center">No hay mesas registradas todavía.</p>
  @endforelse
</div>

{{-- === MODAL UNIR MESAS === --}}
<div id="modalUnirMesas" class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg p-6 w-96">
    <h3 class="text-lg font-semibold mb-4 text-slate-800">Selecciona las mesas a unir</h3>

    <form action="{{ route('mesas.unir') }}" method="POST" id="formUnirMesas">
      @csrf
      <div class="grid grid-cols-3 gap-2 mb-4 max-h-60 overflow-y-auto">
        @foreach($mesas->where('estado', 'libre') as $mesa)
          <label class="cursor-pointer rounded-md border border-slate-200 py-2 text-center text-sm font-medium text-slate-700 hover:bg-emerald-50 transition">
            <input type="checkbox" name="mesas[]" value="{{ $mesa->id }}" class="hidden peer">
            <span class="peer-checked:text-emerald-700 peer-checked:font-bold peer-checked:underline">Mesa {{ $mesa->numero }}</span>
          </label>
        @endforeach
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" id="btnCancelar" class="px-4 py-2 text-sm rounded-md bg-slate-200 hover:bg-slate-300">Cancelar</button>
        <button type="submit" class="px-4 py-2 text-sm rounded-md bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50" id="btnConfirmar" disabled>Unir</button>
      </div>
    </form>
  </div>
</div>

{{-- === SCRIPT === --}}
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
        const seleccionadas = Array.from(checkboxes).filter(c => c.checked);
        btnConfirmar.disabled = seleccionadas.length < 2; // mínimo 2 mesas
      });
    });
  });
</script>
@endsection
