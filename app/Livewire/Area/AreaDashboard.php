<?php

namespace App\Livewire\Area;

use App\Enums\KanbanStatus;
use App\Models\Area;
use App\Models\WorkOrderArea;
use App\Services\WorkOrderService;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire AreaDashboard
 *
 * [SOLID - SRP] Dashboard específico de cada área.
 * Muestra:
 * - Resumen de trabajos (por estado Kanban)
 * - Vista de Calendario (mensual/semanal/diario)
 * - Tablero Kanban (Inicio / En Proceso / Finalizado)
 * - Checklist de etapas por orden
 */
#[Layout('layouts.app')]
class AreaDashboard extends Component
{
    public Area $area;
    public string $view = 'kanban'; // kanban | calendar
    public string $calendarMode = 'monthly'; // monthly | weekly | daily
    public ?string $selectedDate = null;

    public function mount(string $slug): void
    {
        $this->area = Area::where('slug', $slug)->firstOrFail();
        $this->selectedDate = now()->format('Y-m-d');

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super usuario', 'Administración'])) {
            // El usuario es un técnico, verificamos si su rol coincide con esta área
            if (!$user->hasRole($this->area->name)) {
                abort(403, 'Aislamiento estricto: No tienes permiso para ver el flujo de trabajo de otra área.');
            }
        }
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function setCalendarMode(string $mode): void
    {
        $this->calendarMode = $mode;
        $this->view = 'calendar';
    }

    public function previousPeriod(): void
    {
        $date = \Carbon\Carbon::parse($this->selectedDate);
        $this->selectedDate = match ($this->calendarMode) {
            'monthly' => $date->subMonth()->format('Y-m-d'),
            'weekly'  => $date->subWeek()->format('Y-m-d'),
            'daily'   => $date->subDay()->format('Y-m-d'),
        };
    }

    public function nextPeriod(): void
    {
        $date = \Carbon\Carbon::parse($this->selectedDate);
        $this->selectedDate = match ($this->calendarMode) {
            'monthly' => $date->addMonth()->format('Y-m-d'),
            'weekly'  => $date->addWeek()->format('Y-m-d'),
            'daily'   => $date->addDay()->format('Y-m-d'),
        };
    }

    public function today(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    /**
     * Completar/descompletar una etapa del checklist.
     */
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

    /**
     * Mover una orden en el Kanban (drag-and-drop simulado).
     */
    public function moveToKanbanColumn(int $workOrderAreaId, string $newStatus): void
    {
        $woa = WorkOrderArea::findOrFail($workOrderAreaId);
        $woa->update([
            'kanban_status' => $newStatus,
            'started_at'    => $newStatus !== 'pending' && !$woa->started_at ? now() : $woa->started_at,
            'completed_at'  => $newStatus === 'completed' ? now() : null,
        ]);
    }

    /**
     * [WEB SOCKETS] Suscribir a canales dinámicos en base al ID del Área.
     */
    public function getListeners(): array
    {
        // Se asegura que área esté inicializada
        if (! isset($this->area) || ! $this->area->id) {
            return [];
        }

        return [
            "echo:area.{$this->area->id},WorkOrderTransferred" => 'refreshDashboard',
            "echo:area.{$this->area->id},AreaStageCompleted"   => 'refreshDashboard',
            "echo:work-orders,WorkOrderStatusChanged"          => 'refreshDashboard',
        ];
    }

    public function refreshDashboard(): void
    {
        // Livewire refresca visualmente al llamar esta función
    }

    public function render()
    {
        $baseQuery = WorkOrderArea::with([
            'workOrder.client',
            'workOrder.assignedTpd',
            'assignedUser',
            'supervisor',
            'stages.areaStage',
            'stages.performer',
        ])->where('area_id', $this->area->id);

        // Para Kanban
        $kanbanItems = [
            'pending'     => (clone $baseQuery)->where('kanban_status', 'pending')->get(),
            'in_progress' => (clone $baseQuery)->where('kanban_status', 'in_progress')->get(),
            'completed'   => (clone $baseQuery)->where('kanban_status', 'completed')->get(),
        ];

        // Para Calendario
        $calendarItems = $this->getCalendarItems($baseQuery);

        // Estadísticas
        $stats = [
            'total'       => (clone $baseQuery)->count(),
            'pending'     => $kanbanItems['pending']->count(),
            'in_progress' => $kanbanItems['in_progress']->count(),
            'completed'   => $kanbanItems['completed']->count(),
        ];

        return view('livewire.area.area-dashboard', [
            'kanbanItems'   => $kanbanItems,
            'calendarItems' => $calendarItems,
            'stats'         => $stats,
        ]);
    }

    private function getCalendarItems($baseQuery): array
    {
        $date = \Carbon\Carbon::parse($this->selectedDate);

        [$start, $end] = match ($this->calendarMode) {
            'monthly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'weekly'  => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'daily'   => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
        };

        $items = (clone $baseQuery)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('started_at', [$start, $end])
                  ->orWhereBetween('created_at', [$start, $end]);
            })
            ->get();

        if ($this->calendarMode === 'daily') {
            return ['items' => $items];
        }

        // Agrupar por fecha
        $grouped = [];
        foreach ($items as $item) {
            $key = ($item->started_at ?? $item->created_at)->format('Y-m-d');
            $grouped[$key][] = $item;
        }

        return $grouped;
    }
}
