<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductController extends Controller
{
    public function index()
    {
        $productos = Producto::orderByRaw("FIELD(categoria, 'entrada','menu','extra','bebida')")
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|in:entrada,menu,extra,bebida',
            'descripcion' => 'nullable|string|max:1000',
            'requiere_cocina' => 'nullable|boolean',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        $validated['requiere_cocina'] = $request->boolean('requiere_cocina');
        $validated['estado'] = $validated['estado'] ?? 'activo';

        Producto::create($validated);

        return redirect()->route('productos.index')->with('success', 'Producto agregado correctamente.');
    }
}
