<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [FASE 1] Tabla `work_order_areas` — Pivote Enriquecido.
 *
 * Registra el paso de cada Orden por cada Área, incluyendo:
 * - Técnico responsable en el área
 * - Doctor/supervisor del proceso
 * - Estado Kanban local
 * - Timestamps de entrada y salida (trazabilidad)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kanban_status', 20)->default('pending');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['work_order_id', 'area_id']);
            $table->index('kanban_status');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_areas');
    }
};
