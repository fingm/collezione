<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('envios', function (Blueprint $table) {              
            $table->id();

            // Relación con la venta/pedido y la dirección seleccionada
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('direccion_id')->constrained('direcciones')->onDelete('restrict');

            // Estado del ciclo del envío
            $table->enum('estado', ['pendiente', 'en_camino', 'entregado', 'cancelado'])->default('pendiente');

            // Fechas clave
            $table->date('fecha_pactada')->nullable();      // Día previsto para la entrega
            $table->timestamp('fecha_entrega')->nullable();  // Momento en que se marca como entregado

            // Información logística opcional
            $table->string('cadete_o_transporte')->nullable(); // Ej: "Cadete Juan", "DAC", "De Punta"
            $table->text('notas_envio')->nullable();           // Ej: "Entregar después de las 14hs"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::dropIfExists('envios');
    }
};
