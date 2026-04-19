<?php

namespace App\Observers;

use App\Models\WorkOrder;
use App\Models\AuditLog;
use App\Models\TraceabilityLog;
use App\Events\WorkOrderStatusChanged;
use Illuminate\Support\Facades\Auth;

/**
 * Observer WorkOrderObserver
 *
 * [SOLID - SRP] Responsabilidad única: reaccionar a cambios en WorkOrder.
 *
 * Se activa automáticamente en:
 * - Creación → Registra auditoría + log de trazabilidad
 * - Actualización → Detecta cambios de estado y área → emite eventos
 */
class WorkOrderObserver
{
    /**
     * Al crear una nueva Orden de Trabajo.
     */
    public function created(WorkOrder $workOrder): void
    {
        // Registrar en trazabilidad
        TraceabilityLog::create([
            'work_order_id' => $workOrder->id,
            'from_area_id'  => null,
            'to_area_id'    => $workOrder->current_area_id,
            'performed_by'  => Auth::id() ?? $workOrder->created_by,
            'action'        => TraceabilityLog::ACTION_CREATED,
            'from_status'   => null,
            'to_status'     => $workOrder->status->value,
            'notes'         => 'Orden de trabajo creada',
        ]);

        // Registrar auditoría
        $this->logAudit($workOrder, 'created', [], $workOrder->toArray());
    }

    /**
     * Al actualizar una Orden de Trabajo.
     */
    public function updated(WorkOrder $workOrder): void
    {
        $changes = $workOrder->getChanges();
        $original = $workOrder->getOriginal();

        // Detectar cambio de estado → emitir evento
        if (array_key_exists('status', $changes)) {
            $fromStatus = $original['status'] ?? 'draft';
            $toStatus = $changes['status'];

            // Registrar en trazabilidad
            TraceabilityLog::create([
                'work_order_id' => $workOrder->id,
                'from_area_id'  => null,
                'to_area_id'    => $workOrder->current_area_id,
                'performed_by'  => Auth::id() ?? $workOrder->created_by,
                'action'        => TraceabilityLog::ACTION_STATUS_CHANGE,
                'from_status'   => is_string($fromStatus) ? $fromStatus : $fromStatus->value ?? $fromStatus,
                'to_status'     => is_string($toStatus) ? $toStatus : $toStatus->value ?? $toStatus,
            ]);

            // Emitir evento para notificaciones en tiempo real
            event(new WorkOrderStatusChanged(
                workOrder: $workOrder,
                fromStatus: is_string($fromStatus) ? $fromStatus : ($fromStatus->value ?? (string) $fromStatus),
                toStatus: is_string($toStatus) ? $toStatus : ($toStatus->value ?? (string) $toStatus),
                performedBy: Auth::id() ?? $workOrder->created_by,
            ));
        }

        // Auditoría de cualquier cambio
        $oldValues = array_intersect_key($original, $changes);
        $this->logAudit($workOrder, 'updated', $oldValues, $changes);
    }

    /**
     * Al eliminar una Orden (soft o hard).
     */
    public function deleted(WorkOrder $workOrder): void
    {
        $this->logAudit($workOrder, 'deleted', $workOrder->toArray(), []);
    }

    /**
     * [SRP] Registrar en audit_logs.
     */
    private function logAudit(WorkOrder $workOrder, string $action, array $oldValues, array $newValues): void
    {
        AuditLog::create([
            'auditable_type' => WorkOrder::class,
            'auditable_id'   => $workOrder->id,
            'user_id'        => Auth::id(),
            'action'         => $action,
            'old_values'     => $oldValues ?: null,
            'new_values'     => $newValues ?: null,
            'ip_address'     => request()?->ip(),
            'user_agent'     => request()?->userAgent(),
        ]);
    }
}
