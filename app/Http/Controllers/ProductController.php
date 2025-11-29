<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'buscar' => 'nullable|string|max:255',
            'categoria' => 'nullable|in:entrada,menu,extra,bebida,ejecutivo',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        $productos = Producto::query()
            ->when($filters['categoria'] ?? null, fn ($query, $categoria) => $query->where('categoria', $categoria))
            ->when($filters['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->when($filters['buscar'] ?? null, function ($query, $term) {
                $limpio = trim($term);
                $query->where(function ($subQuery) use ($limpio) {
                    $subQuery->where('nombre', 'like', "%{$limpio}%")
                        ->orWhere('descripcion', 'like', "%{$limpio}%");
                });
            })
            ->orderByRaw("FIELD(categoria, 'entrada','menu','extra','bebida','ejecutivo')")
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Producto::count(),
            'activos' => Producto::where('estado', Producto::ESTADO_ACTIVO)->count(),
            'requiere_cocina' => Producto::where('requiere_cocina', true)->count(),
            'inactivos' => Producto::where('estado', Producto::ESTADO_INACTIVO)->count(),
        ];

        $categorias = Producto::select('categoria')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get();

        return view('productos.index', [
            'productos' => $productos,
            'stats' => $stats,
            'categorias' => $categorias,
            'filters' => $filters,
            'categoriasDisponibles' => Producto::categoriasDisponibles(),
            'estadosDisponibles' => Producto::estadosDisponibles(),
        ]);
    }

    public function create()
    {
        return view('productos.create', [
            'categoriasDisponibles' => Producto::categoriasDisponibles(),
            'estadosDisponibles' => Producto::estadosDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|in:entrada,menu,extra,bebida,ejecutivo',
            'descripcion' => 'nullable|string|max:1000',
            'requiere_cocina' => 'nullable|boolean',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        $validated['requiere_cocina'] = $request->boolean('requiere_cocina');
        $validated['estado'] = $validated['estado'] ?? 'activo';

        Producto::create($validated);

        return redirect()->route('productos.index')->with('success', 'Producto agregado correctamente.');
    }

    public function show(Producto $producto)
    {
        return view('productos.show', [
            'producto' => $producto,
            'categoriasDisponibles' => Producto::categoriasDisponibles(),
            'estadosDisponibles' => Producto::estadosDisponibles(),
        ]);
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', [
            'producto' => $producto,
            'categoriasDisponibles' => Producto::categoriasDisponibles(),
            'estadosDisponibles' => Producto::estadosDisponibles(),
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|in:entrada,menu,extra,bebida,ejecutivo',
            'descripcion' => 'nullable|string|max:1000',
            'requiere_cocina' => 'nullable|boolean',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        $validated['requiere_cocina'] = $request->boolean('requiere_cocina');
        $validated['estado'] = $validated['estado'] ?? $producto->estado;

        $producto->update($validated);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }

    public function obtenerPorCategoria($categoria)
    {
        $productos = Producto::where('categoria', $categoria)->get();

        // DEVUELVE JSON PARA CONSULTAS DINÁMICAS
        return response()->json($productos);
    }
}
