<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [INVENTARIO] Tabla `product_variants` — Variaciones de producto.
 *
 * Cada registro es una combinación específica de un producto:
 * Ej: Disco Zirconio → A1 #18, A1 #16, B2 #14, etc.
 *
 * El campo `current_stock` es DESNORMALIZADO por rendimiento.
 * La fuente de verdad es la tabla `inventory_movements` (Kardex).
 *
 * Incluye `expires_at` para control de vencimiento
 * y `cost_price` para costeo promedio ponderado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 50)->unique();                      // ZRC-A1-18
            $table->string('variant_name', 100);                      // A1 #18
            $table->string('color', 30)->nullable();                  // A1, B2
            $table->string('size', 30)->nullable();                   // #18, #16
            $table->json('additional_attributes')->nullable();        // Atributos flexibles
            $table->decimal('current_stock', 10, 2)->default(0);      // Stock cacheado
            $table->decimal('minimum_stock', 10, 2)->nullable();      // Override del mínimo
            $table->decimal('cost_price', 10, 2)->nullable();         // Precio costo unitario (promedio ponderado)
            $table->date('expires_at')->nullable();                   // Fecha de vencimiento
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('product_id');
            $table->index('current_stock');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
