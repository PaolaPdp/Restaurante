<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductController extends Controller
{
    public function index()
    {
        $productos = Producto::orderByRaw("FIELD(categoria, 'entrada','menu','extra','bebida','ejecutivo')")
            ->orderBy('nombre')
            ->paginate(20);

        $stats = [
            'total' => Producto::count(),
            'activos' => Producto::where('estado', 'activo')->count(),
            'requiere_cocina' => Producto::where('requiere_cocina', true)->count(),
            'inactivos' => Producto::where('estado', 'inactivo')->count(),
        ];

        $categorias = Producto::select('categoria')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->get();

        return view('productos.index', compact('productos', 'stats', 'categorias'));
    }

    public function create()
    {
        return view('productos.create');
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
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
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

    // DEVOLVER JSON — NO VISTA
    return response()->json($productos);
}



}
