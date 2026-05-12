<?php

namespace App\Livewire\Admin;

use App\Models\WorkOrder;
use App\Models\WorkOrderArea;
use App\Models\Area;
use App\Models\TraceabilityLog;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire AdminDashboard
 *
 * [SOLID - SRP] Panel analítico central para roles de Administración.
 * Incluye: KPIs, alertas, radar en vivo, historial y FullCalendar
 * con vistas mensual, semanal y diaria de órdenes pendientes.
 */
#[Layout('layouts.app')]
class AdminDashboard extends Component
{
    public function mount()
    {
        $user = auth()->user();
        
        if (!$user->hasAnyRole(['Super usuario', 'Administración'])) {
            $roleName = $user->roles->first()?->name;
            if ($roleName) {
                $area = Area::where('name', $roleName)->first();
                if ($area) {
                    return redirect()->route('area.dashboard', $area->slug);
                }
            }
            abort(403, 'No tienes un área operativa asignada o perfil de administrador.');
        }
    }

    /**
     * Genera los eventos para FullCalendar en formato JSON.
     * Incluye solo órdenes activas (no terminales).
     */
    public function getCalendarEventsProperty(): array
    {
        $terminalStatuses = ['completed', 'delivered', 'cancelled'];

        $orders = WorkOrder::with('currentArea')
            ->whereNotIn('status', $terminalStatuses)
            ->get();

        $statusColors = [
            'draft'       => '#9ca3af',
            'registered'  => '#3b82f6',
            'in_progress' => '#f59e0b',
        ];

        $priorityBorder = [
            'urgent' => '#ef4444',
            'high'   => '#f97316',
            'normal' => 'transparent',
            'low'    => 'transparent',
        ];

        return $orders->map(function ($order) use ($statusColors, $priorityBorder) {
            $date = $order->delivery_date ?? $order->order_date ?? $order->created_at;
            $isDelayed = $order->delivery_date && $order->delivery_date->isPast();
            $priorityVal = $order->priority?->value ?? 'normal';

            return [
                'id'              => $order->id,
                'title'           => $order->code . ' — ' . ($order->patient_name ?? 'Sin paciente'),
                'start'           => $date->format('Y-m-d'),
                'backgroundColor' => $isDelayed ? '#ef4444' : ($statusColors[$order->status->value] ?? '#6b7280'),
                'borderColor'     => $priorityBorder[$priorityVal] ?? 'transparent',
                'textColor'       => '#ffffff',
                'url'             => route('work-orders.show', $order),
                'extendedProps'   => [
                    'code'         => $order->code,
                    'patient'      => $order->patient_name,
                    'doctor'       => $order->doctor_name,
                    'area'         => $order->currentArea?->name ?? '—',
                    'areaColor'    => $order->currentArea?->color ?? '#6b7280',
                    'status'       => $order->status->label(),
                    'statusValue'  => $order->status->value,
                    'priority'     => $order->priority?->label() ?? 'Normal',
                    'priorityVal'  => $priorityVal,
                    'deliveryDate' => $order->delivery_date?->format('d/m/Y'),
                    'isDelayed'    => $isDelayed,
                ],
            ];
        })->values()->toArray();
    }

    public $period = 'monthly'; // 'weekly' o 'monthly'

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->dispatch('charts-updated', chartData: $this->getChartData());
    }

    public function getChartData()
    {
        $labels = [];
        $createdData = [];
        $completedData = [];
        $earningsData = [];

        if ($this->period === 'weekly') {
            for ($i = 7; $i >= 0; $i--) {
                $date = now()->subWeeks($i);
                $start = $date->copy()->startOfWeek();
                $end = $date->copy()->endOfWeek();
                
                $labels[] = 'Sem ' . $date->format('W') . ' (' . $start->format('d/m') . ')';
                
                $createdData[] = WorkOrder::whereBetween('created_at', [$start, $end])->count();
                $completedData[] = WorkOrder::whereBetween('created_at', [$start, $end])->whereIn('status', ['completed', 'delivered'])->count();
                $earningsData[] = (float) WorkOrder::whereBetween('created_at', [$start, $end])->whereIn('status', ['completed', 'delivered'])->sum('total_price');
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();
                
                $labels[] = ucfirst($date->locale('es')->shortMonthName) . ' ' . $date->format('y');
                
                $createdData[] = WorkOrder::whereBetween('created_at', [$start, $end])->count();
                $completedData[] = WorkOrder::whereBetween('created_at', [$start, $end])->whereIn('status', ['completed', 'delivered'])->count();
                $earningsData[] = (float) WorkOrder::whereBetween('created_at', [$start, $end])->whereIn('status', ['completed', 'delivered'])->sum('total_price');
            }
        }

        return [
            'labels' => $labels,
            'created' => $createdData,
            'completed' => $completedData,
            'earnings' => $earningsData,
        ];
    }

    public function render()
    {
        $terminalStatuses = ['completed', 'delivered', 'cancelled'];

        $totalOrders = WorkOrder::count();
        $inProgress  = WorkOrder::where('status', 'in_progress')->count();
        $completed   = WorkOrder::where('status', 'completed')->count();
        $totalEarnings = WorkOrder::whereIn('status', ['completed', 'delivered'])->sum('total_price');
        
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
                                : (\App\Enums\ProstheticType::tryFrom($item->prosthetic_type)?->label() ?? 'Otro/Catálogo'),
                    'total' => $item->total,
                ];
            });

        return view('livewire.admin.admin-dashboard', [
            'totalOrders'       => $totalOrders,
            'inProgress'        => $inProgress,
            'completed'         => $completed,
            'totalEarnings'     => $totalEarnings,
            'delayedOrders'     => $delayedOrders,
            'urgentOrders'      => $urgentOrders,
            'bottlenecks'       => $bottlenecks,
            'prostheticDist'    => $prostheticDist,
            'calendarEvents'    => $this->calendarEvents,
            'initialChartData'  => $this->getChartData(),
            'recentLogs'        => TraceabilityLog::with(['workOrder', 'performer', 'fromArea', 'toArea'])
                                    ->latest()->take(10)->get(),
            'globalHistoryItems' => WorkOrderArea::with(['workOrder', 'area', 'assignedUser'])
                                    ->where('kanban_status', 'completed')
                                    ->whereNotNull('completed_at')
                                    ->where('notes', 'like', '%Entrega confirmada%')
                                    ->orderByDesc('completed_at')
                                    ->take(50)->get(),
        ]);
    }
}
