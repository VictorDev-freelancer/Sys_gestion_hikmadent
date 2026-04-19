<?php

namespace App\Models;

use App\Enums\KanbanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model WorkOrderArea — Pivote Enriquecido
 *
 * [TRAZABILIDAD] Registra el paso de una Orden de Trabajo por un Área.
 * Incluye: quién es el responsable en el área, el doctor supervisor,
 * y el estado local (tipo checklist).
 *
 * @property int         $id
 * @property int         $work_order_id
 * @property int         $area_id
 * @property int|null    $assigned_to     Técnico responsable en esta área
 * @property int|null    $supervisor_id   Doctor/supervisor del proceso
 * @property string      $kanban_status   Estado Kanban local del área
 * @property datetime    $started_at      Cuándo ingresó a esta área
 * @property datetime    $completed_at    Cuándo salió de esta área
 * @property string|null $notes           Observaciones
 */
class WorkOrderArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'area_id',
        'assigned_to',
        'supervisor_id',
        'kanban_status',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'kanban_status' => KanbanStatus::class,
            'started_at'    => 'datetime',
            'completed_at'  => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Técnico responsable del trabajo en esta área.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Doctor/supervisor responsable del proceso.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Checklist de etapas completadas en esta área para esta orden.
     */
    public function stages(): HasMany
    {
        return $this->hasMany(WorkOrderAreaStage::class)->orderBy('area_stage_id');
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * ¿Se completaron todas las etapas requeridas?
     */
    public function isFullyCompleted(): bool
    {
        $requiredStages = $this->stages()
            ->whereHas('areaStage', fn($q) => $q->where('is_required', true))
            ->get();

        return $requiredStages->every(fn($stage) => $stage->completed_at !== null);
    }

    /**
     * Porcentaje de avance en esta área.
     */
    public function getProgressAttribute(): int
    {
        $total = $this->stages()->count();
        $completed = $this->stages()->whereNotNull('completed_at')->count();

        return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }
}
