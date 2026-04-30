<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [INVENTARIO] Tabla `products` — Producto base.
 *
 * Representa un producto genérico del laboratorio
 * (ej: "Disco de Zirconio"). Las variaciones específicas
 * (color, tamaño) viven en `product_variants`.
 *
 * [3FN] Separar producto de variante evita duplicación
 * de datos comunes (nombre, categoría, unidad de medida).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('code', 30)->unique();              // INS-001
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('unit_of_measure', 20)->default('unidad'); // unidad, caja, ml, g
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category_id');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
