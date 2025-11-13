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
    $mesasIds = $request->input('mesas', []);

    if (count($mesasIds) < 2) {
        return back()->with('error', 'Debes seleccionar al menos dos mesas para unir.');
    }

    // Obtener las mesas seleccionadas
    $mesas = Mesa::whereIn('id', $mesasIds)->get();

    // Tomamos la primera como principal
    $mesaPrincipal = $mesas->first();
    $otrasMesas = $mesas->where('id', '!=', $mesaPrincipal->id)->pluck('numero')->toArray();

    // Actualizar todas las mesas seleccionadas como combinadas
    foreach ($mesas as $mesa) {
        $mesa->update([
            'combinada' => true,
            'combinada_con' => implode(',', $mesas->pluck('numero')->toArray()), // Ej: "8,9"
            'estado' => 'ocupada',
        ]);
    }

    // Registrar observación en la principal
    $mesaPrincipal->update([
        'observaciones' => 'Mesa ' . $mesaPrincipal->numero . ' unida con mesas ' . implode(', ', $otrasMesas),
    ]);

    return back()->with('success', 'Las mesas se unieron correctamente.');
}





    public function separar(Request $request)
{
    $mesaId = $request->input('mesa_id');
    $mesaCombinada = Mesa::find($mesaId);

    if (!$mesaCombinada || !$mesaCombinada->combinada) {
        return back()->with('error', 'No se encontró la mesa combinada.');
    }

    $mesasOriginales = json_decode($mesaCombinada->mesas_unidas, true);

    // Liberar las originales
    Mesa::whereIn('id', $mesasOriginales)->update([
        'estado' => 'libre',
    ]);

    // Eliminar la mesa combinada
    $mesaCombinada->delete();

    return back()->with('success', 'Las mesas se han separado correctamente.');
}


}
