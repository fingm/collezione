<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Se agrega la columna cliente_id permitiendo nulos para mantener los registros existentes
            $table->foreignId('cliente_id')
                  ->nullable()
                  ->after('id') // Ubica la columna justo después del ID
                  ->constrained('usuarios')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Elimina la clave foránea y la columna en caso de rollback
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });
    }
};