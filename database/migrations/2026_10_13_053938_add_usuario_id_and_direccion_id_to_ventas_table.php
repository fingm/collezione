<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Clave foránea al usuario/cliente (nullable para ventas de cliente mostrador / consumidor final)
            $table->foreignId('usuario_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('usuarios')
                  ->nullOnDelete();

            // Clave foránea opcional a la dirección de entrega seleccionada
            $table->foreignId('direccion_id')
                  ->nullable()
                  ->after('usuario_id')
                  ->constrained('direcciones')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn('usuario_id');

            $table->dropForeign(['direccion_id']);
            $table->dropColumn('direccion_id');
        });
    }
};