<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesa;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

class MesaController extends Controller
{
    public function create()
    {
        return view('mesas.create');
    }

    public function index()
    {
        $mesas = Mesa::with(['pedidos' => fn ($query) => $query->abiertos()->latest()])
            ->orderBy('numero')
            ->get();
            $mesas = Mesa::query()->orderBy('numero')->get();


        $stats = [
            'total' => Mesa::count(),
            'libres' => Mesa::where('estado', Mesa::ESTADO_LIBRE)->count(),
            'ocupadas' => Mesa::where('estado', Mesa::ESTADO_OCUPADA)->count(),
            'en_cuenta' => Mesa::where('estado', Mesa::ESTADO_CUENTA)->count(),
            'bloqueadas' => Mesa::where('estado', Mesa::ESTADO_BLOQUEADA)->count(),
        ];

        $stats['ocupacion'] = $stats['total'] > 0
            ? round((($stats['ocupadas'] + $stats['en_cuenta']) / $stats['total']) * 100)
            : 0;

        return view('mesas.index', compact('mesas', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => 'required|integer|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'required|in:libre,ocupada,en_cuenta,bloqueada',
        ]);

        Mesa::create($validated);

        return redirect()->route('mesas.index')->with('success', 'Mesa registrada correctamente.');
    }

    public function asignarEstado(Request $request, Mesa $mesa)
    {
        $request->validate([
            'estado' => ['required', 'in:' . implode(',', [
                Mesa::ESTADO_LIBRE,
                Mesa::ESTADO_OCUPADA,
                Mesa::ESTADO_CUENTA,
                Mesa::ESTADO_BLOQUEADA,
            ])],
        ]);

        $mesa->update(['estado' => $request->input('estado')]);

        return back()->with('success', 'Estado de mesa actualizado.');
    }

    public function liberar(Mesa $mesa)
    {
        DB::transaction(function () use ($mesa) {
            $mesa->update(['estado' => Mesa::ESTADO_LIBRE]);

            $mesa->pedidos()
                ->whereIn('estado', [Pedido::ESTADO_PENDIENTE, Pedido::ESTADO_EN_COCINA, Pedido::ESTADO_LISTO])
                ->update(['estado' => Pedido::ESTADO_ANULADO]);
        });

        return back()->with('success', 'Mesa liberada.');
    }

    public function unir(Request $request)
{
  $mesasIds = $request->mesas;

    if (count($mesasIds) < 2) {
        return back()->with('error', 'Debes seleccionar al menos dos mesas.');
    }

    // Generar un grupo único
    $grupo = time(); // o random_int(1000, 9999)

    foreach ($mesasIds as $mesaId) {
        Mesa::where('id', $mesaId)->update([
            'combinada' => true,
            'combinada_grupo' => $grupo,
            'estado' => 'ocupada',
        ]);
    }

    return back()->with('success', 'Mesas unidas correctamente.');
}

    public function separar($grupo)
{
    Mesa::where('combinada_grupo', $grupo)->update([
        'combinada' => false,
        'combinada_grupo' => null,
        'estado' => 'libre',
    ]);

    return back()->with('success', 'Mesas separadas correctamente.');
}



}
