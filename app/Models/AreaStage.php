<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model AreaStage
 *
 * [CLEAN CODE] Cada etapa/actividad del checklist de un área.
 * El prompt define actividades específicas por área (ej: "Vaciado del yeso",
 * "Encerado de patrón", etc.). Cada una se modela como registro independiente.
 *
 * No se suben archivos 3D; cada etapa funciona como un CHECK:
 * ✅ ¿Se completó? ¿Quién lo hizo? ¿Cuándo?
 *
 * @property int    $id
 * @property int    $area_id           FK al área
 * @property string $name              Nombre de la etapa
 * @property string $description       Descripción detallada (opcional)
 * @property int    $display_order     Orden secuencial dentro del área
 * @property int    $estimated_minutes Tiempo estimado en minutos
 * @property bool   $is_required       Si es obligatoria para avanzar
 */
class AreaStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'name',
        'description',
        'display_order',
        'estimated_minutes',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'display_order'     => 'integer',
            'estimated_minutes' => 'integer',
            'is_required'       => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Área a la que pertenece esta etapa.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Registros de ejecución de esta etapa en órdenes concretas.
     */
    public function workOrderAreaStages(): HasMany
    {
        return $this->hasMany(WorkOrderAreaStage::class);
    }
}
