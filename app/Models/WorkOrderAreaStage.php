<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model WorkOrderAreaStage — Checklist de Etapa
 *
 * [CLEAN CODE] Registro individual de si una etapa fue completada.
 * NO sube archivos 3D. Funciona como checklist binario:
 *
 *   ✅ ¿Se completó esta actividad?
 *   👤 ¿Quién la realizó?
 *   🕐 ¿Cuándo?
 *   📝 ¿Observaciones?
 *
 * @property int         $id
 * @property int         $work_order_area_id  Pivote Orden↔Área
 * @property int         $area_stage_id       Etapa del checklist
 * @property bool        $is_completed        Check: ¿sí o no?
 * @property int|null    $performed_by        Quién lo completó
 * @property datetime    $started_at          Inicio de la etapa
 * @property datetime    $completed_at        Finalización de la etapa
 * @property string|null $notes               Observaciones/problemas
 */
class WorkOrderAreaStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_area_id',
        'area_stage_id',
        'is_completed',
        'performed_by',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Pivote Orden↔Área al que pertenece este check.
     */
    public function workOrderArea(): BelongsTo
    {
        return $this->belongsTo(WorkOrderArea::class);
    }

    /**
     * Definición de la etapa (nombre, descripción, etc.).
     */
    public function areaStage(): BelongsTo
    {
        return $this->belongsTo(AreaStage::class);
    }

    /**
     * Usuario que completó esta etapa.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
