<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model ProductVariant — Variante de Producto
 *
 * Representa una combinación específica: Disco Zirconio A1 #18.
 *
 * [DESNORMALIZACIÓN] `current_stock` se actualiza atómicamente
 * en cada movimiento para queries rápidas. La fuente de verdad
 * es siempre `inventory_movements`.
 *
 * [COSTEO] `cost_price` almacena el costo promedio ponderado,
 * recalculado en cada entrada de mercancía.
 */
class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'variant_name',
        'color',
        'size',
        'additional_attributes',
        'current_stock',
        'minimum_stock',
        'cost_price',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'additional_attributes' => 'array',
            'current_stock'         => 'decimal:2',
            'minimum_stock'         => 'decimal:2',
            'cost_price'            => 'decimal:2',
            'expires_at'            => 'date',
            'is_active'             => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->orderByDesc('created_at');
    }

    public function workOrderConsumptions(): HasMany
    {
        return $this->hasMany(WorkOrderConsumption::class);
    }

    /* ------------------------------------------------------------------ */
    /*  SCOPES                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock')
                     ->orWhere(function ($q) {
                         $q->whereNull('minimum_stock')
                           ->whereHas('product', fn($p) => $p->whereColumn('products.minimum_stock', '>=', 'product_variants.current_stock'));
                     });
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now()->addDays($days))
                     ->where('current_stock', '>', 0);
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Nombre completo: "Disco de Zirconio — A1 #18"
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->product->name} — {$this->variant_name}";
    }

    /**
     * Stock mínimo efectivo (propio o heredado del producto).
     */
    public function getEffectiveMinimumStockAttribute(): float
    {
        return $this->minimum_stock ?? $this->product->minimum_stock ?? 0;
    }

    /**
     * ¿Está por debajo del stock mínimo?
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->effective_minimum_stock;
    }

    /**
     * ¿Está vencido?
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
