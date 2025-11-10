@extends('layouts.app')

@section('title', 'Registrar Mesa')

@section('content')
<div class="max-w-lg mx-auto mt-8 bg-white p-6 rounded-2xl shadow-sm">
  <h1 class="text-2xl font-semibold text-slate-800 mb-4">Registrar nueva mesa</h1>

  @if ($errors->any())
    <div class="mb-4 p-3 bg-rose-100 text-rose-700 rounded-md">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('mesas.store') }}" method="POST" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium text-slate-600">Número de mesa</label>
      <input type="number" name="numero" value="{{ old('numero') }}" required
        class="w-full mt-1 border border-slate-300 rounded-lg px-3 py-2 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600">Capacidad</label>
      <input type="number" name="capacidad" value="{{ old('capacidad') }}" required
        class="w-full mt-1 border border-slate-300 rounded-lg px-3 py-2 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600">Estado</label>
      <select name="estado" class="w-full mt-1 border border-slate-300 rounded-lg px-3 py-2 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
        <option value="libre">Libre</option>
        <option value="ocupada">Ocupada</option>
        <option value="en_cuenta">En cuenta</option>
        <option value="bloqueada">Bloqueada</option>
      </select>
    </div>

    <button type="submit" class="w-full bg-emerald-600 text-white py-2 rounded-lg font-semibold hover:bg-emerald-700 transition">
      Guardar Mesa
    </button>
  </form>
</div>
@endsection
