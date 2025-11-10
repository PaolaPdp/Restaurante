<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->unsignedSmallInteger('cantidad');
            $table->decimal('precio_unitario', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->enum('estado', ['pendiente', 'en_preparacion', 'listo', 'entregado', 'anulado'])->default('pendiente');
            $table->text('nota_cocina')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
