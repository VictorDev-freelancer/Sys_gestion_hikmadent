<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model TraceabilityLog — Historial de Trazabilidad
 *
 * [TRAZABILIDAD] Registra cada movimiento de la orden:
 * De qué área salió → a qué área entró, quién hizo el cambio,
 * qué estado tenía antes y después.
 *
 * Esto permite reconstruir el recorrido completo de cualquier
 * solicitud de cliente desde su creación hasta su entrega.
 *
 * @property int         $id
 * @property int         $work_order_id
 * @property int|null    $from_area_id     Área de origen (null si es creación)
 * @property int|null    $to_area_id       Área de destino (null si es finalización)
 * @property int         $performed_by     Usuario que realizó la acción
 * @property string      $action           Tipo de acción
 * @property string      $from_status      Estado anterior
 * @property string      $to_status        Estado nuevo
 * @property string|null $notes            Observaciones
 */
class TraceabilityLog extends Model
{
    use HasFactory;

    /**
     * Solo timestamp de creación (son inmutables).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'work_order_id',
        'from_area_id',
        'to_area_id',
        'performed_by',
        'action',
        'from_status',
        'to_status',
        'notes',
    ];

    /* ------------------------------------------------------------------ */
    /*  CONSTANTES DE ACCIONES                                             */
    /* ------------------------------------------------------------------ */

    public const ACTION_CREATED      = 'created';
    public const ACTION_ASSIGNED     = 'assigned_to_area';
    public const ACTION_TRANSFERRED  = 'transferred';
    public const ACTION_COMPLETED    = 'area_completed';
    public const ACTION_RETURNED     = 'returned';
    public const ACTION_DELIVERED    = 'delivered';
    public const ACTION_CANCELLED    = 'cancelled';
    public const ACTION_STATUS_CHANGE = 'status_changed';
    public const ACTION_KANBAN_MOVED  = 'kanban_moved';
    public const ACTION_DELIVERY_CONFIRMED = 'delivery_confirmed';

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function fromArea(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'from_area_id');
    }

    public function toArea(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'to_area_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Etiqueta legible de la acción.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED       => 'Orden creada',
            self::ACTION_ASSIGNED      => 'Asignada a área',
            self::ACTION_TRANSFERRED   => 'Transferida entre áreas',
            self::ACTION_COMPLETED     => 'Área completada',
            self::ACTION_RETURNED      => 'Devuelta a área anterior',
            self::ACTION_DELIVERED     => 'Entregada al cliente',
            self::ACTION_CANCELLED     => 'Cancelada',
            self::ACTION_STATUS_CHANGE => 'Cambio de estado',
            self::ACTION_KANBAN_MOVED  => 'Movimiento en Kanban',
            self::ACTION_DELIVERY_CONFIRMED => 'Entrega de área confirmada',
            default                    => $this->action,
        };
    }
}
