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
    $mesa = null;
    $mesasGrupo = collect();
    $grupo = $request->grupo;

    if ($request->mesa_id) {
        $mesa = Mesa::findOrFail($request->mesa_id);
    }

    if ($grupo) {
        $mesasGrupo = Mesa::where('combinada_grupo', $grupo)->get();
    }

    return view('pedidos.create', compact('mesa', 'grupo', 'mesasGrupo'));
}



public function store(Request $request)
{
    $data = $request->validate([
        'mesa_id' => 'nullable|exists:mesas,id',
        'grupo'   => 'nullable',
        'notas'   => 'nullable|string',
        'items'   => 'required|array',
        'items.*.cantidad' => 'required|integer|min:1',
        'items.*.descripcion' => 'nullable|string|max:500',
    ]);

    DB::transaction(function () use ($data, &$pedido) {

        $pedido = new Pedido();
        $pedido->usuario_id = auth()->id();
        $pedido->estado = Pedido::ESTADO_PENDIENTE;
        $pedido->notas = $data['notas'] ?? null;

        // 🟢 PEDIDO CON MESAS UNIDAS
        if (!empty($data['grupo'])) {

            $mesas = Mesa::where('combinada_grupo', $data['grupo'])->get();

            $pedido->grupo = $mesas->pluck('id')->toJson();
            $pedido->mesa_id = null;

            Mesa::whereIn('id', $mesas->pluck('id'))
                ->update(['estado' => Mesa::ESTADO_OCUPADA]);
        }
        // 🟢 PEDIDO CON UNA SOLA MESA
        elseif (!empty($data['mesa_id'])) {

            $pedido->mesa_id = $data['mesa_id'];
            $pedido->grupo = null;

            Mesa::where('id', $data['mesa_id'])
                ->update(['estado' => Mesa::ESTADO_OCUPADA]);
        }

        $pedido->save();

        // 🟢 GUARDAR PRODUCTOS
        $total = 0;

        foreach ($data['items'] as $productoId => $item) {

            $producto = Producto::findOrFail($productoId);
            $subtotal = $producto->precio * $item['cantidad'];

            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $producto->id,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $producto->precio,
                'subtotal' => $subtotal,
                'notas' => $item['descripcion'] ?? null,
                'estado' => $producto->requiere_cocina
                    ? DetallePedido::ESTADO_PENDIENTE
                    : DetallePedido::ESTADO_LISTO,
            ]);

            $total += $subtotal;
        }

        $pedido->update(['total' => $total]);
    });

    return redirect()->route('pedidos.show', $pedido);
}






    public function show(Pedido $pedido)
    {
        $pedido->load(['mesa', 'mozo', 'detalles.producto', 'venta']);

    // 🔹 Agregamos todas las mesas para el modal "Cambiar mesa"
    $mesas = \App\Models\Mesa::orderBy('numero')->get();

    return view('pedidos.show', compact('pedido', 'mesas'));
}

public function edit(Pedido $pedido)
{
    $this->autorizarAccionMozo($pedido);

    if (in_array($pedido->estado, [Pedido::ESTADO_PAGADO, Pedido::ESTADO_ANULADO], true)) {
        return redirect()->route('pedidos.show', $pedido)
            ->with('info', 'No se puede modificar un pedido pagado o anulado.');
    }

    $pedido->load(['mesa', 'detalles.producto']);

    // Para permitir agregar nuevos productos, cargamos productos activos
    $productos = Producto::activos()->orderBy('nombre')->get(['id','nombre','precio']);

    return view('pedidos.edit', compact('pedido', 'productos'));
}

public function update(Request $request, Pedido $pedido)
{
    $this->autorizarAccionMozo($pedido);

    if (in_array($pedido->estado, [Pedido::ESTADO_PAGADO, Pedido::ESTADO_ANULADO], true)) {
        return back()->with('info', 'No se puede modificar un pedido pagado o anulado.');
    }

    $validated = $request->validate([
        'items' => ['required', 'array'],
        'items.*' => ['required', 'array'],
        'items.*.cantidad' => ['required', 'integer', 'min:1'],
        'items.*.descripcion' => ['nullable', 'string', 'max:500'],
        'notas' => ['nullable', 'string', 'max:1000'],
    ]);

    $items = collect($validated['items'])
        ->filter(fn ($item) => (int) $item['cantidad'] > 0);

    if ($items->isEmpty()) {
        return back()->withInput()->withErrors(['items' => 'Debes seleccionar al menos un producto.']);
    }

    DB::transaction(function () use ($items, $validated, $pedido) {
        // Eliminar detalles actuales y recrear con la nueva selección
        $pedido->detalles()->delete();

        $total = 0;

        foreach ($items as $productoId => $item) {
            $producto = Producto::activos()->findOrFail($productoId);

            $cantidad = (int) $item['cantidad'];
            $descripcion = $item['descripcion'] ?? null;
            $subtotal = $producto->precio * $cantidad;

            DetallePedido::create([
                'pedido_id'       => $pedido->id,
                'producto_id'     => $producto->id,
                'cantidad'        => $cantidad,
                'precio_unitario' => $producto->precio,
                'subtotal'        => $subtotal,
                'notas'     => $descripcion,
                'estado' => $producto->requiere_cocina
                    ? DetallePedido::ESTADO_EN_PREPARACION
                    : DetallePedido::ESTADO_LISTO,
            ]);

            $total += $subtotal;
        }

        $pedido->update([
            'total' => $total,
            'notas' => $validated['notas'] ?? $pedido->notas,
        ]);
    });

    return redirect()->route('pedidos.show', $pedido)->with('success', 'Pedido actualizado correctamente.');
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

    if (!$mesaAnterior) {
    return back()->with('error', 'Este pedido no tiene mesa asignada.');
}


    // Validar que la nueva mesa esté libre
    if ($nuevaMesa->estado !== Mesa::ESTADO_LIBRE) {

        return back()->with('error', 'La mesa seleccionada no está disponible.');
    }

    // 🔹 Cambiar la relación del pedido
    $pedido->mesa_id = $nuevaMesa->id;
    $pedido->save();

    // 🔹 Registrar observaciones en ambas mesas
    $fecha = now()->format('d/m/Y H:i');
    $mensaje = "El pedido de la mesa {$mesaAnterior->numero} se ha movido a la mesa {$nuevaMesa->numero} el {$fecha}.";

    $mesaAnterior->update([
    'estado' => Mesa::ESTADO_LIBRE,
    'observaciones' => $mensaje,
]);

$nuevaMesa->update([
    'estado' => Mesa::ESTADO_OCUPADA,
    'observaciones' => $mensaje,
]);

    return back()->with('success', "El pedido se ha movido a la Mesa {$nuevaMesa->numero}.");
}

public function pedidosCaja()
{
    $pedidos = Pedido::with(['mesa', 'mozo'])
        ->where('estado', Pedido::ESTADO_SERVIDO)
        ->whereDoesntHave('venta')
        ->latest()
        ->get();

    return view('caja.pedidos', compact('pedidos'));
}


}
