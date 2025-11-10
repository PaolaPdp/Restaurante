<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;

Route::get('/productos', function(){ return App\Models\Producto::all(); });
Route::post('/pedidos', [PedidoController::class,'store']);
