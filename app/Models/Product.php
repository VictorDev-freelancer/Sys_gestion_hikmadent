<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Product — Producto Base
 *
 * [3FN] Almacena datos comunes del producto.
 * Las variaciones (color, tamaño) viven en ProductVariant.
 *
 * Ejemplo: "Disco de Zirconio" es un Product.
 *          "A1 #18" es un ProductVariant.
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'unit_of_measure',
        'minimum_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minimum_stock' => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  BOOT — Auto-generar código                                         */
    /* ------------------------------------------------------------------ */

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->code)) {
                $product->code = self::generateCode();
            }
        });
    }

    private static function generateCode(): string
    {
        $last = self::orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->code, 4)) + 1 : 1;

        return 'INS-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /* ------------------------------------------------------------------ */
    /*  SCOPES                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Stock total sumando todas las variantes.
     */
    public function getTotalStockAttribute(): float
    {
        return $this->variants()->sum('current_stock');
    }

    /**
     * ¿Alguna variante está por debajo del stock mínimo?
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->variants()
            ->whereColumn('current_stock', '<', 'minimum_stock')
            ->orWhere(function ($query) {
                $query->whereNull('minimum_stock')
                      ->where('current_stock', '<', $this->minimum_stock);
            })
            ->exists();
    }
}
