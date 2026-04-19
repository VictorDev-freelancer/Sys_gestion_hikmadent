<?php

namespace App\Livewire\WorkOrder;

use App\Enums\KanbanStatus;
use App\Enums\WorkOrderStatus;
use App\Models\Area;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire WorkOrderDetail
 *
 * [TRAZABILIDAD] Muestra el detalle completo de una Orden de Trabajo:
 * - Datos del paciente/doctor
 * - Recorrido por áreas (timeline visual)
 * - Checklist de cada área con progreso
 * - Historial de trazabilidad completo
 * - Acciones: transferir, completar, entregar, cancelar
 */
#[Layout('layouts.app')]
class WorkOrderDetail extends Component
{
    public WorkOrder $workOrder;

    // Para transferencia
    public bool $showTransferModal = false;
    public ?int $transferAreaId = null;
    public ?int $transferTechnicianId = null;
    public ?int $transferSupervisorId = null;
    public string $transferNotes = '';

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder->load([
            'client',
            'creator',
            'assignedTpd',
            'currentArea',
            'workOrderAreas.area',
            'workOrderAreas.assignedUser',
            'workOrderAreas.supervisor',
            'workOrderAreas.stages.areaStage',
            'workOrderAreas.stages.performer',
            'traceabilityLogs.fromArea',
            'traceabilityLogs.toArea',
            'traceabilityLogs.performer',
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  ACCIONES SOBRE LA ORDEN                                            */
    /* ------------------------------------------------------------------ */

    public function registerOrder(): void
    {
        $service = app(WorkOrderService::class);
        $service->register($this->workOrder);
        $this->refreshWorkOrder();
        session()->flash('message', 'Orden registrada exitosamente.');
    }

    public function completeOrder(): void
    {
        $service = app(WorkOrderService::class);
        $service->complete($this->workOrder);
        $this->refreshWorkOrder();
        session()->flash('message', 'Orden completada exitosamente.');
    }

    public function deliverOrder(): void
    {
        $service = app(WorkOrderService::class);
        $service->deliver($this->workOrder);
        $this->refreshWorkOrder();
        session()->flash('message', 'Orden entregada al cliente.');
    }

    public function cancelOrder(): void
    {
        $service = app(WorkOrderService::class);
        $service->cancel($this->workOrder, 'Cancelada por administración');
        $this->refreshWorkOrder();
        session()->flash('message', 'Orden cancelada.');
    }

    /* ------------------------------------------------------------------ */
    /*  TRANSFERENCIA ENTRE ÁREAS                                          */
    /* ------------------------------------------------------------------ */

    public function openTransferModal(): void
    {
        $this->showTransferModal = true;
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->reset(['transferAreaId', 'transferTechnicianId', 'transferSupervisorId', 'transferNotes']);
    }

    public function transferToArea(): void
    {
        $this->validate([
            'transferAreaId' => 'required|exists:areas,id',
        ]);

        $service = app(WorkOrderService::class);
        $area = Area::findOrFail($this->transferAreaId);

        $service->transferToArea(
            $this->workOrder,
            $area,
            $this->transferTechnicianId,
            $this->transferSupervisorId,
            $this->transferNotes ?: null,
        );

        $this->closeTransferModal();
        $this->refreshWorkOrder();
        session()->flash('message', "Orden transferida a {$area->name}.");
    }

    /* ------------------------------------------------------------------ */
    /*  CHECKLIST — Completar etapas                                       */
    /* ------------------------------------------------------------------ */

    public function toggleStage(int $stageId): void
    {
        $service = app(WorkOrderService::class);
        $stage = \App\Models\WorkOrderAreaStage::findOrFail($stageId);

        if ($stage->is_completed) {
            // Desmarcar
            $stage->update([
                'is_completed' => false,
                'completed_at' => null,
            ]);
        } else {
            $service->completeStage($stage);
        }

        $this->refreshWorkOrder();
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                            */
    /* ------------------------------------------------------------------ */

    private function refreshWorkOrder(): void
    {
        $this->workOrder = $this->workOrder->fresh([
            'client',
            'creator',
            'assignedTpd',
            'currentArea',
            'workOrderAreas.area',
            'workOrderAreas.assignedUser',
            'workOrderAreas.supervisor',
            'workOrderAreas.stages.areaStage',
            'workOrderAreas.stages.performer',
            'traceabilityLogs.fromArea',
            'traceabilityLogs.toArea',
            'traceabilityLogs.performer',
        ]);
    }

    public function render()
    {
        return view('livewire.work-order.work-order-detail', [
            'areas'       => Area::active()->ordered()->get(),
            'technicians' => User::all(),
        ]);
    }
}
