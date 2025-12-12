<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ticket pedido #{{ $pedido->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            body { font-size: 12px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 py-6">
    <div class="mx-auto w-full max-w-sm rounded-xl bg-white p-6 shadow-lg">
        <header class="mb-4 text-center">
            <h1 class="text-lg font-semibold text-slate-800">Restaurante POS</h1>
            <p class="text-xs text-slate-500">Ticket de pedido</p>
        </header>

        <section class="space-y-1 text-sm text-slate-600">
            <p><span class="font-semibold">Pedido:</span> #{{ $pedido->id }}</p>
            <p><span class="font-semibold">Mesa:</span> {{ $pedido->mesa?->numero ?? 'para llevar' }}</p>
            <p><span class="font-semibold">Mozo:</span> {{ $pedido->mozo?->name ?? '—' }}</p>
            <p><span class="font-semibold">Fecha:</span> {{ $pedido->created_at?->format('d/m/Y H:i') }}</p>
        </section>

        <table class="mt-4 w-full text-left text-sm text-slate-700">
            <thead>
                <tr class="border-b border-slate-200 text-xs uppercase text-slate-400">
                    <th class="py-2">Producto</th>
                    <th class="py-2 text-center">Cant.</th>
                    <th class="py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tbody>
    @foreach($pedido->detalles as $detalle)
        <tr class="border-b border-slate-100">
            <td class="py-2">
                {{ $detalle->producto->nombre ?? 'Producto' }}

                {{-- Nota del producto (nota_cocina) --}}
                @if($detalle->nota_cocina)
                    <div class="text-xs text-amber-700 mt-1">
                        → {{ $detalle->nota_cocina }}
                    </div>
                @endif
            </td>

            <td class="py-2 text-center">
                {{ $detalle->cantidad }}
            </td>

            <td class="py-2 text-right">
                S/ {{ number_format($detalle->subtotal, 2) }}
            </td>
            
        </tr>

        
    @endforeach
</tbody>

            </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="py-2 text-right font-semibold">Total</td>
                            <td class="py-2 text-right font-semibold">S/ {{ number_format($pedido->total, 2) }}</td>
                        </tr>
                    </tfoot>
                 
            </table>

                   

        <button onclick="window.print()" class="no-print mt-6 w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Imprimir</button>
    </div>
</body>
</html>
