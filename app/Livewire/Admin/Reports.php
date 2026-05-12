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

/**
 * Reports Component
 *
 * [SOLID - SRP] Proporciona análisis dinámico de negocio (Business Intelligence).
 * Permite filtrar KPIs y 5 gráficos diferentes por rango de fecha y área.
 */
#[Layout('layouts.app')]
class Reports extends Component
{
    public $startDate;
    public $endDate;
    public $areaId = '';

    public function mount()
    {
        // Por defecto: desde el primer día del mes anterior hasta hoy para dar un rango de análisis visible
        $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');
    }

    /**
     * Aplica los filtros de fecha y área, despachando los datos actualizados a todos los gráficos.
     */
    public function applyFilters()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
            'areaId'    => 'nullable|exists:areas,id',
        ]);

        $this->dispatch('charts-updated', chartData: [
            'mainCharts'             => $this->getChartData(),
            'catalogDistribution'    => $this->getCatalogDistribution(),
            'areaProductivity'       => $this->getAreaProductivity(),
            'clientTypeDistribution' => $this->getClientTypeDistribution(),
        ]);
    }

    /**
     * Reajusta los filtros de vuelta a los valores predeterminados y actualiza.
     */
    public function resetFilters()
    {
        $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');
        $this->areaId    = '';

        $this->applyFilters();
    }

    /**
     * Genera datos de producción y finanzas segmentados automáticamente según la escala del tiempo.
     */
    public function getChartData()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();
        
        $diffInDays = $start->diffInDays($end);
        
        $labels = [];
        $createdData = [];
        $completedData = [];
        $earningsData = [];

        if ($diffInDays <= 14) {
            // Escala Diaria (Rango pequeño)
            $current = $start->copy();
            while ($current->lte($end)) {
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();
                
                $labels[] = $current->format('d/m');
                
                $createdQuery = WorkOrder::whereBetween('created_at', [$dayStart, $dayEnd]);
                $completedQuery = WorkOrder::whereBetween('created_at', [$dayStart, $dayEnd])->whereIn('status', ['completed', 'delivered']);

                if ($this->areaId) {
                    $createdQuery->whereHas('workOrderAreas', function ($q) { $q->where('area_id', $this->areaId); });
                    $completedQuery->whereHas('workOrderAreas', function ($q) { $q->where('area_id', $this->areaId); });
                }

                $createdData[]   = $createdQuery->count();
                $completedData[] = $completedQuery->count();
                $earningsData[]  = (float) $completedQuery->sum('total_price');
                
                $current->addDay();
            }
        } elseif ($diffInDays <= 90) {
            // Escala Semanal (Rango mediano)
            $current = $start->copy();
            while ($current->lte($end)) {
                $weekStart = $current->copy()->startOfWeek();
                $weekEnd = $current->copy()->endOfWeek();
                
                if ($weekStart->lt($start)) $weekStart = $start->copy();
                if ($weekEnd->gt($end)) $weekEnd = $end->copy();
                
                $labels[] = 'Sem ' . $current->format('W') . ' (' . $weekStart->format('d/m') . ')';
                
                $createdQuery = WorkOrder::whereBetween('created_at', [$weekStart, $weekEnd]);
                $completedQuery = WorkOrder::whereBetween('created_at', [$weekStart, $weekEnd])->whereIn('status', ['completed', 'delivered']);

                if ($this->areaId) {
                    $createdQuery->whereHas('workOrderAreas', function ($q) { $q->where('area_id', $this->areaId); });
                    $completedQuery->whereHas('workOrderAreas', function ($q) { $q->where('area_id', $this->areaId); });
                }

                $createdData[]   = $createdQuery->count();
                $completedData[] = $completedQuery->count();
                $earningsData[]  = (float) $completedQuery->sum('total_price');
                
                $current->addWeek();
            }
        } else {
            // Escala Mensual (Rango grande)
            $current = $start->copy();
            while ($current->lte($end)) {
                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();
                
                if ($monthStart->lt($start)) $monthStart = $start->copy();
                if ($monthEnd->gt($end)) $monthEnd = $end->copy();
                
                $labels[] = ucfirst($current->locale('es')->shortMonthName) . ' ' . $current->format('y');
                
                $createdQuery = WorkOrder::whereBetween('created_at', [$monthStart, $monthEnd]);
                $completedQuery = WorkOrder::whereBetween('created_at', [$monthStart, $monthEnd])->whereIn('status', ['completed', 'delivered']);

                if ($this->areaId) {
                    $createdQuery->whereHas('workOrderAreas', function ($q) { $q->where('area_id', $this->areaId); });
                    $completedQuery->whereHas('workOrderAreas', function ($q) { $q->where('area_id', $this->areaId); });
                }

                $createdData[]   = $createdQuery->count();
                $completedData[] = $completedQuery->count();
                $earningsData[]  = (float) $completedQuery->sum('total_price');
                
                $current->addMonth();
            }
        }

        return [
            'labels'    => $labels,
            'created'   => $createdData,
            'completed' => $completedData,
            'earnings'  => $earningsData,
        ];
    }

    /**
     * Calcula los KPIs financieros y operacionales filtrados.
     */
    public function getKpis()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $query = WorkOrder::whereBetween('created_at', [$start, $end]);
        if ($this->areaId) {
            $query->whereHas('workOrderAreas', function ($q) {
                $q->where('area_id', $this->areaId);
            });
        }
        $totalOrders = $query->count();
        
        $completedQuery = WorkOrder::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['completed', 'delivered']);
        if ($this->areaId) {
            $completedQuery->whereHas('workOrderAreas', function ($q) {
                $q->where('area_id', $this->areaId);
            });
        }
        $completedOrders = $completedQuery->count();
        $totalEarnings   = $completedQuery->sum('total_price');
        $averageTicket   = $completedOrders > 0 ? $totalEarnings / $completedOrders : 0;
        
        // Eficiencia de entrega: % de órdenes finalizadas a tiempo
        $onTimeQuery = WorkOrder::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['completed', 'delivered'])
            ->whereNotNull('delivery_date')
            ->where(function($q) {
                $q->whereNull('delivery_date')
                  ->orWhere('delivery_date', '>=', DB::raw('created_at'));
            });
        if ($this->areaId) {
            $onTimeQuery->whereHas('workOrderAreas', function ($q) {
                $q->where('area_id', $this->areaId);
            });
        }
        $onTimeOrders = $onTimeQuery->count();
        
        $onTimePercentage = $completedOrders > 0 ? ($onTimeOrders / $completedOrders) * 100 : 100;

        return [
            'total_orders'       => $totalOrders,
            'completed_orders'   => $completedOrders,
            'total_earnings'     => (float) $totalEarnings,
            'average_ticket'     => (float) $averageTicket,
            'on_time_percentage' => (float) $onTimePercentage,
        ];
    }

    /**
     * Distribución de trabajos del catálogo.
     */
    public function getCatalogDistribution()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $query = WorkOrder::select('catalog_item_id', DB::raw('count(*) as total'))
            ->with('catalogItem')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('catalog_item_id');

        if ($this->areaId) {
            $query->whereHas('workOrderAreas', function ($q) {
                $q->where('area_id', $this->areaId);
            });
        }

        return $query->groupBy('catalog_item_id')
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

    /**
     * Productividad de las áreas (procesamientos completados).
     */
    public function getAreaProductivity()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $query = WorkOrderArea::select('area_id', DB::raw('count(*) as total'))
            ->with('area')
            ->whereBetween('created_at', [$start, $end])
            ->where('kanban_status', 'completed');

        if ($this->areaId) {
            $query->where('area_id', $this->areaId);
        }

        return $query->groupBy('area_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($woa) {
                return [
                    'label' => $woa->area?->name ?? 'N/A',
                    'total' => $woa->total
                ];
            })->toArray();
    }

    /**
     * Distribución por tipo de cliente.
     */
    public function getClientTypeDistribution()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $query = WorkOrder::select('client_type', DB::raw('count(*) as total, sum(total_price) as earnings'))
            ->whereBetween('created_at', [$start, $end]);

        if ($this->areaId) {
            $query->whereHas('workOrderAreas', function ($q) {
                $q->where('area_id', $this->areaId);
            });
        }

        return $query->groupBy('client_type')
            ->get()
            ->map(function ($wo) {
                return [
                    'label' => $wo->client_type === 'student' ? 'Estudiantes' : 'Odontólogos / Clínicas',
                    'total' => $wo->total,
                    'earnings' => (float) $wo->earnings
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
            'areas'                  => Area::active()->ordered()->get(),
            'kpis'                   => $this->getKpis(),
            'initialChartData'       => $this->getChartData(),
            'catalogDistribution'    => $this->getCatalogDistribution(),
            'areaProductivity'       => $this->getAreaProductivity(),
            'clientTypeDistribution' => $this->getClientTypeDistribution(),
        ]);
    }
}
