<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;

class CajaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::whereHas('pedidos', function ($q) {
                $q->whereNotIn('estado', [
                    Pedido::ESTADO_PAGADO,
                    Pedido::ESTADO_ANULADO
                ]);
            })
            ->with(['pedidos' => function ($q) {
                $q->whereNotIn('estado', [
                    Pedido::ESTADO_PAGADO,
                    Pedido::ESTADO_ANULADO
                ]);
            }])
            ->orderBy('numero')
            ->get();

        return view('caja.index', compact('mesas'));
    }
}
