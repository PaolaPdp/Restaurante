<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CajaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Sistema
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Mesas
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,mozo,caja')->group(function () {
        Route::get('mesas', [MesaController::class, 'index'])->name('mesas.index');
        Route::get('mesas/create', [MesaController::class, 'create'])->name('mesas.create');
        Route::post('mesas', [MesaController::class, 'store'])->name('mesas.store');
        Route::patch('mesas/{mesa}/estado', [MesaController::class, 'asignarEstado'])->name('mesas.estado');
        Route::post('mesas/{mesa}/liberar', [MesaController::class, 'liberar'])->name('mesas.liberar');
        Route::post('mesas/unir', [MesaController::class, 'unir'])->name('mesas.unir');
        Route::post('mesas/separar/{grupo}', [MesaController::class, 'separar'])->name('mesas.separar');
    });

    /*
    |--------------------------------------------------------------------------
    | Pedidos
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,mozo,caja')->group(function () {
        Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
        // Asegurar que la ruta específica /pedidos/create no sea capturada por el comodín {pedido}
        // Alternativamente, restringimos {pedido} a numérico
        Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])
            ->whereNumber('pedido')
            ->name('pedidos.show');
        Route::get('tickets/{pedido}', [TicketController::class, 'show'])
            ->whereNumber('pedido')
            ->name('tickets.show');
    });

    Route::middleware('role:admin,mozo')->group(function () {
        Route::get('pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create');
        Route::post('pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
        // Editar y actualizar pedido
        Route::get('pedidos/{pedido}/edit', [PedidoController::class, 'edit'])
            ->whereNumber('pedido')
            ->name('pedidos.edit');
        Route::patch('pedidos/{pedido}', [PedidoController::class, 'update'])
            ->whereNumber('pedido')
            ->name('pedidos.update');
        Route::post('pedidos/{pedido}/enviar', [PedidoController::class, 'enviarACocina'])
            ->whereNumber('pedido')
            ->name('pedidos.enviar');
        Route::post('pedidos/{pedido}/servido', [PedidoController::class, 'marcarServido'])
            ->whereNumber('pedido')
            ->name('pedidos.servido');
        Route::post('pedidos/{pedido}/anular', [PedidoController::class, 'anular'])
            ->whereNumber('pedido')
            ->name('pedidos.anular');
        Route::post('pedidos/{pedido}/cambiar-mesa', [PedidoController::class, 'cambiarMesa'])
            ->whereNumber('pedido')
            ->name('pedidos.cambiarMesa');
        // Endpoint para la vista de nuevo pedido: cargar productos por categoría (JSON)
        Route::get('productos/por-categoria/{categoria}', [ProductController::class, 'obtenerPorCategoria'])
            ->name('productos.porCategoria');
    });

    /*
    |--------------------------------------------------------------------------
    | Cocina
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,cocina')->group(function () {
        Route::get('cocina/pedidos', [KitchenController::class, 'index'])->name('cocina.pedidos');
        Route::patch('cocina/detalles/{detalle}', [KitchenController::class, 'actualizarDetalle'])
            ->whereNumber('detalle')
            ->name('cocina.detalles.actualizar');
        Route::post('cocina/pedidos/{pedido}/listo', [KitchenController::class, 'marcarPedidoListo'])->name('cocina.pedidos.listo');
    });

    /*
    |--------------------------------------------------------------------------
    | Caja
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,caja')->group(function () {
        Route::get('caja', [CajaController::class, 'index'])->name('caja.index');
        Route::get('caja/pedidos', [CajaController::class, 'pedidos'])->name('caja.pedidos');

        Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index');
        Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store');
        Route::get('ventas/{pedido}/create', [VentaController::class, 'create'])
            ->whereNumber('pedido')
            ->name('ventas.create');
    });

});
