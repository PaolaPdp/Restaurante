<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Iniciar sesión | Restaurante</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet"> -->
     @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-emerald-100 via-white to-slate-100 font-sans text-slate-800">
    <div class="relative w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="grid gap-0 md:grid-cols-2">
            <section class="hidden bg-emerald-600/95 p-10 text-white md:flex md:flex-col md:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs uppercase tracking-widest">Sistema POS</span>
                    <h1 class="mt-6 text-3xl font-bold leading-tight">Gestiona tu restaurante con rapidez y estilo</h1>
                    <p class="mt-4 text-sm text-emerald-50">Actualiza pedidos, mesas y ventas en un flujo centralizado diseñado para mozos, cocina y caja.</p>
                </div>
                <div class="space-y-3 text-sm text-emerald-100">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-xs font-semibold">1</span>
                        Revisa las mesas activas y crea pedidos en segundos.
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-xs font-semibold">2</span>
                        Envía órdenes a cocina y sigue el avance en tiempo real.
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-xs font-semibold">3</span>
                        Cierra tickets y registra ventas con diferentes medios de pago.
                    </div>
                </div>
                <footer class="text-xs text-emerald-200">© {{ date('Y') }} Restaurante POS. Todos los derechos reservados.</footer>
            </section>
            <section class="flex flex-col justify-center gap-8 px-8 py-12 md:px-12">
                <header class="space-y-2 text-center md:text-left">
                    <span class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-500">Bienvenido</span>
                    <h2 class="text-2xl font-bold text-slate-800">Inicia sesión en tu cuenta</h2>
                    <p class="text-sm text-slate-500">Autentícate con tu correo institucional para acceder al panel.</p>
                </header>
                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-600" for="email">Correo electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-600" for="password">Contraseña</label>
                        <input id="password" type="password" name="password" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-500">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500">
                        Mantener sesión iniciada
                    </label>
                    @if($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <button class="w-full rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
                        Ingresar
                    </button>
                </form>
                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 text-xs text-slate-500 shadow-sm">
                    <p class="font-semibold text-slate-600">Usuarios demo</p>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        <p><span class="font-medium text-slate-700">Admin:</span> admin@restaurante.test / admin123</p>
                        <p><span class="font-medium text-slate-700">Mozo:</span> mozo@restaurante.test / mozo123</p>
                        <p><span class="font-medium text-slate-700">Cocina:</span> cocina@restaurante.test / cocina123</p>
                        <p><span class="font-medium text-slate-700">Caja:</span> caja@restaurante.test / caja123</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
