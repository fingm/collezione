<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            
            // Relación con Roles
            $table->foreignId('role_id')->default(3)->constrained('roles')->onDelete('cascade');

            // Datos Personales
            $table->string('nombre');
            $table->string('apellido');
            $table->enum('tipo_documento', ['CI', 'RUT', 'DNI', 'PASAPORTE', 'OTRO'])->default('CI');
            $table->string('documento')->unique()->nullable();
            $table->string('telefono')->nullable();

            // Autenticación
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Estado y Notas
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};