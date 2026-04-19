<?php

namespace App\Models;

use App\Enums\KanbanStatus;
use App\Enums\Priority;
use App\Enums\ProstheticType;
use App\Enums\WorkOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Model WorkOrder — Orden de Trabajo
 *
 * [SOLID - SRP] Modelo central del sistema HIKMADENT.
 * Registra todos los datos de la ficha de trabajo según el prompt:
 * Dr.(a), Paciente, Edad, Tipo protésico, Especificaciones, Color, etc.
 *
 * La trazabilidad se logra a través de:
 * - `workOrderAreas` → qué áreas ha recorrido
 * - `traceabilityLogs` → historial completo de movimientos
 * - `auditLogs` → quién hizo qué cambio y cuándo
 *
 * @property string $code Código único auto-generado (OT-YYYYMMDD-XXXX)
 */
class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'client_id',
        'created_by',
        'assigned_tpd_id',
        'doctor_name',
        'clinic_name',
        'patient_name',
        'patient_age',
        'prosthetic_type',
        'specifications',
        'color',
        'quantity',
        'final_work_type',
        'status',
        'kanban_status',
        'priority',
        'order_date',
        'technical_send_date',
        'clinic_delivery_date',
        'delivery_date',
        'current_area_id',
    ];

    protected function casts(): array
    {
        return [
            'prosthetic_type'      => ProstheticType::class,
            'status'               => WorkOrderStatus::class,
            'kanban_status'        => KanbanStatus::class,
            'priority'             => Priority::class,
            'patient_age'          => 'integer',
            'quantity'             => 'integer',
            'order_date'           => 'date',
            'technical_send_date'  => 'date',
            'clinic_delivery_date' => 'date',
            'delivery_date'        => 'date',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  BOOT — Auto-generar código OT                                      */
    /* ------------------------------------------------------------------ */

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $order) {
            if (empty($order->code)) {
                $order->code = self::generateCode();
            }
            if (empty($order->status)) {
                $order->status = WorkOrderStatus::DRAFT;
            }
            if (empty($order->kanban_status)) {
                $order->kanban_status = KanbanStatus::PENDING;
            }
            if (empty($order->priority)) {
                $order->priority = Priority::NORMAL;
            }
        });
    }

    /**
     * Genera código único: OT-20260418-0001
     */
    private static function generateCode(): string
    {
        $today = now()->format('Ymd');
        $prefix = "OT-{$today}-";

        $lastOrder = self::where('code', 'like', "{$prefix}%")
            ->orderByDesc('code')
            ->first();

        $nextNumber = 1;
        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->code, -4);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /* ------------------------------------------------------------------ */
    /*  RELACIONES                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Cliente que solicitó la orden.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Usuario que creó la orden (Administración).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * TPD (Técnico en Prótesis Dental) responsable global.
     */
    public function assignedTpd(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_tpd_id');
    }

    /**
     * Área actual donde se encuentra la orden.
     */
    public function currentArea(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'current_area_id');
    }

    /**
     * Recorrido por áreas (trazabilidad principal).
     */
    public function workOrderAreas(): HasMany
    {
        return $this->hasMany(WorkOrderArea::class)->orderBy('created_at');
    }

    /**
     * Historial de trazabilidad detallado.
     */
    public function traceabilityLogs(): HasMany
    {
        return $this->hasMany(TraceabilityLog::class)->orderByDesc('created_at');
    }

    /**
     * Logs de auditoría (polimórficos).
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Verifica si la orden puede transicionar a un estado dado.
     */
    public function canTransitionTo(WorkOrderStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }

    /**
     * Retorna el porcentaje de avance basado en etapas completadas.
     */
    public function getProgressAttribute(): int
    {
        $totalStages = $this->workOrderAreas()
            ->withCount(['stages as total' => function ($q) { /* all */ },
                         'stages as completed' => function ($q) {
                             $q->whereNotNull('completed_at');
                         }])
            ->get();

        $total = $totalStages->sum('total');
        $completed = $totalStages->sum('completed');

        return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }
}
