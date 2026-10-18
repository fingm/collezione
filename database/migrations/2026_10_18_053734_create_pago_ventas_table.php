<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->string('metodo_pago'); // 'efectivo', 'tarjeta_debito', 'tarjeta_credito', 'transferencia', etc.
            $table->decimal('monto', 10, 2);
            $table->string('referencia')->nullable(); // Nro de voucher, transacción o comprobante si aplica
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_ventas');
    }
};