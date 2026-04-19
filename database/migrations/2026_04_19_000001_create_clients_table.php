<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [FASE 1] Tabla `clients`.
 *
 * Almacena los datos del cliente/doctor que solicita
 * servicios al laboratorio HIKMADENT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('clinic_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices para búsqueda rápida
            $table->index('name');
            $table->index('doctor_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
