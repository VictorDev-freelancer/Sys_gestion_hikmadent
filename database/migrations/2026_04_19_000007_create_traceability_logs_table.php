<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [FASE 1] Tabla `traceability_logs` — Historial de Trazabilidad.
 *
 * Registra CADA movimiento de una orden:
 * Área origen → Área destino, quién, cuándo, por qué.
 *
 * Permite reconstruir la ruta completa de cualquier solicitud.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traceability_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('to_area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete();
            $table->string('action', 30);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Índices para consultas de trazabilidad
            $table->index('work_order_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traceability_logs');
    }
};
