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


        $mesas = Mesa::with(['pedidos' => function ($query) use ($user) {
            $query->abiertos()->latest();
        }])->orderBy('numero')->get();

        $pedidos = Pedido::with(['mesa', 'mozo'])
            ->abiertos()
            ->when($user->role === 'mozo', fn ($query) => $query->where('usuario_id', $user->id))
            ->latest()
            ->take(10)
            ->get();

        $totalMesas = Mesa::count();
        $mesasLibres = Mesa::where('estado', Mesa::ESTADO_LIBRE)->count();
        $mesasOcupadas = Mesa::where('estado', Mesa::ESTADO_OCUPADA)->count();
        $mesasEnCuenta = Mesa::where('estado', Mesa::ESTADO_CUENTA)->count();

        $stats = [
            'total_mesas' => $totalMesas,
            'libres' => $mesasLibres,
            'ocupadas' => $mesasOcupadas,
            'en_cuenta' => $mesasEnCuenta,
            'ocupacion' => $totalMesas > 0 ? round((($mesasOcupadas + $mesasEnCuenta) / $totalMesas) * 100) : 0,
            'pedidos_activos' => Pedido::abiertos()->count(),
            'cocina_en_preparacion' => Pedido::where('estado', Pedido::ESTADO_EN_COCINA)->count(),
            'ventas_hoy' => Venta::whereDate('fecha', Carbon::today())->sum('total'),
        ];

        return view('dashboard', compact('mesas', 'pedidos', 'stats'));
    }
}
