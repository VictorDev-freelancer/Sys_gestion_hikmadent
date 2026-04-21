<?php

namespace App\Livewire\Area;

use App\Enums\KanbanStatus;
use App\Models\Area;
use App\Models\WorkOrderArea;
use App\Services\WorkOrderService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

/**
 * Livewire AreaDashboard
 *
 * [SOLID - SRP] Dashboard específico de cada área.
 * Ofrece 5 vistas:
 *   1. Kanban   → Tablero Inicio / En Proceso / Finalizado
 *   2. Mensual  → Grid de calendario mensual real (7 cols × semanas)
 *   3. Semanal  → Grid de 7 días con órdenes por día
 *   4. Diario   → Lista detallada del día con checklist y roles
 *   5. Historial → Órdenes completadas / transferidas a otra área
 */
#[Layout('layouts.app')]
class AreaDashboard extends Component
{
    use WithPagination;

    public Area $area;
    public string $view = 'kanban';
    public string $calendarMode = 'monthly';
    public ?string $selectedDate = null;

    /* ── Filtros de historial ── */
    public string $historySearch = '';
    public ?string $historyFrom = null;
    public ?string $historyTo = null;

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
        if ($view === 'history') {
            $this->resetPage();
        }
    }

    public function setCalendarMode(string $mode): void
    {
        $this->calendarMode = $mode;
        $this->view = 'calendar';
    }

    public function previousPeriod(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $this->selectedDate = match ($this->calendarMode) {
            'monthly' => $date->subMonth()->format('Y-m-d'),
            'weekly'  => $date->subWeek()->format('Y-m-d'),
            'daily'   => $date->subDay()->format('Y-m-d'),
        };
    }

    public function nextPeriod(): void
    {
        $date = Carbon::parse($this->selectedDate);
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

    public function selectDay(string $date): void
    {
        $this->selectedDate = $date;
        $this->calendarMode = 'daily';
        $this->view = 'calendar';
    }

    /* ================================================================== */
    /*  ACCIONES DE KANBAN                                                 */
    /* ================================================================== */

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
     * Mover una orden en el Kanban.
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

        /* ── Kanban: solo órdenes activas (no finalizadas hace >24h) ── */
        $activeQuery = (clone $baseQuery)->where(function ($q) {
            $q->where('kanban_status', '!=', 'completed')
              ->orWhere(function ($q2) {
                  $q2->where('kanban_status', 'completed')
                     ->where('completed_at', '>=', now()->subHours(24));
              });
        });

        $kanbanItems = [
            'pending'     => (clone $activeQuery)->where('kanban_status', 'pending')->get(),
            'in_progress' => (clone $activeQuery)->where('kanban_status', 'in_progress')->get(),
            'completed'   => (clone $activeQuery)->where('kanban_status', 'completed')->get(),
        ];

        /* ── Estadísticas ── */
        $allCount = (clone $baseQuery)->count();
        $stats = [
            'total'       => $allCount,
            'pending'     => (clone $baseQuery)->where('kanban_status', 'pending')->count(),
            'in_progress' => (clone $baseQuery)->where('kanban_status', 'in_progress')->count(),
            'completed'   => (clone $baseQuery)->where('kanban_status', 'completed')->count(),
        ];

        /* ── Calendario ── */
        $monthGrid    = [];
        $weekGrid     = [];
        $daySchedule  = collect();

        if ($this->view === 'calendar') {
            match ($this->calendarMode) {
                'monthly' => $monthGrid   = $this->buildMonthGrid(clone $baseQuery),
                'weekly'  => $weekGrid    = $this->buildWeekGrid(clone $baseQuery),
                'daily'   => $daySchedule = $this->buildDaySchedule(clone $baseQuery),
            };
        }

        /* ── Historial ── */
        $historyItems = null;
        if ($this->view === 'history') {
            $historyQuery = WorkOrderArea::with($eagerLoads)
                ->where('area_id', $this->area->id)
                ->where('kanban_status', 'completed')
                ->whereNotNull('completed_at');

            // Filtro por búsqueda
            if ($this->historySearch) {
                $search = $this->historySearch;
                $historyQuery->whereHas('workOrder', function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('patient_name', 'like', "%{$search}%")
                      ->orWhere('doctor_name', 'like', "%{$search}%");
                });
            }

            // Filtro por rango de fechas
            if ($this->historyFrom) {
                $historyQuery->where('completed_at', '>=', Carbon::parse($this->historyFrom)->startOfDay());
            }
            if ($this->historyTo) {
                $historyQuery->where('completed_at', '<=', Carbon::parse($this->historyTo)->endOfDay());
            }

            $historyItems = $historyQuery->orderByDesc('completed_at')->paginate(15);
        }

        return view('livewire.area.area-dashboard', [
            'kanbanItems'   => $kanbanItems,
            'stats'         => $stats,
            'monthGrid'     => $monthGrid,
            'weekGrid'      => $weekGrid,
            'daySchedule'   => $daySchedule,
            'historyItems'  => $historyItems,
        ]);
    }

    /* ================================================================== */
    /*  BUILDERS DE CALENDARIO                                             */
    /* ================================================================== */

    /**
     * [CALENDARIO MENSUAL]
     * Genera una estructura de semanas × 7 días para renderizar
     * un grid de calendario real con las órdenes dentro de cada celda.
     *
     * @return array [ ['date' => Carbon, 'inMonth' => bool, 'items' => Collection], ... ][]
     */
    private function buildMonthGrid($query): array
    {
        $date = Carbon::parse($this->selectedDate);
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd   = $date->copy()->endOfMonth();

        // Extender para cubrir la semana completa
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        // Traer todas las órdenes del rango visible
        $items = (clone $query)
            ->where(function ($q) use ($gridStart, $gridEnd) {
                $q->whereBetween('started_at', [$gridStart, $gridEnd])
                  ->orWhere(function ($q2) use ($gridStart, $gridEnd) {
                      $q2->whereNull('started_at')
                         ->whereBetween('created_at', [$gridStart, $gridEnd]);
                  });
            })
            ->get();

        // Indexar por fecha
        $itemsByDate = [];
        foreach ($items as $item) {
            $key = ($item->started_at ?? $item->created_at)->format('Y-m-d');
            $itemsByDate[$key][] = $item;
        }

        // Construir el grid semana por semana
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

    /**
     * [CALENDARIO SEMANAL]
     * Genera un arreglo de 7 días (Lun-Dom) con las órdenes de cada día.
     */
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
            })
            ->get();

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

    /**
     * [CALENDARIO DIARIO]
     * Retorna las órdenes del día seleccionado con todos sus datos.
     */
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
            })
            ->get();
    }
}
