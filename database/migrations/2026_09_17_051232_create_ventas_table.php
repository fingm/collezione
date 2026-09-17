<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            // --- Datos del Cliente ---
            $table->string('cliente_nombre');
            $table->string('cliente_telefono')->nullable();
            $table->string('cliente_direccion')->nullable();

            // --- Entrega / Envío ---
            $table->enum('tipo_entrega', ['retiro', 'flete'])->default('retiro');
            $table->decimal('costo_flete', 10, 2)->default(0.00);

            // --- Pago ---
            $table->string('metodo_pago'); // Ej: Contado, Tarjeta, Transferencia
            $table->integer('cuotas')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0.00);
            $table->decimal('precio_total', 10, 2);

            // --- Control y Tiempos ---
            $table->dateTime('fecha_compra');
            $table->string('estado')->default('completada'); // completada, pendiente, cancelada
            $table->text('notas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};