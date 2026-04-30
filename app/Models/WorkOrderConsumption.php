<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model WorkOrderConsumption — Consumo de Insumo por Orden
 *
 * [TRAZABILIDAD] Tabla puente que vincula cada consumo
 * a su orden de trabajo Y a su movimiento en el Kardex.
 *
 * Permite responder: "¿Qué insumos usó la orden OT-20260429-0001?"
 * y "¿En qué órdenes se usó el disco A1 #18?"
 */
class WorkOrderConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'product_variant_id',
        'inventory_movement_id',
        'quantity',
        'area_id',
        'performed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function inventoryMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
