<?php

namespace App\Livewire\Admin;

use App\Models\WorkOrder;
use App\Models\WorkOrderArea;
use App\Models\Area;
use App\Models\TraceabilityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire AdminDashboard
 *
 * [SOLID - SRP] Panel analítico central para roles de Administración.
 * Cumple con el requerimiento de visibilidad global y métricas.
 *
 * Vistas disponibles:
 *   - dashboard: KPIs, alertas, radar en vivo, historial
 *   - monthly:   Calendario mensual con OTs pendientes
 *   - weekly:    Calendario semanal con detalle por día
 *   - daily:     Agenda diaria con checklist completo
 */
#[Layout('layouts.app')]
class AdminDashboard extends Component
{
    public string $view = 'dashboard';
    public ?string $selectedDate = null;

    public function mount()
    {
        $user = auth()->user();
        $this->selectedDate = now()->format('Y-m-d');
        
        // Si no es un rol gerencial, se asume que es un Técnico (aislado)
        if (!$user->hasAnyRole(['Super usuario', 'Administración'])) {
            $roleName = $user->roles->first()?->name;
            if ($roleName) {
                $area = Area::where('name', $roleName)->first();
                if ($area) {
                    return redirect()->route('area.dashboard', $area->slug);
                }
            }
            // Fallback (sin rol reconocido)
            abort(403, 'No tienes un área operativa asignada o perfil de administrador.');
        }
    }

    /* ── Navegación entre vistas ── */

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

    /* ── Render ── */

    public function render()
    {
        // Estados terminales
        $terminalStatuses = ['completed', 'delivered', 'cancelled'];

        // ── Datos del Dashboard principal ──
        $totalOrders = WorkOrder::count();
        $inProgress  = WorkOrder::where('status', 'in_progress')->count();
        $completed   = WorkOrder::where('status', 'completed')->count();
        
        $delayedOrders = WorkOrder::whereNotIn('status', $terminalStatuses)
            ->whereNotNull('delivery_date')
            ->where('delivery_date', '<', now())
            ->get();

        $urgentOrders = WorkOrder::whereNotIn('status', $terminalStatuses)
            ->where('priority', 'urgent')
            ->get();

        $bottlenecks = Area::withCount(['workOrderAreas' => function ($query) {
                $query->where('kanban_status', '!=', 'completed');
            }])
            ->orderByDesc('work_order_areas_count')
            ->get();

        $prostheticDist = WorkOrder::select('prosthetic_type', DB::raw('count(*) as total'))
            ->groupBy('prosthetic_type')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->prosthetic_type instanceof \App\Enums\ProstheticType 
                                ? $item->prosthetic_type->label() 
                                : \App\Enums\ProstheticType::from($item->prosthetic_type)->label(),
                    'total' => $item->total,
                ];
            });

        // ── Datos del Calendario (solo órdenes activas) ──
        $calendarBaseQuery = WorkOrder::with(['currentArea', 'client', 'assignedTpd'])
            ->whereNotIn('status', $terminalStatuses);

        $monthGrid   = $this->view === 'monthly' ? $this->buildMonthGrid(clone $calendarBaseQuery) : [];
        $weekGrid    = $this->view === 'weekly'  ? $this->buildWeekGrid(clone $calendarBaseQuery)  : [];
        $daySchedule = $this->view === 'daily'   ? $this->buildDaySchedule(clone $calendarBaseQuery) : collect();

        // Estadísticas del calendario
        $calendarStats = [
            'active'   => (clone $calendarBaseQuery)->count(),
            'urgent'   => (clone $calendarBaseQuery)->where('priority', 'urgent')->count(),
            'delayed'  => $delayedOrders->count(),
            'today'    => (clone $calendarBaseQuery)
                            ->whereDate('delivery_date', today())
                            ->count(),
        ];

        return view('livewire.admin.admin-dashboard', [
            'totalOrders'    => $totalOrders,
            'inProgress'     => $inProgress,
            'completed'      => $completed,
            'delayedOrders'  => $delayedOrders,
            'urgentOrders'   => $urgentOrders,
            'bottlenecks'    => $bottlenecks,
            'prostheticDist' => $prostheticDist,
            'recentLogs'     => TraceabilityLog::with(['workOrder', 'performer', 'fromArea', 'toArea'])
                                ->latest()
                                ->take(10)
                                ->get(),
            'globalHistoryItems' => WorkOrderArea::with(['workOrder', 'area', 'assignedUser'])
                                ->where('kanban_status', 'completed')
                                ->whereNotNull('completed_at')
                                ->where('notes', 'like', '%Entrega confirmada%')
                                ->orderByDesc('completed_at')
                                ->take(50)
                                ->get(),
            // Calendario
            'monthGrid'      => $monthGrid,
            'weekGrid'       => $weekGrid,
            'daySchedule'    => $daySchedule,
            'calendarStats'  => $calendarStats,
            'areas'          => Area::active()->ordered()->get(),
        ]);
    }

    /* ================================================================== */
    /*  CALENDARIO MENSUAL                                                 */
    /* ================================================================== */

    private function buildMonthGrid($query): array
    {
        $date = Carbon::parse($this->selectedDate);
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd   = $date->copy()->endOfMonth();
        $gridStart  = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd    = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        // Obtener órdenes activas cuya fecha de entrega cae en el rango
        $orders = (clone $query)->where(function ($q) use ($gridStart, $gridEnd) {
            $q->whereBetween('delivery_date', [$gridStart, $gridEnd])
              ->orWhere(function ($q2) use ($gridStart, $gridEnd) {
                  $q2->whereNull('delivery_date')
                     ->whereBetween('order_date', [$gridStart, $gridEnd]);
              })
              ->orWhere(function ($q3) use ($gridStart, $gridEnd) {
                  $q3->whereNull('delivery_date')
                     ->whereNull('order_date')
                     ->whereBetween('created_at', [$gridStart, $gridEnd]);
              });
        })->get();

        // Agrupar por fecha
        $byDate = [];
        foreach ($orders as $order) {
            $dateKey = ($order->delivery_date ?? $order->order_date ?? $order->created_at)->format('Y-m-d');
            $byDate[$dateKey][] = $order;
        }

        // Construir grid semana por semana
        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $key = $cursor->format('Y-m-d');
                $week[] = [
                    'date'    => $cursor->copy(),
                    'inMonth' => $cursor->month === $monthStart->month,
                    'isToday' => $cursor->isToday(),
                    'items'   => $byDate[$key] ?? [],
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }
        return $weeks;
    }

    /* ================================================================== */
    /*  CALENDARIO SEMANAL                                                 */
    /* ================================================================== */

    private function buildWeekGrid($query): array
    {
        $date = Carbon::parse($this->selectedDate);
        $start = $date->copy()->startOfWeek(Carbon::MONDAY);
        $end   = $date->copy()->endOfWeek(Carbon::SUNDAY);

        $orders = (clone $query)->where(function ($q) use ($start, $end) {
            $q->whereBetween('delivery_date', [$start, $end])
              ->orWhere(function ($q2) use ($start, $end) {
                  $q2->whereNull('delivery_date')
                     ->whereBetween('order_date', [$start, $end]);
              })
              ->orWhere(function ($q3) use ($start, $end) {
                  $q3->whereNull('delivery_date')
                     ->whereNull('order_date')
                     ->whereBetween('created_at', [$start, $end]);
              });
        })->get();

        $byDate = [];
        foreach ($orders as $order) {
            $dateKey = ($order->delivery_date ?? $order->order_date ?? $order->created_at)->format('Y-m-d');
            $byDate[$dateKey][] = $order;
        }

        $days = [];
        $cursor = $start->copy();
        for ($d = 0; $d < 7; $d++) {
            $key = $cursor->format('Y-m-d');
            $days[] = [
                'date'    => $cursor->copy(),
                'isToday' => $cursor->isToday(),
                'items'   => $byDate[$key] ?? [],
            ];
            $cursor->addDay();
        }
        return $days;
    }

    /* ================================================================== */
    /*  CALENDARIO DIARIO                                                  */
    /* ================================================================== */

    private function buildDaySchedule($query)
    {
        $date = Carbon::parse($this->selectedDate);

        return (clone $query)->where(function ($q) use ($date) {
            $q->whereDate('delivery_date', $date)
              ->orWhere(function ($q2) use ($date) {
                  $q2->whereNull('delivery_date')
                     ->whereDate('order_date', $date);
              })
              ->orWhere(function ($q3) use ($date) {
                  $q3->whereNull('delivery_date')
                     ->whereNull('order_date')
                     ->whereDate('created_at', $date);
              });
        })->get();
    }
}
