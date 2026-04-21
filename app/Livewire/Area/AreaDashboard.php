<?php

namespace App\Livewire\Area;

use App\Enums\KanbanStatus;
use App\Models\Area;
use App\Models\WorkOrderArea;
use App\Models\TraceabilityLog;
use App\Services\WorkOrderService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire AreaDashboard
 *
 * Dashboard por área con:
 *   - Fila 1: Kanban (Inicio / En Proceso / Finalizado)
 *   - Fila 2: Historial de trabajos entregados
 *   - Tabs: Kanban, Mensual, Semanal, Diario
 *
 * Flujo: Tareas al 100% → Finalizado → Confirmar Entrega → Historial
 */
#[Layout('layouts.app')]
class AreaDashboard extends Component
{
    public Area $area;
    public string $view = 'kanban';
    public ?string $selectedDate = null;

    public function mount(string $slug): void
    {
        $this->area = Area::where('slug', $slug)->firstOrFail();
        $this->selectedDate = now()->format('Y-m-d');

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
        $woa->update([
            'kanban_status' => $newStatus,
            'started_at'    => $newStatus !== 'pending' && !$woa->started_at ? now() : $woa->started_at,
            'completed_at'  => $newStatus === 'completed' ? now() : null,
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

        // Marcar flag de entrega (usamos el campo notes para distinguir entregados)
        // El historial muestra los que tienen completed_at y notes con "Entrega confirmada"
        
        // Registrar en trazabilidad
        TraceabilityLog::create([
            'work_order_id' => $woa->work_order_id,
            'action'        => 'delivery_confirmed',
            'from_area_id'  => $this->area->id,
            'to_area_id'    => null,
            'performed_by'  => auth()->id(),
            'details'       => "Entrega confirmada en área: {$this->area->name}",
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

        $baseQuery = WorkOrderArea::with($eagerLoads)
            ->where('area_id', $this->area->id);

        // Kanban: solo órdenes NO entregadas
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

        // Historial: órdenes con entrega confirmada
        $historyItems = (clone $baseQuery)
            ->where('kanban_status', 'completed')
            ->whereNotNull('completed_at')
            ->where('notes', 'like', '%Entrega confirmada%')
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        // Estadísticas
        $stats = [
            'total'       => (clone $baseQuery)->count(),
            'pending'     => $kanbanItems['pending']->count(),
            'in_progress' => $kanbanItems['in_progress']->count(),
            'completed'   => $kanbanItems['completed']->count(),
            'delivered'   => $historyItems->count(),
        ];

        // Calendario
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

    /* ── Calendario Mensual ── */

    private function buildMonthGrid($query): array
    {
        $date = Carbon::parse($this->selectedDate);
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd   = $date->copy()->endOfMonth();
        $gridStart  = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd    = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $items = (clone $query)->where(function ($q) use ($gridStart, $gridEnd) {
            $q->whereBetween('started_at', [$gridStart, $gridEnd])
              ->orWhere(fn($q2) => $q2->whereNull('started_at')->whereBetween('created_at', [$gridStart, $gridEnd]));
        })->get();

        $byDate = [];
        foreach ($items as $item) {
            $byDate[($item->started_at ?? $item->created_at)->format('Y-m-d')][] = $item;
        }

        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $key = $cursor->format('Y-m-d');
                $week[] = [
                    'date' => $cursor->copy(), 'inMonth' => $cursor->month === $monthStart->month,
                    'isToday' => $cursor->isToday(), 'items' => $byDate[$key] ?? [],
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }
        return $weeks;
    }

    /* ── Calendario Semanal ── */

    private function buildWeekGrid($query): array
    {
        $date = Carbon::parse($this->selectedDate);
        $start = $date->copy()->startOfWeek(Carbon::MONDAY);
        $end   = $date->copy()->endOfWeek(Carbon::SUNDAY);

        $items = (clone $query)->where(function ($q) use ($start, $end) {
            $q->whereBetween('started_at', [$start, $end])
              ->orWhere(fn($q2) => $q2->whereNull('started_at')->whereBetween('created_at', [$start, $end]));
        })->get();

        $byDate = [];
        foreach ($items as $item) {
            $byDate[($item->started_at ?? $item->created_at)->format('Y-m-d')][] = $item;
        }

        $days = [];
        $cursor = $start->copy();
        for ($d = 0; $d < 7; $d++) {
            $key = $cursor->format('Y-m-d');
            $days[] = ['date' => $cursor->copy(), 'isToday' => $cursor->isToday(), 'items' => $byDate[$key] ?? []];
            $cursor->addDay();
        }
        return $days;
    }

    /* ── Calendario Diario ── */

    private function buildDaySchedule($query)
    {
        $date = Carbon::parse($this->selectedDate);
        return (clone $query)->where(function ($q) use ($date) {
            $q->whereDate('started_at', $date)
              ->orWhere(fn($q2) => $q2->whereNull('started_at')->whereDate('created_at', $date));
        })->get();
    }
}
