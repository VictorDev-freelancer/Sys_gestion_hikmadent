<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [FASE 1] Tabla `area_stages`.
 *
 * Checklist de actividades/etapas por cada área.
 * Cada registro es un item del checklist que debe completarse.
 * NO almacena archivos 3D — solo tracking de completado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->unsignedSmallInteger('estimated_minutes')->default(30);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['area_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_stages');
    }
};
