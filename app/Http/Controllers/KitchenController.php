<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KitchenController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with(['mesa', 'mozo', 'detalles.producto'])
            ->whereIn('estado', [
                Pedido::ESTADO_PENDIENTE,
                Pedido::ESTADO_EN_COCINA,
            ])
            ->orderByRaw('CASE WHEN estado = ? THEN 0 ELSE 1 END', [Pedido::ESTADO_EN_COCINA])
            ->orderBy('enviado_a_cocina_at')
            ->orderBy('created_at')
            ->get();

        $stats = [
            'pendientes' => Pedido::where('estado', Pedido::ESTADO_PENDIENTE)->count(),
            'en_cocina' => Pedido::where('estado', Pedido::ESTADO_EN_COCINA)->count(),
            'detalles_en_preparacion' => DetallePedido::where('estado', DetallePedido::ESTADO_EN_PREPARACION)->count(),
        ];

        $stats['promedio_espera'] = $pedidos->filter(fn ($pedido) => $pedido->enviado_a_cocina_at)
            ->avg(fn ($pedido) => $pedido->enviado_a_cocina_at->diffInMinutes(now()));

        $stats['promedio_espera'] = $stats['promedio_espera'] ? round($stats['promedio_espera']) : 0;

        return view('cocina.index', compact('pedidos', 'stats'));
    }

    public function actualizarDetalle(Request $request, DetallePedido $detalle)
    {
        $request->validate([
            'estado' => ['required', 'in:' . implode(',', [
                DetallePedido::ESTADO_EN_PREPARACION,
                DetallePedido::ESTADO_LISTO,
            ])],
            'nota' => ['nullable', 'string', 'max:500'],
        ]);

        $detalle->update([
            'estado' => $request->input('estado'),
            'nota_cocina' => $request->filled('nota') ? $request->input('nota') : null,
        ]);

        if ($detalle->pedido->detalles()->whereNotIn('estado', [DetallePedido::ESTADO_LISTO, DetallePedido::ESTADO_ANULADO])->doesntExist()) {
            $detalle->pedido->update([
                'estado' => Pedido::ESTADO_LISTO,
                'entregado_at' => null,
            ]);
        } elseif ($detalle->pedido->estado === Pedido::ESTADO_PENDIENTE) {
            $detalle->pedido->update([
                'estado' => Pedido::ESTADO_EN_COCINA,
                'enviado_a_cocina_at' => Carbon::now(),
            ]);
        }

        return back()->with('success', 'Detalle actualizado.');
    }

    public function marcarPedidoListo(Pedido $pedido)
    {
        if (!in_array($pedido->estado, [Pedido::ESTADO_EN_COCINA, Pedido::ESTADO_PENDIENTE], true)) {
            return back()->with('info', 'El pedido ya fue marcado como listo.');
        }

        $pedido->detalles()->update(['estado' => DetallePedido::ESTADO_LISTO]);
        $pedido->update([
            'estado' => Pedido::ESTADO_LISTO,
            'entregado_at' => null,
        ]);

        return back()->with('success', 'Pedido marcado como listo para servir.');
    }
}
