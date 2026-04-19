<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [FASE 1] Tabla `areas`.
 *
 * Las 7 áreas operativas de HIKMADENT almacenadas en BD.
 * [SOLID - OCP] Almacenar en BD permite escalar sin tocar código.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->string('color', 7)->default('#6366F1'); // Hex color
            $table->string('icon', 50)->default('squares-2x2');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
