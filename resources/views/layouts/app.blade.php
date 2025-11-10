<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Restaurante | Panel</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-[Inter] text-slate-800">
  <div class="min-h-screen">
    <header class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        <div>
          <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-emerald-600">Restaurante POS</a>
          <p class="text-xs text-slate-500">Sistema de gestión para mozos y cocina</p>
        </div>
        @auth
        <nav class="flex flex-wrap items-center gap-2 text-sm font-medium">
          @php
            $role = auth()->user()->role;
          @endphp
          @if(in_array($role, ['admin', 'mozo', 'caja'], true))
            <a href="{{ route('mesas.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('mesas.*') ? 'bg-emerald-100 text-emerald-700' : 'hover:bg-slate-100' }}">Mesas</a>
          @endif
          @if(in_array($role, ['admin', 'mozo'], true))
            <a href="{{ route('pedidos.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('pedidos.*') ? 'bg-emerald-100 text-emerald-700' : 'hover:bg-slate-100' }}">Pedidos</a>
          @endif
          @if(in_array($role, ['admin', 'cocina'], true))
            <a href="{{ route('cocina.pedidos') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('cocina.*') ? 'bg-emerald-100 text-emerald-700' : 'hover:bg-slate-100' }}">Cocina</a>
          @endif
          @if(in_array($role, ['admin', 'caja'], true))
            <a href="{{ route('ventas.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('ventas.*') ? 'bg-emerald-100 text-emerald-700' : 'hover:bg-slate-100' }}">Ventas</a>
          @endif
          @if($role === 'admin')
            <a href="{{ route('productos.index') }}" class="px-3 py-2 rounded-md {{ request()->routeIs('productos.*') ? 'bg-emerald-100 text-emerald-700' : 'hover:bg-slate-100' }}">Productos</a>
          @endif
        </nav>
        <div class="flex items-center gap-3 text-sm">
          <div class="text-right">
            <p class="font-semibold">{{ auth()->user()->name }}</p>
            <p class="text-xs capitalize text-slate-500">{{ auth()->user()->role }}</p>
          </div>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-900">Cerrar sesión</button>
          </form>
        </div>
        @endauth
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
      @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ session('success') }}
        </div>
      @endif
      @if(session('info'))
        <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
          {{ session('info') }}
        </div>
      @endif
      @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @yield('content')
    </main>
  </div>
</body>
</html>
