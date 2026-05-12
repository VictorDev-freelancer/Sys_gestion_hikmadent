<?php

namespace App\Livewire\Admin;

use App\Models\Area;
use App\Models\WorkOrder;
use App\Models\WorkOrderArea;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Reports extends Component
{
    public $startDate;
    public $endDate;
    public $areaId = '';
    public $period = 'monthly'; // 'weekly' o 'monthly'

    public function mount()
    {
        // Por defecto el mes actual para el exportador
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->endOfMonth()->format('Y-m-d');
    }

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

    public function getKpis()
    {
        $totalOrders = WorkOrder::count();
        $completedOrders = WorkOrder::whereIn('status', ['completed', 'delivered'])->count();
        $totalEarnings = WorkOrder::whereIn('status', ['completed', 'delivered'])->sum('total_price');
        $averageTicket = $completedOrders > 0 ? $totalEarnings / $completedOrders : 0;
        
        // Eficiencia de entrega: % de órdenes finalizadas a tiempo
        $onTimeOrders = WorkOrder::whereIn('status', ['completed', 'delivered'])
            ->whereNotNull('delivery_date')
            ->where(function($q) {
                $q->whereNull('delivery_date')
                  ->orWhere('delivery_date', '>=', DB::raw('created_at'));
            })
            ->count();
        
        $onTimePercentage = $completedOrders > 0 ? ($onTimeOrders / $completedOrders) * 100 : 100;

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'total_earnings' => (float) $totalEarnings,
            'average_ticket' => (float) $averageTicket,
            'on_time_percentage' => (float) $onTimePercentage,
        ];
    }

    public function getCatalogDistribution()
    {
        return WorkOrder::select('catalog_item_id', DB::raw('count(*) as total'))
            ->with('catalogItem')
            ->whereNotNull('catalog_item_id')
            ->groupBy('catalog_item_id')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($wo) {
                return [
                    'label' => $wo->catalogItem?->name ?? 'Desconocido',
                    'total' => $wo->total
                ];
            })->toArray();
    }

    public function getAreaProductivity()
    {
        return WorkOrderArea::select('area_id', DB::raw('count(*) as total'))
            ->with('area')
            ->where('kanban_status', 'completed')
            ->groupBy('area_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($woa) {
                return [
                    'label' => $woa->area?->name ?? 'N/A',
                    'total' => $woa->total
                ];
            })->toArray();
    }

    public function getClientTypeDistribution()
    {
        return WorkOrder::select('client_type', DB::raw('count(*) as total, sum(total_price) as earnings'))
            ->groupBy('client_type')
            ->get()
            ->map(function ($wo) {
                return [
                    'label' => $wo->client_type === 'student' ? 'Estudiantes' : 'Persona Natural / Clínicas',
                    'total' => $wo->total,
                    'earnings' => (float) $wo->earnings
                ];
            })->toArray();
    }

    public function getPriorityDistribution()
    {
        return WorkOrder::select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->get()
            ->map(function ($wo) {
                return [
                    'label' => ucfirst($wo->priority?->value ?? 'normal'),
                    'total' => $wo->total
                ];
            })->toArray();
    }

    public function downloadReport(ReportExportService $exportService)
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
            'areaId'    => 'nullable|exists:areas,id',
        ]);

        return $exportService->exportWorkOrdersToCsv(
            $this->startDate,
            $this->endDate,
            $this->areaId ?: null
        );
    }

    public function render()
    {
        return view('livewire.admin.reports', [
            'areas' => Area::active()->ordered()->get(),
            'kpis' => $this->getKpis(),
            'initialChartData' => $this->getChartData(),
            'catalogDistribution' => $this->getCatalogDistribution(),
            'areaProductivity' => $this->getAreaProductivity(),
            'clientTypeDistribution' => $this->getClientTypeDistribution(),
            'priorityDistribution' => $this->getPriorityDistribution(),
        ]);
    }
}
