<?php

namespace App\Livewire\WorkOrder;

use App\Enums\KanbanStatus;
use App\Enums\Priority;
use App\Enums\ProstheticType;
use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\Area;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

/**
 * Livewire WorkOrderList
 *
 * [SOLID - SRP] Listado y filtrado de Órdenes de Trabajo.
 * Permite buscar, filtrar por estado/área/prioridad, y navegar.
 */
#[Layout('layouts.app')]
class WorkOrderList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterArea = '';
    public string $filterPriority = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search'         => ['except' => ''],
        'filterStatus'   => ['except' => ''],
        'filterArea'     => ['except' => ''],
        'filterPriority' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterArea(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPriority(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterStatus', 'filterArea', 'filterPriority']);
        $this->resetPage();
    }

    public function render()
    {
        $query = WorkOrder::with(['client', 'creator', 'assignedTpd', 'currentArea'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('code', 'like', "%{$this->search}%")
                        ->orWhere('patient_name', 'like', "%{$this->search}%")
                        ->orWhere('doctor_name', 'like', "%{$this->search}%")
                        ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterArea, fn($q) => $q->where('current_area_id', $this->filterArea))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.work-order.work-order-list', [
            'workOrders' => $query->paginate(15),
            'areas'      => Area::active()->ordered()->get(),
            'statuses'   => WorkOrderStatus::options(),
            'priorities' => Priority::options(),
            'totalCount' => WorkOrder::count(),
        ]);
    }
}
