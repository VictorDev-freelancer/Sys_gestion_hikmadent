<?php

namespace App\Services;

use App\Enums\KanbanStatus;
use App\Enums\WorkOrderStatus;
use App\Events\WorkOrderTransferred;
use App\Models\Area;
use App\Models\AreaStage;
use App\Models\TraceabilityLog;
use App\Models\WorkOrder;
use App\Models\WorkOrderArea;
use App\Models\WorkOrderAreaStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service WorkOrderService
 *
 * [SOLID - SRP] Servicio central que orquesta el ciclo de vida
 * completo de una Orden de Trabajo:
 *
 * 1. Creación desde Administración
 * 2. Asignación a áreas
 * 3. Transiciones de estado
 * 4. Transferencia entre áreas (con trazabilidad)
 * 5. Inicialización del checklist por área
 * 6. Completar etapas del checklist
 *
 * [SOLID - OCP] Las áreas y etapas vienen de BD,
 * no están hardcodeadas aquí.
 */
class WorkOrderService
{
    /* ================================================================== */
    /*  1. CREACIÓN                                                        */
    /* ================================================================== */

    /**
     * Crea una nueva Orden de Trabajo.
     *
     * @param array $data Datos del formulario de Administración.
     * @return WorkOrder
     */
    public function create(array $data): WorkOrder
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = $data['created_by'] ?? Auth::id();
            $data['status']     = WorkOrderStatus::DRAFT;

            return WorkOrder::create($data);
        });
    }

    /* ================================================================== */
    /*  2. TRANSICIONES DE ESTADO                                          */
    /* ================================================================== */

    /**
     * Cambia el estado global de la orden.
     * Solo permite transiciones válidas definidas en el Enum.
     */
    public function transitionTo(WorkOrder $workOrder, WorkOrderStatus $newStatus): WorkOrder
    {
        if (! $workOrder->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Transición inválida: {$workOrder->status->label()} → {$newStatus->label()}"
            );
        }

        $workOrder->update(['status' => $newStatus]);

        return $workOrder->fresh();
    }

    /**
     * Registra la orden → pasa de Borrador a Registrada.
     */
    public function register(WorkOrder $workOrder): WorkOrder
    {
        return $this->transitionTo($workOrder, WorkOrderStatus::REGISTERED);
    }

    /* ================================================================== */
    /*  3. ASIGNACIÓN A ÁREAS                                              */
    /* ================================================================== */

    /**
     * Asigna una orden a un área específica.
     * Crea el pivote enriquecido + inicializa el checklist de etapas.
     *
     * @param WorkOrder  $workOrder
     * @param Area       $area         Área destino
     * @param int|null   $assignedTo   Técnico responsable
     * @param int|null   $supervisorId Doctor/supervisor del proceso
     */
    public function assignToArea(
        WorkOrder $workOrder,
        Area $area,
        ?int $assignedTo = null,
        ?int $supervisorId = null,
    ): WorkOrderArea {
        return DB::transaction(function () use ($workOrder, $area, $assignedTo, $supervisorId) {
            // Si la orden está en borrador o registrada, ponerla en progreso
            if ($workOrder->status === WorkOrderStatus::DRAFT) {
                $this->transitionTo($workOrder, WorkOrderStatus::REGISTERED);
                $workOrder->refresh();
            }
            if ($workOrder->status === WorkOrderStatus::REGISTERED) {
                $this->transitionTo($workOrder, WorkOrderStatus::IN_PROGRESS);
                $workOrder->refresh();
            }

            // Actualizar área actual
            $workOrder->update([
                'current_area_id' => $area->id,
                'kanban_status'   => KanbanStatus::PENDING,
            ]);

            // Crear pivote enriquecido
            $workOrderArea = WorkOrderArea::create([
                'work_order_id' => $workOrder->id,
                'area_id'       => $area->id,
                'assigned_to'   => $assignedTo,
                'supervisor_id' => $supervisorId,
                'kanban_status' => KanbanStatus::PENDING,
                'started_at'    => now(),
            ]);

            // Inicializar checklist con todas las etapas del área
            $this->initializeAreaChecklist($workOrderArea);

            return $workOrderArea;
        });
    }

    /* ================================================================== */
    /*  4. TRANSFERENCIA ENTRE ÁREAS                                       */
    /* ================================================================== */

    /**
     * Transfiere una orden del área actual a otra área.
     * Completa el área origen y crea nueva asignación en área destino.
     */
    public function transferToArea(
        WorkOrder $workOrder,
        Area $targetArea,
        ?int $assignedTo = null,
        ?int $supervisorId = null,
        ?string $notes = null,
    ): WorkOrderArea {
        return DB::transaction(function () use ($workOrder, $targetArea, $assignedTo, $supervisorId, $notes) {
            $fromArea = $workOrder->currentArea;

            // Completar el área actual
            $currentWorkOrderArea = $workOrder->workOrderAreas()
                ->where('area_id', $workOrder->current_area_id)
                ->whereNull('completed_at')
                ->latest()
                ->first();

            if ($currentWorkOrderArea) {
                $currentWorkOrderArea->update([
                    'completed_at'  => now(),
                    'kanban_status' => KanbanStatus::COMPLETED,
                ]);
            }

            // Registrar transferencia en trazabilidad
            TraceabilityLog::create([
                'work_order_id' => $workOrder->id,
                'from_area_id'  => $fromArea?->id,
                'to_area_id'    => $targetArea->id,
                'performed_by'  => Auth::id() ?? $workOrder->created_by,
                'action'        => TraceabilityLog::ACTION_TRANSFERRED,
                'from_status'   => $workOrder->status->value,
                'to_status'     => $workOrder->status->value,
                'notes'         => $notes,
            ]);

            // Asignar a nueva área
            return $this->assignToArea($workOrder, $targetArea, $assignedTo, $supervisorId);
        });
    }

    /* ================================================================== */
    /*  5. CHECKLIST — Completar etapas                                    */
    /* ================================================================== */

    /**
     * Marca una etapa del checklist como completada.
     */
    public function completeStage(
        WorkOrderAreaStage $stage,
        ?int $performedBy = null,
        ?string $notes = null,
    ): WorkOrderAreaStage {
        $stage->update([
            'is_completed' => true,
            'performed_by' => $performedBy ?? Auth::id(),
            'completed_at' => now(),
            'notes'        => $notes,
        ]);

        // Verificar si el área se completó 100%
        $workOrderArea = $stage->workOrderArea;
        if ($workOrderArea->isFullyCompleted()) {
            $workOrderArea->update([
                'kanban_status' => KanbanStatus::COMPLETED,
                'completed_at'  => now(),
            ]);
        }

        return $stage->fresh();
    }

    /**
     * Inicia una etapa del checklist (marca en progreso).
     */
    public function startStage(
        WorkOrderAreaStage $stage,
        ?int $performedBy = null,
    ): WorkOrderAreaStage {
        $stage->update([
            'started_at'   => now(),
            'performed_by' => $performedBy ?? Auth::id(),
        ]);

        // Actualizar el Kanban del área a "En Proceso"
        $workOrderArea = $stage->workOrderArea;
        if ($workOrderArea->kanban_status === KanbanStatus::PENDING) {
            $workOrderArea->update(['kanban_status' => KanbanStatus::IN_PROGRESS]);
        }

        return $stage->fresh();
    }

    /* ================================================================== */
    /*  6. FINALIZACIÓN Y ENTREGA                                          */
    /* ================================================================== */

    /**
     * Marca la orden como completada (todas las áreas finalizaron).
     */
    public function complete(WorkOrder $workOrder): WorkOrder
    {
        $order = $this->transitionTo($workOrder, WorkOrderStatus::COMPLETED);

        // Forzar a Completado las áreas activas que sigan pendientes
        $activeAreas = $workOrder->workOrderAreas()
            ->where('kanban_status', '!=', 'completed')
            ->get();

        foreach ($activeAreas as $woa) {
            $woa->update([
                'kanban_status' => 'completed',
                'completed_at'  => now(),
            ]);
        }

        return $order;
    }

    /**
     * Marca la orden como entregada al cliente.
     */
    public function deliver(WorkOrder $workOrder): WorkOrder
    {
        $order = $this->transitionTo($workOrder, WorkOrderStatus::DELIVERED);

        // Confirmar entrega en el Kanban del área activa para que pase a su historial
        $activeAreas = $workOrder->workOrderAreas()
            ->where(function ($q) {
                $q->whereNull('notes')
                  ->orWhere('notes', 'not like', '%Entrega confirmada%');
            })
            ->get();

        foreach ($activeAreas as $woa) {
            $woa->update([
                'kanban_status' => 'completed',
                'completed_at'  => $woa->completed_at ?? now(),
                'notes'         => trim(($woa->notes ?? '') . ' | Entrega confirmada: ' . now()->format('d/m/Y H:i')),
            ]);
        }

        TraceabilityLog::create([
            'work_order_id' => $workOrder->id,
            'from_area_id'  => $workOrder->current_area_id,
            'to_area_id'    => null,
            'performed_by'  => Auth::id() ?? $workOrder->created_by,
            'action'        => TraceabilityLog::ACTION_DELIVERY_CONFIRMED,
            'from_status'   => 'completed',
            'to_status'     => 'delivered',
            'notes'         => 'Orden entregada al cliente',
        ]);

        return $order;
    }

    /**
     * Cancela la orden.
     */
    public function cancel(WorkOrder $workOrder, ?string $reason = null): WorkOrder
    {
        $order = $this->transitionTo($workOrder, WorkOrderStatus::CANCELLED);

        TraceabilityLog::create([
            'work_order_id' => $workOrder->id,
            'from_area_id'  => $workOrder->current_area_id,
            'to_area_id'    => null,
            'performed_by'  => Auth::id() ?? $workOrder->created_by,
            'action'        => TraceabilityLog::ACTION_CANCELLED,
            'from_status'   => $workOrder->getOriginal('status'),
            'to_status'     => 'cancelled',
            'notes'         => $reason,
        ]);

        return $order;
    }

    /* ================================================================== */
    /*  PRIVATE HELPERS                                                     */
    /* ================================================================== */

    /**
     * Inicializa el checklist del área con todas sus etapas.
     */
    private function initializeAreaChecklist(WorkOrderArea $workOrderArea): void
    {
        $stages = AreaStage::where('area_id', $workOrderArea->area_id)
            ->orderBy('display_order')
            ->get();

        foreach ($stages as $stage) {
            WorkOrderAreaStage::firstOrCreate(
                [
                    'work_order_area_id' => $workOrderArea->id,
                    'area_stage_id'      => $stage->id,
                ],
                [
                    'is_completed' => false,
                ]
            );
        }
    }
}
