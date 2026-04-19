<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Area
 *
 * [SOLID - OCP] Las 7 áreas operativas de HIKMADENT viven en BD,
 * no hardcodeadas en código. Esto permite agregar nuevas áreas
 * sin modificar ni una línea de código existente.
 *
 * Áreas del prompt: Administración, Impresión, Yeso, Digital,
 * Fresado, Inyectado y Adaptación, Cerámica.
 *
 * @property int    $id
 * @property string $name           Nombre del área
 * @property string $slug           Slug para URLs
 * @property int    $display_order  Orden de visualización
 * @property string $color          Color hex para UI
 * @property string $icon           Nombre del ícono heroicons
 * @property bool   $is_active      Si el área está activa
 */
class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_order',
        'color',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'display_order' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Etapas/checklist que pertenecen a esta área.
     */
    public function stages(): HasMany
    {
        return $this->hasMany(AreaStage::class)->orderBy('display_order');
    }

    /**
     * Órdenes de trabajo asignadas a esta área (pivote enriquecido).
     */
    public function workOrderAreas(): HasMany
    {
        return $this->hasMany(WorkOrderArea::class);
    }

    /* ------------------------------------------------------------------ */
    /*  SCOPES                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Solo áreas activas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ordenadas por su posición de visualización.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
