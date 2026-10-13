<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            
            // Apunta a la nueva tabla 'usuarios'
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            
            $table->string('alias')->nullable(); // Ej: 'Casa', 'Oficina', 'Depósito'
            $table->string('direccion');         // Calle / Nombre de vía
            $table->string('numero_puerta')->nullable();
            $table->string('apartamento')->nullable(); // Piso / Bis / Apto
            $table->string('esquina')->nullable();
            $table->string('barrio')->nullable();
            $table->string('ciudad')->default('Maldonado');
            $table->string('departamento')->default('Maldonado');
            $table->text('indicaciones')->nullable();  // Indicaciones para el flete
            
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};