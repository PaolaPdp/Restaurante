<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['pedido.mesa', 'responsable'])
            ->latest('fecha')
            ->paginate(15);

        $stats = [
            'total' => Venta::count(),
            'ingresos_hoy' => Venta::whereDate('fecha', Carbon::today())->sum('total'),
            'ingresos_mes' => Venta::whereBetween('fecha', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('total'),
            'ticket_promedio' => Venta::avg('total') ?: 0,
        ];

        $mediosPago = Venta::select('tipo_pago')
            ->selectRaw('COUNT(*) as total, SUM(total) as monto')
            ->groupBy('tipo_pago')
            ->orderByDesc('total')
            ->get();

        return view('ventas.index', compact('ventas', 'stats', 'mediosPago'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => ['required', 'exists:pedidos,id'],
            'tipo_pago' => ['required', 'in:efectivo,tarjeta,yape,plin,transferencia'],
        ]);

        $pedido = Pedido::with(['mesa', 'venta'])->findOrFail($validated['pedido_id']);

        if ($pedido->estado === Pedido::ESTADO_PAGADO || $pedido->venta) {
            return back()->with('info', 'El pedido ya fue cancelado.');
        }

        if ($pedido->estado !== Pedido::ESTADO_SERVIDO) {
            return back()->with('info', 'El pedido debe marcarse como servido antes de cobrar.');
        }

        $codigo = $this->generarCodigo();

        DB::transaction(function () use ($pedido, $validated, $codigo) {
            Venta::create([
                'codigo' => $codigo,
                'pedido_id' => $pedido->id,
                'total' => $pedido->total,
                'tipo_pago' => $validated['tipo_pago'],
                'registrado_por' => request()->user()?->id,
                'fecha' => Carbon::now(),
            ]);

            $pedido->update(['estado' => Pedido::ESTADO_PAGADO]);
            $pedido->mesa?->update(['estado' => Mesa::ESTADO_LIBRE]);
        });

        return redirect()->route('ventas.index')->with('success', 'Venta registrada.');
    }

    protected function generarCodigo(): string
    {
        $secuencia = (Venta::max('id') ?? 0) + 1;

        return 'V-' . str_pad((string) $secuencia, 6, '0', STR_PAD_LEFT);
    }
}
