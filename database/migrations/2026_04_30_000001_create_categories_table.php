<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [INVENTARIO] Tabla `categories` — Familias de productos.
 *
 * Agrupa los insumos del laboratorio en categorías:
 * Discos de Zirconio, Resinas, PMMA, Cerámicas, etc.
 *
 * [SOLID - OCP] Almacenar en BD permite agregar categorías
 * sin modificar código fuente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
