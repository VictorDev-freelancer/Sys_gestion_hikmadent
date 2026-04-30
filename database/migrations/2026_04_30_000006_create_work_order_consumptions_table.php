<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [INVENTARIO] Tabla `work_order_consumptions` — Consumos por Orden.
 *
 * Tabla puente entre órdenes de trabajo e insumos consumidos.
 * Cada consumo está vinculado a un movimiento del Kardex,
 * garantizando trazabilidad completa OT ↔ Inventario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->foreignId('inventory_movement_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices
            $table->index('work_order_id');
            $table->index('product_variant_id');
            $table->unique('inventory_movement_id', 'woc_inv_mov_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_consumptions');
    }
};
