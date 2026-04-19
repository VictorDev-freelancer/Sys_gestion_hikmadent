<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [FASE 1] Tabla `work_order_area_stages` — Checklist Entries.
 *
 * Cada registro = un item del checklist de una etapa.
 * NO almacena archivos. Solo:
 *   ✅ ¿Completado? → is_completed
 *   👤 ¿Por quién? → performed_by
 *   🕐 ¿Cuándo? → completed_at
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_area_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_stage_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Evitar duplicados: una etapa solo una vez por orden-área
            $table->unique(['work_order_area_id', 'area_stage_id'], 'woas_unique');
            $table->index('is_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_area_stages');
    }
};
