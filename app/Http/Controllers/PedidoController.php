<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $estado = $request->input('estado');

        $query = Pedido::with(['mesa', 'mozo'])
            ->when($user->role === 'mozo', fn ($q) => $q->where('usuario_id', $user->id))
            ->latest();

        $estados = [
            Pedido::ESTADO_PENDIENTE => 'Pendiente',
            Pedido::ESTADO_EN_COCINA => 'En cocina',
            Pedido::ESTADO_LISTO => 'Listo',
            Pedido::ESTADO_SERVIDO => 'Servido',
            Pedido::ESTADO_PAGADO => 'Pagado',
            Pedido::ESTADO_ANULADO => 'Anulado',
        ];

        if ($estado && !array_key_exists($estado, $estados)) {
            $estado = null;
        }

        $pedidos = $query
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->paginate(10)
            ->withQueryString();

        return view('pedidos.index', compact('pedidos', 'estado', 'estados'));
    }

    public function create(Request $request)
    {
        $mesaId = $request->input('mesa_id');
        $mesa = $mesaId ? Mesa::findOrFail($mesaId) : null;

        $productos = Producto::activos()
            ->orderByRaw("FIELD(categoria, 'entrada','menu','extra','bebida')")
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');

        $mesas = Mesa::orderBy('numero')->get();

        return view('pedidos.create', compact('productos', 'mesa', 'mesas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mesa_id' => ['required', 'exists:mesas,id'],
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'enviar_a_cocina' => ['nullable', 'boolean'],
        ]);

        $items = collect($validated['items'])
            ->filter(fn ($cantidad) => (int) $cantidad > 0);

        if ($items->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Debes seleccionar al menos un producto.']);
        }

        $mesa = Mesa::findOrFail($validated['mesa_id']);
        $usuarioId = $request->user()?->id;

        $pedido = null;

        DB::transaction(function () use ($items, $validated, $mesa, $usuarioId, &$pedido) {
            $estadoInicial = (bool) ($validated['enviar_a_cocina'] ?? false)
                ? Pedido::ESTADO_EN_COCINA
                : Pedido::ESTADO_PENDIENTE;

            $pedido = Pedido::create([
                'mesa_id' => $mesa->id,
                'usuario_id' => $usuarioId,
                'estado' => $estadoInicial,
                'total' => 0,
                'notas' => $validated['notas'] ?? null,
                'enviado_a_cocina_at' => $estadoInicial === Pedido::ESTADO_EN_COCINA ? Carbon::now() : null,
            ]);

            $total = 0;

            foreach ($items as $productoId => $cantidad) {
                $producto = Producto::activos()->findOrFail($productoId);
                $cantidad = (int) $cantidad;
                $subtotal = $producto->precio * $cantidad;

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                    'estado' => $producto->requiere_cocina
                        ? ($estadoInicial === Pedido::ESTADO_EN_COCINA
                            ? DetallePedido::ESTADO_EN_PREPARACION
                            : DetallePedido::ESTADO_PENDIENTE)
                        : DetallePedido::ESTADO_LISTO,
                ]);

                $total += $subtotal;
            }

            $pedido->update(['total' => $total]);

            $mesa->update(['estado' => Mesa::ESTADO_OCUPADA]);
        });

        return redirect()->route('pedidos.show', $pedido)->with('success', 'Pedido registrado correctamente.');
    }

    public function show(Pedido $pedido)
{
    $pedido->load(['mesa', 'mozo', 'detalles.producto', 'venta']);

    // 🔹 Agregamos todas las mesas para el modal "Cambiar mesa"
    $mesas = \App\Models\Mesa::orderBy('numero')->get();

    return view('pedidos.show', compact('pedido', 'mesas'));
}


    public function enviarACocina(Pedido $pedido)
    {
        $this->autorizarAccionMozo($pedido);

        if ($pedido->estado !== Pedido::ESTADO_PENDIENTE) {
            return back()->with('info', 'El pedido ya fue enviado a cocina.');
        }

        $pedido->update([
            'estado' => Pedido::ESTADO_EN_COCINA,
            'enviado_a_cocina_at' => Carbon::now(),
        ]);

        $pedido->detalles()
            ->where('estado', DetallePedido::ESTADO_PENDIENTE)
            ->update(['estado' => DetallePedido::ESTADO_EN_PREPARACION]);

        return back()->with('success', 'Pedido enviado a cocina.');
    }

    public function marcarServido(Pedido $pedido)
    {
        $this->autorizarAccionMozo($pedido);

        if (!in_array($pedido->estado, [Pedido::ESTADO_LISTO, Pedido::ESTADO_SERVIDO], true)) {
            return back()->with('info', 'El pedido aún no está listo.');
        }

        $pedido->update([
            'estado' => Pedido::ESTADO_SERVIDO,
            'entregado_at' => Carbon::now(),
        ]);

        $pedido->detalles()
            ->where('estado', '!=', DetallePedido::ESTADO_ANULADO)
            ->update(['estado' => DetallePedido::ESTADO_ENTREGADO]);

        $pedido->mesa?->update(['estado' => Mesa::ESTADO_CUENTA]);

        return back()->with('success', 'Pedido marcado como servido.');
    }

    public function anular(Pedido $pedido)
    {
        $this->autorizarAccionMozo($pedido);

        if ($pedido->estado === Pedido::ESTADO_PAGADO) {
            return back()->with('info', 'No se puede anular un pedido pagado.');
        }

        DB::transaction(function () use ($pedido) {
            $pedido->update(['estado' => Pedido::ESTADO_ANULADO]);
            $pedido->detalles()->update(['estado' => DetallePedido::ESTADO_ANULADO]);

            if ($pedido->mesa) {
                $hayOtrosPedidos = $pedido->mesa
                    ->pedidos()
                    ->whereNotIn('estado', [Pedido::ESTADO_ANULADO, Pedido::ESTADO_PAGADO])
                    ->where('id', '!=', $pedido->id)
                    ->exists();

                if (!$hayOtrosPedidos) {
                    $pedido->mesa->update(['estado' => Mesa::ESTADO_LIBRE]);
                }
            }
        });

        return redirect()->route('pedidos.index')->with('success', 'Pedido anulado.');
    }

    protected function autorizarAccionMozo(Pedido $pedido): void
    {
        $user = request()->user();

        if ($user->role === 'mozo' && $pedido->usuario_id !== $user->id) {
            abort(403);
        }
    }
     public function cambiarMesa(Request $request, $pedidoId)
{
    $request->validate([
        'nueva_mesa_id' => 'required|exists:mesas,id',
    ]);

    $pedido = \App\Models\Pedido::findOrFail($pedidoId);

    $mesaAnterior = $pedido->mesa;
    $nuevaMesa = \App\Models\Mesa::findOrFail($request->nueva_mesa_id);

    // Validar que la nueva mesa esté libre
    if ($nuevaMesa->estado !== 'libre') {
        return back()->with('error', 'La mesa seleccionada no está disponible.');
    }

    // 🔹 Cambiar la relación del pedido
    $pedido->mesa_id = $nuevaMesa->id;
    $pedido->save();

    // 🔹 Registrar observaciones en ambas mesas
    $fecha = now()->format('d/m/Y H:i');
    $mensaje = "El pedido de la mesa {$mesaAnterior->numero} se ha movido a la mesa {$nuevaMesa->numero} el {$fecha}.";

    $mesaAnterior->update([
        'estado' => 'libre',
        'observaciones' => $mensaje,
    ]);

    $nuevaMesa->update([
        'estado' => 'ocupada',
        'observaciones' => $mensaje,
    ]);

    return back()->with('success', "El pedido se ha movido a la Mesa {$nuevaMesa->numero}.");
}



}
