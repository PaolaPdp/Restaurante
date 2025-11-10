<?php

namespace Database\Seeders;

use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@restaurante.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $mozo = User::factory()->create([
            'name' => 'Mozo Principal',
            'email' => 'mozo@restaurante.test',
            'password' => Hash::make('mozo123'),
            'role' => 'mozo',
        ]);

        $cocina = User::factory()->create([
            'name' => 'Cocina',
            'email' => 'cocina@restaurante.test',
            'password' => Hash::make('cocina123'),
            'role' => 'cocina',
        ]);

        User::factory()->create([
            'name' => 'Caja',
            'email' => 'caja@restaurante.test',
            'password' => Hash::make('caja123'),
            'role' => 'caja',
        ]);

        foreach (range(1, 12) as $numero) {
            Mesa::create([
                'numero' => str_pad((string) $numero, 2, '0', STR_PAD_LEFT),
                'capacidad' => $numero <= 8 ? 4 : 6,
            ]);
        }

        $productos = [
            ['Causa Limeña', 'entrada', 12.50, true],
            ['Ensalada César', 'entrada', 10.00, true],
            ['Lomo Saltado', 'menu', 24.90, true],
            ['Ají de Gallina', 'menu', 22.50, true],
            ['Arroz Blanco', 'extra', 4.00, true],
            ['Papas Fritas', 'extra', 6.50, true],
            ['Chicha Morada', 'bebida', 5.50, false],
            ['Limonada', 'bebida', 4.50, false],
        ];

        $productoIds = [];
        foreach ($productos as [$nombre, $categoria, $precio, $requiereCocina]) {
            $productoIds[$nombre] = Producto::create([
                'nombre' => $nombre,
                'categoria' => $categoria,
                'precio' => $precio,
                'requiere_cocina' => $requiereCocina,
                'estado' => 'activo',
            ])->id;
        }

        $pedidoDemo = Pedido::create([
            'mesa_id' => Mesa::first()->id,
            'usuario_id' => $mozo->id,
            'estado' => Pedido::ESTADO_EN_COCINA,
            'total' => 0,
            'enviado_a_cocina_at' => Carbon::now()->subMinutes(10),
        ]);

        $total = 0;
        foreach ([
            ['Lomo Saltado', 2],
            ['Chicha Morada', 2],
            ['Papas Fritas', 1],
        ] as [$productoNombre, $cantidad]) {
            $producto = Producto::find($productoIds[$productoNombre]);
            $subtotal = $producto->precio * $cantidad;
            DetallePedido::create([
                'pedido_id' => $pedidoDemo->id,
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio,
                'subtotal' => $subtotal,
                'estado' => DetallePedido::ESTADO_EN_PREPARACION,
            ]);
            $total += $subtotal;
        }

        $pedidoDemo->update(['total' => $total]);

        Venta::create([
            'codigo' => 'V-000001',
            'pedido_id' => $pedidoDemo->id,
            'total' => $total,
            'tipo_pago' => 'efectivo',
            'registrado_por' => $cocina->id,
            'fecha' => Carbon::now()->subMinutes(5),
        ]);
    }
}
