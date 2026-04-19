<?php

namespace App\Livewire\Admin;

use App\Models\WorkOrder;
use App\Models\Area;
use App\Models\TraceabilityLog;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire AdminDashboard
 *
 * [SOLID - SRP] Panel analítico central para roles de Administración.
 * Cumple con el requerimiento de visibilidad global y métricas.
 */
#[Layout('layouts.app')]
class AdminDashboard extends Component
{
    public function mount()
    {
        $user = auth()->user();
        
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

    public function render()
    {
        // 1. Métricas Globales
        $totalOrders = WorkOrder::count();
        $inProgress = WorkOrder::where('status', 'in_progress')->count();
        $completed = WorkOrder::where('status', 'completed')->count();
        
        $delayedOrders = WorkOrder::where('status', '!=', 'completed')
            ->whereNotNull('delivery_date')
            ->where('delivery_date', '<', now())
            ->get();

        $urgentOrders = WorkOrder::where('status', '!=', 'completed')
            ->where('priority', 'urgent')
            ->get();

        // 2. Cuellos de Botella (Dónde se acumula más trabajo)
        $bottlenecks = Area::withCount(['workOrderAreas' => function ($query) {
                // Contar los que NO están finalizados en el Area
                $query->where('kanban_status', '!=', 'completed');
            }])
            ->orderByDesc('work_order_areas_count')
            ->get();

        // 3. Tipos de Prótesis más comunes (Data Analytics)
        $prostheticDist = WorkOrder::select('prosthetic_type', DB::raw('count(*) as total'))
            ->groupBy('prosthetic_type')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($item) {
                // $item->prosthetic_type ya es casteado automáticamente por Laravel a un Enum
                return [
                    'label' => $item->prosthetic_type instanceof \App\Enums\ProstheticType 
                                ? $item->prosthetic_type->label() 
                                : \App\Enums\ProstheticType::from($item->prosthetic_type)->label(),
                    'total' => $item->total,
                ];
            });

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
        ]);
    }
}
