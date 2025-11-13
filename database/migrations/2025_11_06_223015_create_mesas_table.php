<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::table('mesas', function (Blueprint $table) {
        // $table->boolean('combinada')->default(false);
        // $table->json('mesas_unidas')->nullable(); // guarda IDs de mesas originales
        Schema::table('mesas', function (Blueprint $table) {
        if (!Schema::hasColumn('mesas', 'combinada')) {
            $table->boolean('combinada')->default(false);
        }
        if (!Schema::hasColumn('mesas', 'combinada_con')) {
            $table->string('combinada_con')->nullable();
        }

        if (!Schema::hasColumn('mesas', 'observaciones')) {
            $table->text('observaciones')->nullable();
        }
    });
    
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->unsignedTinyInteger('capacidad')->default(4);
            $table->enum('estado', ['libre', 'ocupada', 'en_cuenta', 'bloqueada'])->default('libre');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
            Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->unique();
            $table->integer('capacidad');
            $table->enum('estado', ['libre', 'ocupada', 'en_cuenta', 'bloqueada'])->default('libre');
            $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');

        Schema::table('mesas', function (Blueprint $table) {
        $table->dropColumn('observaciones');
    });
    }

    
};
