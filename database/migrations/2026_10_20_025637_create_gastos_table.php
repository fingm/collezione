<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->string('concepto'); // Descripción breve del gasto (ej: "Luz", "Compra de insumos")
            $table->decimal('monto', 10, 2);
            $table->string('categoria')->nullable(); // Ej: "Servicios", "Insumos", "Mantenimiento", "Sueldos"
            $table->string('metodo_pago')->default('Efectivo'); // Efectivo, Transferencia, Tarjeta
            $table->date('fecha');
            $table->text('comprobante_nro')->nullable(); // Nro de factura o ticket
            $table->text('observaciones')->nullable();
            
            // Si quieres asociarlo al usuario que registra el gasto
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};