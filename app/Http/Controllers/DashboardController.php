<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
{
    $user = $request->user();

    if ($user->role === 'cocina') {
        return redirect()->route('cocina.pedidos');
    }

    if ($user->role === 'caja') {
        return redirect()->route('caja.index');
    }

    // 🔵 MOZO → SOLO MESAS
    if ($user->role === 'mozo') {

        $mesas = Mesa::with(['pedidos' => function ($q) use ($user) {
                $q->abiertos()
                  ->where('usuario_id', $user->id);
            }])
            ->orderBy('numero')
            ->get();

        return view('dashboard_mozo', compact('mesas'));
    }

    // 🟢 ADMIN → TODO
    $mesas = Mesa::with(['pedidos' => fn ($q) => $q->abiertos()])
        ->orderBy('numero')
        ->get();

    $pedidos = Pedido::with(['mesa', 'mozo'])
        ->abiertos()
        ->latest()
        ->take(10)
        ->get();

    $stats = [
        'total_mesas' => Mesa::count(),
        'libres' => Mesa::where('estado', Mesa::ESTADO_LIBRE)->count(),
        'ocupadas' => Mesa::where('estado', Mesa::ESTADO_OCUPADA)->count(),
        'en_cuenta' => Mesa::where('estado', Mesa::ESTADO_CUENTA)->count(),
        'ocupacion' => Mesa::count() > 0
            ? round(((Mesa::whereIn('estado', [
                Mesa::ESTADO_OCUPADA,
                Mesa::ESTADO_CUENTA,
            ])->count()) / Mesa::count()) * 100)
            : 0,
        'pedidos_activos' => Pedido::abiertos()->count(),
    ];

    return view('dashboard', compact('mesas', 'pedidos', 'stats'));
}

}
