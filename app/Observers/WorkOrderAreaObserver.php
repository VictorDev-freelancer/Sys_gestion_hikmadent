<?php

namespace App\Observers;

use App\Models\WorkOrderArea;
use App\Models\AuditLog;
use App\Models\TraceabilityLog;
use App\Enums\KanbanStatus;
use App\Events\WorkOrderTransferred;
use Illuminate\Support\Facades\Auth;

/**
 * Observer WorkOrderAreaObserver
 *
 * [SOLID - SRP] Reacciona a cambios en la asignación de áreas.
 *
 * Detecta cuándo un área completa su trabajo y puede activar
 * la transferencia automática a la siguiente área.
 */
class WorkOrderAreaObserver
{
    /**
     * Al asignar una orden a un área nueva.
     */
    public function created(WorkOrderArea $workOrderArea): void
    {
        TraceabilityLog::create([
            'work_order_id' => $workOrderArea->work_order_id,
            'from_area_id'  => null,
            'to_area_id'    => $workOrderArea->area_id,
            'performed_by'  => Auth::id() ?? $workOrderArea->workOrder->created_by,
            'action'        => TraceabilityLog::ACTION_ASSIGNED,
            'to_status'     => $workOrderArea->kanban_status->value ?? 'pending',
            'notes'         => 'Trabajo asignado al área: ' . $workOrderArea->area->name,
        ]);

        // Emitir evento de transferencia para notificación
        event(new WorkOrderTransferred(
            workOrder: $workOrderArea->workOrder,
            fromArea: null,
            toArea: $workOrderArea->area,
            performedBy: Auth::id() ?? $workOrderArea->workOrder->created_by,
        ));
    }

    /**
     * Al actualizar el estado de un área.
     */
    public function updated(WorkOrderArea $workOrderArea): void
    {
        $changes = $workOrderArea->getChanges();

        // Si el área se completó → registrar en trazabilidad
        if (array_key_exists('completed_at', $changes) && $workOrderArea->completed_at !== null) {
            TraceabilityLog::create([
                'work_order_id' => $workOrderArea->work_order_id,
                'from_area_id'  => $workOrderArea->area_id,
                'to_area_id'    => null,
                'performed_by'  => Auth::id() ?? $workOrderArea->assigned_to ?? $workOrderArea->workOrder->created_by,
                'action'        => TraceabilityLog::ACTION_COMPLETED,
                'from_status'   => 'in_progress',
                'to_status'     => 'completed',
                'notes'         => 'Área completada: ' . $workOrderArea->area->name,
            ]);
        }

        // Auditoría
        AuditLog::create([
            'auditable_type' => WorkOrderArea::class,
            'auditable_id'   => $workOrderArea->id,
            'user_id'        => Auth::id(),
            'action'         => 'updated',
            'old_values'     => array_intersect_key($workOrderArea->getOriginal(), $changes),
            'new_values'     => $changes,
            'ip_address'     => request()?->ip(),
            'user_agent'     => request()?->userAgent(),
        ]);
    }
}
