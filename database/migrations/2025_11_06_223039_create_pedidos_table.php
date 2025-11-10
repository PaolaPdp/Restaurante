<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total', 8, 2)->default(0);
            $table->enum('estado', ['pendiente', 'en_cocina', 'listo', 'servido', 'pagado', 'anulado'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamp('enviado_a_cocina_at')->nullable();
            $table->timestamp('entregado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
