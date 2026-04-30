<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [INVENTARIO] Tabla `inventory_movements` — KARDEX.
 *
 * Registro INMUTABLE de cada entrada, salida, ajuste y devolución.
 * Esta tabla es la FUENTE DE VERDAD del inventario.
 *
 * [APPEND-ONLY] Nunca se editan ni eliminan registros.
 * Los errores se corrigen con movimientos compensatorios.
 *
 * Incluye `stock_before` y `stock_after` para reconstruir
 * el estado del inventario en cualquier punto del tiempo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->string('movement_type', 20);                       // Enum: entry, exit, adjustment, return
            $table->decimal('quantity', 10, 2);                        // Siempre positivo
            $table->decimal('stock_before', 10, 2);                    // Snapshot antes
            $table->decimal('stock_after', 10, 2);                     // Snapshot después
            $table->string('reference_type', 50)->nullable();          // Polimórfico: work_order, manual, purchase
            $table->unsignedBigInteger('reference_id')->nullable();    // ID del documento
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('unit_cost', 10, 2)->nullable();           // Costo unitario
            $table->string('reason', 30);                              // Enum: purchase, work_order_consumption, etc.
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamp('created_at')->useCurrent();

            // Índices para consultas frecuentes
            $table->index(['product_variant_id', 'movement_date'], 'inv_mov_variant_date_idx');
            $table->index('movement_type');
            $table->index(['reference_type', 'reference_id'], 'inv_mov_reference_idx');
            $table->index('performed_by');
            $table->index('movement_date');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
