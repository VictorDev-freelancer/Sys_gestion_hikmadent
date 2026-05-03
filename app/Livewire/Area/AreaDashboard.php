<?php

namespace App\Livewire\Area;

use App\Enums\KanbanStatus;
use App\Models\Area;
use App\Models\WorkOrderArea;
use App\Models\TraceabilityLog;
use App\Services\WorkOrderService;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire AreaDashboard
 *
 * Dashboard por área con:
 *   - Kanban (Inicio / En Proceso / Finalizado)
 *   - FullCalendar (Mensual, Semanal, Diario)
 *   - Historial de trabajos entregados
 *
 * Flujo: Tareas al 100% → Finalizado → Confirmar Entrega → Historial
 */
#[Layout('layouts.app')]
class AreaDashboard extends Component
{
    public Area $area;
    public string $view = 'kanban';

    public function mount(string $slug): void
    {
        $this->area = Area::where('slug', $slug)->firstOrFail();

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super usuario', 'Administración'])) {
            if (!$user->hasRole($this->area->name)) {
                abort(403, 'No tienes permiso para ver esta área.');
            }
        }
    }

    /* ── Navegación ── */

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    /* ── Acciones Kanban ── */

    public function toggleStage(int $stageId): void
    {
        $service = app(WorkOrderService::class);
        $stage = \App\Models\WorkOrderAreaStage::findOrFail($stageId);

        if ($stage->is_completed) {
            $stage->update(['is_completed' => false, 'completed_at' => null]);
        } else {
            $service->completeStage($stage);
        }
    }

    public function moveToKanbanColumn(int $workOrderAreaId, string $newStatus): void
    {
        $woa = WorkOrderArea::findOrFail($workOrderAreaId);
        $oldKanbanStatusValue = $woa->kanban_status->value;

        $woa->update([
            'kanban_status' => $newStatus,
            'started_at'    => $newStatus !== 'pending' && !$woa->started_at ? now() : $woa->started_at,
            'completed_at'  => $newStatus === 'completed' ? now() : null,
        ]);

        TraceabilityLog::create([
            'work_order_id' => $woa->work_order_id,
            'action'        => 'kanban_moved',
            'from_area_id'  => $this->area->id,
            'to_area_id'    => $this->area->id,
            'performed_by'  => auth()->id(),
            'notes'         => "Movimiento dentro del tablero",
            'from_status'   => $oldKanbanStatusValue,
            'to_status'     => $newStatus,
        ]);
    }

    /**
     * Confirmar entrega: mueve la orden al historial.
     * Se marca como 'delivered' (entregada) y se registra en trazabilidad.
     */
    public function confirmDelivery(int $workOrderAreaId): void
    {
        $woa = WorkOrderArea::with('workOrder')->findOrFail($workOrderAreaId);

        // Marcar como entregado con timestamp
        $woa->update([
            'kanban_status' => 'completed',
            'completed_at'  => $woa->completed_at ?? now(),
            'notes'         => trim(($woa->notes ?? '') . ' | Entrega confirmada: ' . now()->format('d/m/Y H:i')),
        ]);

        // Registrar en trazabilidad
        TraceabilityLog::create([
            'work_order_id' => $woa->work_order_id,
            'action'        => 'delivery_confirmed',
            'from_area_id'  => $this->area->id,
            'to_area_id'    => $this->area->id,
            'performed_by'  => auth()->id(),
            'notes'         => "Confirmación de entrega de trabajos finalizados.",
            'from_status'   => $woa->workOrder->status->value,
            'to_status'     => $woa->workOrder->status->value,
        ]);
    }

    /* ── WebSockets ── */

    public function getListeners(): array
    {
        if (!isset($this->area) || !$this->area->id) {
            return [];
        }
        return [
            "echo:area.{$this->area->id},WorkOrderTransferred" => '$refresh',
            "echo:area.{$this->area->id},AreaStageCompleted"   => '$refresh',
            "echo:work-orders,WorkOrderStatusChanged"          => '$refresh',
        ];
    }

    /**
     * Genera eventos para FullCalendar del área.
     */
    private function buildCalendarEvents($items): array
    {
        $kanbanColors = [
            'pending'     => '#3b82f6',
            'in_progress' => '#f59e0b',
            'completed'   => '#10b981',
        ];

        return $items->map(function ($woa) use ($kanbanColors) {
            $date = $woa->started_at ?? $woa->created_at;
            $wo = $woa->workOrder;
            $isDelayed = $wo->delivery_date && $wo->delivery_date->isPast();

            return [
                'id'              => $woa->id,
                'title'           => $wo->code . ' — ' . ($wo->patient_name ?? 'Sin paciente'),
                'start'           => $date->format('Y-m-d'),
                'backgroundColor' => $isDelayed ? '#ef4444' : ($kanbanColors[$woa->kanban_status->value] ?? '#6b7280'),
                'borderColor'     => ($wo->priority?->value === 'urgent') ? '#ef4444' : 'transparent',
                'textColor'       => '#ffffff',
                'url'             => route('work-orders.show', $wo),
                'extendedProps'   => [
                    'code'         => $wo->code,
                    'patient'      => $wo->patient_name,
                    'doctor'       => $wo->doctor_name,
                    'status'       => $woa->kanban_status->label(),
                    'priority'     => $wo->priority?->label() ?? 'Normal',
                    'priorityVal'  => $wo->priority?->value ?? 'normal',
                    'deliveryDate' => $wo->delivery_date?->format('d/m/Y'),
                    'isDelayed'    => $isDelayed,
                    'technician'   => $woa->assignedUser?->name ?? '—',
                ],
            ];
        })->values()->toArray();
    }

    /* ── Render ── */

    public function render()
    {
        $eagerLoads = [
            'workOrder.client',
            'workOrder.assignedTpd',
            'assignedUser',
            'supervisor',
            'stages.areaStage',
            'stages.performer',
        ];

        // Estados terminales de la WorkOrder global
        $terminalStatuses = ['completed', 'delivered', 'cancelled'];

        $baseQuery = WorkOrderArea::with($eagerLoads)
            ->where('area_id', $this->area->id)
            ->whereHas('workOrder', function ($q) use ($terminalStatuses) {
                $q->whereNotIn('status', $terminalStatuses);
            });

        // Base query sin filtro de estado global (para historial)
        $allAreaQuery = WorkOrderArea::with($eagerLoads)
            ->where('area_id', $this->area->id);

        // Kanban: solo órdenes cuya WorkOrder NO está en estado terminal
        $kanbanItems = [
            'pending'     => (clone $baseQuery)->where('kanban_status', 'pending')->get(),
            'in_progress' => (clone $baseQuery)->where('kanban_status', 'in_progress')->get(),
            'completed'   => (clone $baseQuery)
                ->where('kanban_status', 'completed')
                ->where(function ($q) {
                    $q->whereNull('notes')
                      ->orWhere('notes', 'not like', '%Entrega confirmada%');
                })->get(),
        ];

        // Historial: órdenes con entrega confirmada (incluye estados terminales)
        $historyItems = (clone $allAreaQuery)
            ->where('kanban_status', 'completed')
            ->whereNotNull('completed_at')
            ->where('notes', 'like', '%Entrega confirmada%')
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        // Estadísticas
        $stats = [
            'total'       => (clone $allAreaQuery)->count(),
            'pending'     => $kanbanItems['pending']->count(),
            'in_progress' => $kanbanItems['in_progress']->count(),
            'completed'   => $kanbanItems['completed']->count(),
            'delivered'   => $historyItems->count(),
        ];

        // Eventos FullCalendar (todas las OTs activas del área)
        $allActiveItems = (clone $baseQuery)->get();
        $calendarEvents = $this->buildCalendarEvents($allActiveItems);

        return view('livewire.area.area-dashboard', [
            'kanbanItems'    => $kanbanItems,
            'historyItems'   => $historyItems,
            'stats'          => $stats,
            'calendarEvents' => $calendarEvents,
        ]);
    }
}
