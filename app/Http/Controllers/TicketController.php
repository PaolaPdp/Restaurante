<?php

namespace App\Http\Controllers;

use App\Models\Pedido;

class TicketController extends Controller
{
    public function show(Pedido $pedido)
    {
        $pedido->load(['mesa', 'mozo', 'detalles.producto']);

        return view('tickets.show', compact('pedido'));
    }
}
