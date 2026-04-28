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

    /**
     * Retorna la identidad visual del estado resultante para la trazabilidad (Color y Etiqueta).
     */
    public function getResultStateAttribute(): array
    {
        $state = ['label' => 'Desconocido', 'color' => 'gray'];

        if ($this->action === self::ACTION_CREATED) {
            $state = ['label' => 'Creado', 'color' => 'gray'];
        } elseif (in_array($this->action, [self::ACTION_ASSIGNED, self::ACTION_TRANSFERRED])) {
            $state = ['label' => 'Asignado', 'color' => 'yellow'];
        } elseif ($this->action === self::ACTION_KANBAN_MOVED) {
            if ($this->to_status === 'pending' || str_contains($this->to_status, 'Asignado')) $state = ['label' => 'Asignado', 'color' => 'yellow'];
            elseif ($this->to_status === 'in_progress' || str_contains($this->to_status, 'Desarrollo')) $state = ['label' => 'En desarrollo', 'color' => 'blue'];
            elseif ($this->to_status === 'completed' || str_contains($this->to_status, 'Completado')) $state = ['label' => 'Finalizado', 'color' => 'green'];
        } elseif (in_array($this->action, [self::ACTION_COMPLETED, self::ACTION_DELIVERY_CONFIRMED, self::ACTION_DELIVERED])) {
            $state = ['label' => 'Finalizado', 'color' => 'green'];
        } elseif ($this->action === self::ACTION_CANCELLED) {
            $state = ['label' => 'Bloqueado', 'color' => 'red'];
        } else {
             if ($this->to_status === 'pending') $state = ['label' => 'Asignado', 'color' => 'yellow'];
             elseif ($this->to_status === 'in_progress') $state = ['label' => 'En desarrollo', 'color' => 'blue'];
             elseif ($this->to_status === 'completed') $state = ['label' => 'Finalizado', 'color' => 'green'];
        }
        
        // Clases de Tailwind explícitas para evitar que JIT compiler las descarte durante la compilación
        $classes = match($state['color']) {
            'yellow' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'badge_bg' => 'bg-yellow-100', 'dot' => 'bg-yellow-500'],
            'blue'   => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'badge_bg' => 'bg-blue-100', 'dot' => 'bg-blue-500'],
            'green'  => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700', 'badge_bg' => 'bg-green-100', 'dot' => 'bg-green-500'],
            'red'    => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700', 'badge_bg' => 'bg-red-100', 'dot' => 'bg-red-500'],
            default  => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-700', 'badge_bg' => 'bg-gray-100', 'dot' => 'bg-gray-500'],
        };

        return ['label' => $state['label'], 'css' => $classes];
    }
}
