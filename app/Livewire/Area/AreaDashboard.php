<?php

namespace App\Livewire\Area;

use App\Enums\KanbanStatus;
use App\Models\Area;
use App\Models\WorkOrderArea;
use App\Services\WorkOrderService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire AreaDashboard
 *
 * [SOLID - SRP] Dashboard específico de cada área.
 * Ofrece 4 vistas de calendario + Kanban con historial integrado:
 *   1. Kanban   → Tablero con 4 columnas: Inicio / En Proceso / Finalizado / Historial
 *   2. Mensual  → Grid de calendario mensual real (7 cols × semanas)
 *   3. Semanal  → Grid de 7 días con órdenes por día
 *   4. Diario   → Lista detallada del día con checklist
 */
#[Layout('layouts.app')]
class AreaDashboard extends Component
{
    public Area $area;
    public string $view = 'kanban'; // kanban | monthly | weekly | daily
    public ?string $selectedDate = null;

    public function mount(string $slug): void
    {
        $this->area = Area::where('slug', $slug)->firstOrFail();
        $this->selectedDate = now()->format('Y-m-d');

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super usuario', 'Administración'])) {
            if (!$user->hasRole($this->area->name)) {
                abort(403, 'Aislamiento estricto: No tienes permiso para ver el flujo de trabajo de otra área.');
            }
        }
    }

    /* ================================================================== */
    /*  ACCIONES DE NAVEGACIÓN                                             */
    /* ================================================================== */

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function previousPeriod(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $this->selectedDate = match ($this->view) {
            'monthly' => $date->subMonth()->format('Y-m-d'),
            'weekly'  => $date->subWeek()->format('Y-m-d'),
            'daily'   => $date->subDay()->format('Y-m-d'),
            default   => $this->selectedDate,
        };
    }

    public function nextPeriod(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $this->selectedDate = match ($this->view) {
            'monthly' => $date->addMonth()->format('Y-m-d'),
            'weekly'  => $date->addWeek()->format('Y-m-d'),
            'daily'   => $date->addDay()->format('Y-m-d'),
            default   => $this->selectedDate,
        };
    }

    public function today(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function selectDay(string $date): void
    {
        $this->selectedDate = $date;
        $this->view = 'daily';
    }

    /* ================================================================== */
    /*  ACCIONES DE KANBAN                                                 */
    /* ================================================================== */

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
        $woa->update([
            'kanban_status' => $newStatus,
            'started_at'    => $newStatus !== 'pending' && !$woa->started_at ? now() : $woa->started_at,
            'completed_at'  => $newStatus === 'completed' ? now() : null,
        ]);
    }

    /* ================================================================== */
    /*  WEBSOCKETS                                                         */
    /* ================================================================== */

    public function getListeners(): array
    {
        if (!isset($this->area) || !$this->area->id) {
            return [];
        }
        return [
            "echo:area.{$this->area->id},WorkOrderTransferred" => 'refreshDashboard',
            "echo:area.{$this->area->id},AreaStageCompleted"   => 'refreshDashboard',
            "echo:work-orders,WorkOrderStatusChanged"          => 'refreshDashboard',
        ];
    }

    public function refreshDashboard(): void {}

    /* ================================================================== */
    /*  RENDER                                                             */
    /* ================================================================== */

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

        $baseQuery = WorkOrderArea::with($eagerLoads)
            ->where('area_id', $this->area->id);

        /* ── Kanban columns (activos) ── */
        $kanbanItems = [
            'pending'     => (clone $baseQuery)->where('kanban_status', 'pending')->get(),
            'in_progress' => (clone $baseQuery)->where('kanban_status', 'in_progress')->get(),
            'completed'   => (clone $baseQuery)->where('kanban_status', 'completed')
                                ->where(function ($q) {
                                    $q->whereNull('completed_at')
                                      ->orWhere('completed_at', '>=', now()->subHours(24));
                                })->get(),
        ];

        /* ── Historial: completados hace >24h ── */
        $historyItems = (clone $baseQuery)
            ->where('kanban_status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '<', now()->subHours(24))
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        /* ── Estadísticas ── */
        $stats = [
            'total'       => (clone $baseQuery)->count(),
            'pending'     => $kanbanItems['pending']->count(),
            'in_progress' => $kanbanItems['in_progress']->count(),
            'completed'   => (clone $baseQuery)->where('kanban_status', 'completed')->count(),
        ];

        /* ── Calendario ── */
        $monthGrid   = $this->view === 'monthly' ? $this->buildMonthGrid(clone $baseQuery) : [];
        $weekGrid    = $this->view === 'weekly'  ? $this->buildWeekGrid(clone $baseQuery)  : [];
        $daySchedule = $this->view === 'daily'   ? $this->buildDaySchedule(clone $baseQuery) : collect();

        return view('livewire.area.area-dashboard', [
            'kanbanItems'  => $kanbanItems,
            'historyItems' => $historyItems,
            'stats'        => $stats,
            'monthGrid'    => $monthGrid,
            'weekGrid'     => $weekGrid,
            'daySchedule'  => $daySchedule,
        ]);
    }

    /* ================================================================== */
    /*  BUILDERS DE CALENDARIO                                             */
    /* ================================================================== */

    private function buildMonthGrid($query): array
    {
        $date = Carbon::parse($this->selectedDate);
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd   = $date->copy()->endOfMonth();
        $gridStart  = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd    = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $items = (clone $query)
            ->where(function ($q) use ($gridStart, $gridEnd) {
                $q->whereBetween('started_at', [$gridStart, $gridEnd])
                  ->orWhere(function ($q2) use ($gridStart, $gridEnd) {
                      $q2->whereNull('started_at')
                         ->whereBetween('created_at', [$gridStart, $gridEnd]);
                  });
            })->get();

        $itemsByDate = [];
        foreach ($items as $item) {
            $key = ($item->started_at ?? $item->created_at)->format('Y-m-d');
            $itemsByDate[$key][] = $item;
        }

        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $dayKey = $cursor->format('Y-m-d');
                $week[] = [
                    'date'    => $cursor->copy(),
                    'inMonth' => $cursor->month === $monthStart->month,
                    'isToday' => $cursor->isToday(),
                    'items'   => $itemsByDate[$dayKey] ?? [],
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }
        return $weeks;
    }

    private function buildWeekGrid($query): array
    {
        $date = Carbon::parse($this->selectedDate);
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $date->copy()->endOfWeek(Carbon::SUNDAY);

        $items = (clone $query)
            ->where(function ($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('started_at', [$weekStart, $weekEnd])
                  ->orWhere(function ($q2) use ($weekStart, $weekEnd) {
                      $q2->whereNull('started_at')
                         ->whereBetween('created_at', [$weekStart, $weekEnd]);
                  });
            })->get();

        $itemsByDate = [];
        foreach ($items as $item) {
            $key = ($item->started_at ?? $item->created_at)->format('Y-m-d');
            $itemsByDate[$key][] = $item;
        }

        $days = [];
        $cursor = $weekStart->copy();
        for ($d = 0; $d < 7; $d++) {
            $dayKey = $cursor->format('Y-m-d');
            $days[] = [
                'date'    => $cursor->copy(),
                'isToday' => $cursor->isToday(),
                'items'   => $itemsByDate[$dayKey] ?? [],
            ];
            $cursor->addDay();
        }
        return $days;
    }

    private function buildDaySchedule($query)
    {
        $date = Carbon::parse($this->selectedDate);
        return (clone $query)
            ->where(function ($q) use ($date) {
                $q->whereDate('started_at', $date)
                  ->orWhere(function ($q2) use ($date) {
                      $q2->whereNull('started_at')
                         ->whereDate('created_at', $date);
                  });
            })->get();
    }
}
