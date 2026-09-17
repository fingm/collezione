<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            // --- Identificación Base ---
            $table->string('nombre');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->text('descripcion')->nullable(); // Para la etapa de e-commerce o ficha detallada

            // --- Costos, Precios e Impuestos ---
            $table->decimal('costo', 10, 2); 
            $table->decimal('precio_venta', 10, 2); // Precio final al público

            // --- Inventario y Control ---
            // Si es null, representa un producto/servicio sin control estricto de stock
            $table->integer('stock')->nullable(); 
            $table->boolean('activo')->default(true); // Para ocultar o deshabilitar sin borrar del historial

            // --- Especificaciones Físicas y Garantía ---
            $table->string('garantia')->nullable(); // Ej: "12 meses", "6 meses con la marca"
            $table->string('medidas')->nullable(); // Ej: "30x20x10 cm" o "1.5 m"

            $table->timestamps();
            $table->softDeletes(); // Permite "borrado suave" para no perder historial en ventas pasadas
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
