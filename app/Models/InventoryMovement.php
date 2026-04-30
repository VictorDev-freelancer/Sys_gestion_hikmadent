<?php

namespace App\Models;

use App\Enums\MovementReason;
use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model InventoryMovement — Movimiento de Inventario (KARDEX)
 *
 * [APPEND-ONLY] Este modelo es INMUTABLE después de creado.
 * No se permiten updates ni deletes.
 *
 * Cada registro captura un snapshot del stock antes y después,
 * permitiendo reconstruir el estado del inventario en cualquier
 * punto del tiempo.
 *
 * @property string $movement_type  Tipo: entry, exit, adjustment, return
 * @property string $reason         Motivo específico del movimiento
 */
class InventoryMovement extends Model
{
    use HasFactory;

    /**
     * Solo timestamp de creación (inmutable).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'product_variant_id',
        'movement_type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'supplier_id',
        'unit_cost',
        'reason',
        'notes',
        'performed_by',
        'movement_date',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'reason'        => MovementReason::class,
            'quantity'       => 'decimal:2',
            'stock_before'   => 'decimal:2',
            'stock_after'    => 'decimal:2',
            'unit_cost'      => 'decimal:2',
            'movement_date'  => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Referencia polimórfica manual (work_order, etc.)
     */
    public function reference()
    {
        if ($this->reference_type === 'work_order') {
            return $this->belongsTo(WorkOrder::class, 'reference_id');
        }

        return null;
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Descripción resumida del movimiento para UI.
     */
    public function getSummaryAttribute(): string
    {
        $variant = $this->productVariant;
        $type = $this->movement_type->label();
        $reason = $this->reason->label();

        return "{$type} — {$reason}: {$this->quantity} {$variant->product->unit_of_measure}";
    }
}
